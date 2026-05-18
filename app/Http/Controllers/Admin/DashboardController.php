<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Exam;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index', [
            'questionsCount' => class_exists(Question::class) ? Question::query()->count() : 0,
            'examsCount' => class_exists(Exam::class) ? Exam::query()->count() : 0,
            'customersCount' => class_exists(User::class) ? User::query()->count() : 0,
            'ticketsCount' => 0,
        ]);
    }
}
