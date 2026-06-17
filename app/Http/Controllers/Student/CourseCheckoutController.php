<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Billing\CoursePaymentService;
use App\Services\Billing\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CourseCheckoutController extends Controller
{
    public function __construct(
        protected CoursePaymentService $coursePaymentService,
        protected MercadoPagoService $mercadoPagoService,
    ) {
    }

    public function checkout(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle' => ['required', Rule::in(array_keys(Course::billingCycleOptions()))],
        ]);

        try {
            $checkout = $this->coursePaymentService->checkout(
                Auth::user(),
                $course,
                (string) $validated['billing_cycle'],
                $this->mercadoPagoService
            );

            $checkoutUrl = $checkout['checkout']['init_point'] ?? $checkout['checkout']['sandbox_init_point'] ?? null;

            if (! $checkoutUrl) {
                return back()->with('error', 'Não foi possível obter a URL de pagamento.');
            }

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            Log::error('Erro ao iniciar checkout do curso.', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'billing_cycle' => $validated['billing_cycle'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível iniciar o pagamento deste curso.');
        }
    }
}
