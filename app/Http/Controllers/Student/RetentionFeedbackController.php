<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseAccess;
use App\Models\RetentionFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetentionFeedbackController extends Controller
{
    public function store(Request $request, CourseAccess $courseAccess): RedirectResponse
    {
        abort_unless((int)$courseAccess->user_id === (int)Auth::id(),403);
        $data=$request->validate(['reason'=>['required','in:price,not_using,exam_finished,content,usability,other'],'notes'=>['nullable','string','max:500']]);
        RetentionFeedback::query()->updateOrCreate(
            ['user_id'=>Auth::id(),'course_access_id'=>$courseAccess->id,'stage'=>'non_renewal'],
            ['course_id'=>$courseAccess->course_id,'reason'=>$data['reason'],'notes'=>$data['notes'] ?? null]
        );
        return back()->with('success','Obrigado pelo retorno. Seu acesso continua disponível até o fim do período atual.');
    }
}
