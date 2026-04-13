<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected PaymentWebhookService $paymentWebhookService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $result = $this->paymentWebhookService->handleMercadoPago(
                $request->all(),
                $request->query('topic') ?: $request->input('type')
            );

            return response()->json([
                'message' => $result['message'] ?? 'Webhook processado com sucesso.',
                'result' => $result,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao processar webhook do Mercado Pago.', [
                'exception' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['message' => 'Erro ao processar webhook.'], 500);
        }
    }
}
