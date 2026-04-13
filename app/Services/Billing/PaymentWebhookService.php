<?php

namespace App\Services\Billing;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class PaymentWebhookService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected MercadoPagoService $mercadoPagoService,
    ) {
    }

    public function handleMercadoPago(array $webhookPayload, ?string $topic = null): array
    {
        $paymentId = $this->mercadoPagoService->extractPaymentIdFromWebhook($webhookPayload, $topic);

        if (! $paymentId) {
            return [
                'handled' => false,
                'message' => 'Webhook recebido sem payment_id identificável.',
            ];
        }

        $paymentData = $this->mercadoPagoService->getPayment((string) $paymentId);
        $normalized = $this->mercadoPagoService->normalizePaymentPayload($paymentData);

        $externalReference = Arr::get($normalized, 'external_reference');

        if (! $externalReference) {
            return [
                'handled' => false,
                'message' => 'Pagamento sem external_reference. Não foi possível localizar a transação interna.',
                'payment' => $normalized,
            ];
        }

        $transaction = PaymentTransaction::query()
            ->with(['subscription.plan'])
            ->find($externalReference);

        if (! $transaction) {
            throw new ModelNotFoundException('Transação interna não encontrada para o external_reference informado.');
        }

        $payloadToStore = [
            'webhook' => $webhookPayload,
            'payment' => $normalized,
        ];

        $status = (string) Arr::get($normalized, 'status');

        if ($status === 'approved') {
            $subscription = $this->subscriptionService->activateSubscriptionFromTransaction($transaction, $payloadToStore);

            return [
                'handled' => true,
                'status' => 'approved',
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->id,
            ];
        }

        if (in_array($status, ['pending', 'in_process'], true)) {
            $this->subscriptionService->markTransactionAsPending($transaction, $payloadToStore + ['external_id' => $normalized['payment_id']]);

            return [
                'handled' => true,
                'status' => 'pending',
                'transaction_id' => $transaction->id,
            ];
        }

        $this->subscriptionService->failTransaction($transaction, $payloadToStore + ['external_id' => $normalized['payment_id']]);

        return [
            'handled' => true,
            'status' => 'failed',
            'transaction_id' => $transaction->id,
        ];
    }
}
