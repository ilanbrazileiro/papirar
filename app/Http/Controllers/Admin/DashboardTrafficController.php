<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Marketing\AdminTrafficDashboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardTrafficController extends Controller
{
    public function index(Request $request, AdminTrafficDashboard $traffic): JsonResponse
    {
        $data = $request->validate([
            'period' => ['required', Rule::in(['today', '7', '30', 'month', 'custom'])],
            'group' => ['required', Rule::in(['day', 'month'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        return response()->json($traffic->data($data));
    }
}
