<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\MercadoPagoService;
use App\Services\Billing\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected PaymentWebhookService $paymentWebhookService,
        protected MercadoPagoService $mercadoPagoService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (! $this->mercadoPagoService->validateWebhookSignature($request->headers->all(), $request->query(), $request->all())) {
            Log::warning('Webhook do Mercado Pago rejeitado por assinatura inválida.', [
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
            ]);

            return response()->json(['message' => 'Assinatura do webhook inválida.'], 401);
        }

        try {
            $result = $this->paymentWebhookService->handleMercadoPago(
                $request->all(),
                $request->query('topic') ?: $request->input('type') ?: $request->input('action')
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
