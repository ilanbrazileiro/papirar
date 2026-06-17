<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\Billing\CoursePaymentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CoursePaymentReturnController extends Controller
{
    public function success(Request $request, PaymentTransaction $transaction, CoursePaymentStatusService $statusService): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $this->trySyncPayment($request, $transaction, $statusService);

        return redirect()
            ->route('student.purchases.index')
            ->with('success', 'Pagamento recebido. O acesso ao curso será liberado assim que a confirmação do Mercado Pago estiver concluída.');
    }

    public function pending(Request $request, PaymentTransaction $transaction, CoursePaymentStatusService $statusService): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $this->trySyncPayment($request, $transaction, $statusService);

        return redirect()
            ->route('student.purchases.index')
            ->with('warning', 'Pagamento em análise. Quando o Mercado Pago aprovar, o acesso será liberado automaticamente.');
    }

    public function failure(Request $request, PaymentTransaction $transaction, CoursePaymentStatusService $statusService): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $this->trySyncPayment($request, $transaction, $statusService);

        return redirect()
            ->route('student.purchases.index')
            ->with('error', 'Pagamento não aprovado. Você pode tentar novamente pelo histórico de compras.');
    }

    protected function authorizeTransaction(PaymentTransaction $transaction): void
    {
        abort_unless((int) $transaction->user_id === (int) auth()->id(), 403);
    }

    protected function trySyncPayment(Request $request, PaymentTransaction $transaction, CoursePaymentStatusService $statusService): void
    {
        $paymentId = $request->input('payment_id')
            ?: $request->input('collection_id')
            ?: $request->input('data_id');

        if (! $paymentId) {
            return;
        }

        try {
            $statusService->syncFromPaymentId((string) $paymentId, $transaction);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
