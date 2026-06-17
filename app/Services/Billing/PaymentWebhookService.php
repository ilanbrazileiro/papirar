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
        protected CoursePaymentService $coursePaymentService,
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
            ->with(['subscription.plan', 'subscription.course', 'course'])
            ->find($externalReference);

        if (! $transaction) {
            throw new ModelNotFoundException('Transação interna não encontrada para o external_reference informado.');
        }

        $payloadToStore = [
            'webhook' => $webhookPayload,
            'payment' => $normalized,
            'external_id' => $normalized['payment_id'] ?? $transaction->external_id,
        ];

        $status = (string) Arr::get($normalized, 'status');
        $isCoursePayment = $transaction->course_id || $transaction->subscription?->course_id;

        if ($transaction->isPaid() && $status === 'approved') {
            return [
                'handled' => true,
                'status' => 'approved',
                'transaction_id' => $transaction->id,
                'message' => 'Pagamento já processado anteriormente.',
            ];
        }

        if ($status === 'approved') {
            if ($isCoursePayment) {
                $subscription = $this->coursePaymentService->activateCourseAccessFromTransaction($transaction, $payloadToStore);

                return [
                    'handled' => true,
                    'status' => 'approved',
                    'subscription_id' => $subscription->id,
                    'course_id' => $subscription->course_id,
                    'transaction_id' => $transaction->id,
                ];
            }

            $subscription = $this->subscriptionService->activateSubscriptionFromTransaction($transaction, $payloadToStore);

            return [
                'handled' => true,
                'status' => 'approved',
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->id,
            ];
        }

        if (in_array($status, ['pending', 'in_process'], true)) {
            if ($isCoursePayment) {
                $this->coursePaymentService->markTransactionAsPending($transaction, $payloadToStore);
            } else {
                $this->subscriptionService->markTransactionAsPending($transaction, $payloadToStore);
            }

            return [
                'handled' => true,
                'status' => 'pending',
                'transaction_id' => $transaction->id,
            ];
        }

        if ($isCoursePayment) {
            $this->coursePaymentService->failTransaction($transaction, $payloadToStore);
        } else {
            $this->subscriptionService->failTransaction($transaction, $payloadToStore);
        }

        return [
            'handled' => true,
            'status' => 'failed',
            'transaction_id' => $transaction->id,
        ];
    }
}
