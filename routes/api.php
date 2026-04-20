<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\MercadoPagoWebhookController;

Route::post('/webhooks/mercado-pago', [MercadoPagoWebhookController::class, 'handle']);