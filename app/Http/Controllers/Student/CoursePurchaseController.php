<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoursePurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = PaymentTransaction::query()
            ->with(['course', 'subscription.course'])
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', $request->integer('course_id')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $courseIds = PaymentTransaction::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('course_id')
            ->distinct()
            ->pluck('course_id')
            ->filter()
            ->values();

        $courses = Course::query()
            ->whereIn('id', $courseIds)
            ->orderBy('title')
            ->get();

        $statuses = $this->statuses();

        return view('student.courses.purchases', compact('transactions', 'courses', 'statuses'));
    }

    private function statuses(): array
    {
        return [
            PaymentTransaction::STATUS_PENDING => 'Pendente',
            PaymentTransaction::STATUS_PAID => 'Pago',
            PaymentTransaction::STATUS_FAILED => 'Falhou',
            PaymentTransaction::STATUS_REFUNDED => 'Reembolsado',
            PaymentTransaction::STATUS_CANCELED => 'Cancelado',
        ];
    }
}
