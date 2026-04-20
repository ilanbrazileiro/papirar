<?php

namespace App\Services\Billing;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MercadoPagoService
{
    protected string $baseUrl;
    protected string $accessToken;
    protected ?string $webhookUrl;
    protected ?string $webhookSecret;
    protected ?string $publicKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.mercadopago.base_url', 'https://api.mercadopago.com'), '/');
        $this->accessToken = (string) config('services.mercadopago.access_token');
        $this->webhookUrl = config('services.mercadopago.webhook_url');
        $this->webhookSecret = config('services.mercadopago.webhook_secret');
        $this->publicKey = config('services.mercadopago.public_key');
    }

    public function createCheckoutPreference(User $user, Subscription $subscription, PaymentTransaction $transaction): array
    {
        $subscription->loadMissing('plan');

        if (! $subscription->plan) {
            throw new RuntimeException('Plano da assinatura não encontrado.');
        }

        $payload = [
            'items' => [[
                'id' => (string) $subscription->plan->id,
                'title' => $subscription->plan->name,
                'description' => 'Assinatura do plano ' . $subscription->plan->name,
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => (float) $subscription->plan->price,
            ]],
            'payer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'external_reference' => (string) $transaction->id,
            'notification_url' => $this->webhookUrl,
            'back_urls' => [
                'success' => route('student.subscriptions.index', ['payment' => 'success']),
                'failure' => route('student.subscriptions.index', ['payment' => 'failure']),
                'pending' => route('student.subscriptions.index', ['payment' => 'pending']),
            ],
            'auto_return' => 'approved',
            'metadata' => [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->id,
                'plan_id' => $subscription->plan->id,
            ],
        ];

        $response = $this->client()->post($this->baseUrl . '/checkout/preferences', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao criar preferência de pagamento no Mercado Pago.');
        }

        $data = $response->json();

        return [
            'gateway' => PaymentTransaction::GATEWAY_MERCADO_PAGO,
            'external_id' => $data['id'] ?? null,
            'init_point' => $data['init_point'] ?? null,
            'sandbox_init_point' => $data['sandbox_init_point'] ?? null,
            'response' => $data,
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $response = $this->client()->get($this->baseUrl . '/v1/payments/' . $paymentId);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao consultar pagamento no Mercado Pago.');
        }

        return $response->json();
    }

    public function extractPaymentIdFromWebhook(array $payload, ?string $topic = null): ?string
    {
        return Arr::get($payload, 'data.id')
            ?? Arr::get($payload, 'id')
            ?? Arr::get($payload, 'resource.id')
            ?? ($topic === 'payment' ? Arr::get($payload, 'resource') : null);
    }

    public function normalizePaymentPayload(array $paymentData): array
    {
        return [
            'payment_id' => Arr::get($paymentData, 'id'),
            'status' => Arr::get($paymentData, 'status'),
            'status_detail' => Arr::get($paymentData, 'status_detail'),
            'external_reference' => Arr::get($paymentData, 'external_reference'),
            'amount' => Arr::get($paymentData, 'transaction_amount'),
            'paid_at' => Arr::get($paymentData, 'date_approved'),
            'raw' => $paymentData,
        ];
    }

    public function validateWebhookSignature(array $headers, array $queryParams, array $payload): bool
    {
        if (! $this->webhookSecret) {
            return true;
        }

        $xSignature = $headers['x-signature'][0] ?? $headers['X-Signature'][0] ?? null;
        $xRequestId = $headers['x-request-id'][0] ?? $headers['X-Request-Id'][0] ?? null;

        if (! $xSignature || ! $xRequestId) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, null);
            if ($key && $value) {
                $parts[trim($key)] = trim($value);
            }
        }

        $receivedHash = $parts['v1'] ?? null;
        if (! $receivedHash) {
            return false;
        }

        $dataId = (string) ($queryParams['data.id'] ?? Arr::get($payload, 'data.id') ?? Arr::get($payload, 'id') ?? '');
        $ts = (string) ($parts['ts'] ?? '');

        if ($dataId === '' || $ts === '') {
            return false;
        }

        $manifest = "id:$dataId;request-id:$xRequestId;ts:$ts;";
        $expected = hash_hmac('sha256', $manifest, $this->webhookSecret);

        return hash_equals($expected, $receivedHash);
    }

    public function publicKey(): ?string
    {
        return $this->publicKey;
    }

    protected function client(): PendingRequest
    {
        if ($this->accessToken === '') {
            throw new RuntimeException('Mercado Pago não configurado. Defina services.mercadopago.access_token.');
        }

        return Http::acceptJson()
            ->withToken($this->accessToken)
            ->timeout(20);
    }
}
