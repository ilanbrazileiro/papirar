<?php

namespace App\Services\Billing;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use RuntimeException;

class CoursePaymentStatusService
{
    public function __construct(
        protected MercadoPagoService $mercadoPagoService,
        protected CoursePaymentService $coursePaymentService,
    ) {
    }

    public function syncFromPaymentId(string $paymentId, ?PaymentTransaction $expectedTransaction = null): PaymentTransaction
    {
        $paymentData = $this->mercadoPagoService->getPayment($paymentId);
        $normalized = $this->mercadoPagoService->normalizePaymentPayload($paymentData);

        return $this->syncFromNormalizedPayment($normalized, $expectedTransaction, [
            'source' => 'checkout_return',
            'payment_data' => $paymentData,
        ]);
    }

    public function syncFromNormalizedPayment(array $normalized, ?PaymentTransaction $expectedTransaction = null, array $context = []): PaymentTransaction
    {
        $transaction = $this->resolveTransaction($normalized, $expectedTransaction);
        $transaction->loadMissing(['subscription.course', 'course']);

        if (! $transaction->course_id && ! $transaction->subscription?->course_id) {
            throw new RuntimeException('A transação informada não pertence a um pagamento de curso.');
        }

        $payloadToStore = [
            'sync' => $context,
            'payment' => $normalized,
            'external_id' => $normalized['payment_id'] ?? $transaction->external_id,
        ];

        $status = (string) Arr::get($normalized, 'status');

        if ($status === 'approved') {
            $this->coursePaymentService->activateCourseAccessFromTransaction($transaction, $payloadToStore);

            return $transaction->fresh(['subscription.course', 'course']);
        }

        if (in_array($status, ['pending', 'in_process'], true)) {
            return $this->coursePaymentService
                ->markTransactionAsPending($transaction, $payloadToStore)
                ->fresh(['subscription.course', 'course']);
        }

        return $this->coursePaymentService
            ->failTransaction($transaction, $payloadToStore)
            ->fresh(['subscription.course', 'course']);
    }

    protected function resolveTransaction(array $normalized, ?PaymentTransaction $expectedTransaction = null): PaymentTransaction
    {
        $externalReference = Arr::get($normalized, 'external_reference');

        if ($externalReference) {
            $transaction = PaymentTransaction::query()
                ->with(['subscription.course', 'course'])
                ->find($externalReference);

            if (! $transaction) {
                throw new ModelNotFoundException('Transação interna não encontrada para o external_reference informado.');
            }

            if ($expectedTransaction && (int) $expectedTransaction->id !== (int) $transaction->id) {
                throw new RuntimeException('O pagamento retornado não pertence à transação esperada.');
            }

            return $transaction;
        }

        if ($expectedTransaction) {
            return $expectedTransaction;
        }

        throw new ModelNotFoundException('Pagamento sem external_reference. Não foi possível localizar a transação interna.');
    }
}
