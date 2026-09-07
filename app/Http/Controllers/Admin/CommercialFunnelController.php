<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommercialFunnelController extends Controller
{
    public function index(Request $request): View
    {
        $data=$request->validate(['from'=>['nullable','date'],'to'=>['nullable','date'],'course_id'=>['nullable','integer','exists:courses,id'],'source'=>['nullable','string','max:120'],'campaign'=>['nullable','string','max:190']]);
        $to=isset($data['to'])?Carbon::parse($data['to'])->endOfDay():now()->endOfDay();
        $from=isset($data['from'])?Carbon::parse($data['from'])->startOfDay():$to->copy()->subDays(29)->startOfDay();
        abort_if($from->gt($to)||$from->diffInDays($to)>366,422,'Informe um período válido de até 367 dias.');
        $courseId=isset($data['course_id'])?(int)$data['course_id']:null;
        $registrations=User::query()->where('role','student')->whereBetween('created_at',[$from,$to])->when($data['source']??null,fn($q,$v)=>$q->where('acquisition_source',$v))->when($data['campaign']??null,fn($q,$v)=>$q->where('acquisition_campaign',$v));
        $registrationIds=(clone $registrations)->pluck('id');
        $trials=CourseAccess::query()->whereIn('user_id',$registrationIds)->where('access_type',CourseAccess::TYPE_TRIAL)->where('starts_at','<=',$to)->when($courseId,fn($q)=>$q->where('course_id',$courseId))->distinct()->pluck('user_id');
        $activated=DB::table('user_answers as ua')->join('study_sessions as ss','ss.id','=','ua.study_session_id')->whereIn('ua.user_id',$trials)->where('ua.answered_at','<=',$to)->when($courseId,fn($q)=>$q->where('ss.course_id',$courseId))->groupBy('ua.user_id')->havingRaw('COUNT(*) >= 10')->havingRaw("MAX(CASE WHEN ss.finished_at IS NOT NULL OR ss.mode = 'review' THEN 1 ELSE 0 END) = 1")->pluck('ua.user_id');
        $paid=PaymentTransaction::query()->whereIn('user_id',$trials)->where('status',PaymentTransaction::STATUS_PAID)->where('paid_at','<=',$to)->when($courseId,fn($q)=>$q->where('course_id',$courseId))->distinct()->pluck('user_id');
        $paidPeriod=PaymentTransaction::query()->where('status',PaymentTransaction::STATUS_PAID)->whereBetween('paid_at',[$from,$to])->when($courseId,fn($q)=>$q->where('course_id',$courseId));
        $renewals=(clone $paidPeriod)->whereExists(function($q){$q->selectRaw('1')->from('payment_transactions as previous')->whereColumn('previous.user_id','payment_transactions.user_id')->whereColumn('previous.course_id','payment_transactions.course_id')->where('previous.status',PaymentTransaction::STATUS_PAID)->whereColumn('previous.paid_at','<','payment_transactions.paid_at');})->count();
        $funnel=['registrations'=>$registrationIds->count(),'trials'=>$trials->count(),'activated'=>$activated->count(),'paid'=>$paid->count()];
        $summary=['revenue'=>(float)(clone $paidPeriod)->sum('amount'),'transactions'=>(clone $paidPeriod)->count(),'renewals'=>$renewals,'pending'=>PaymentTransaction::query()->where('status',PaymentTransaction::STATUS_PENDING)->whereBetween('created_at',[$from,$to])->when($courseId,fn($q)=>$q->where('course_id',$courseId))->count(),'failed'=>PaymentTransaction::query()->whereIn('status',[PaymentTransaction::STATUS_FAILED,PaymentTransaction::STATUS_CANCELED])->whereBetween('created_at',[$from,$to])->when($courseId,fn($q)=>$q->where('course_id',$courseId))->count()];
        $byCourse=Course::query()->active()->orderBy('sort_order')->orderBy('title')->get()->map(function($course)use($from,$to){$trials=CourseAccess::query()->where('course_id',$course->id)->where('access_type',CourseAccess::TYPE_TRIAL)->whereBetween('starts_at',[$from,$to])->distinct()->count('user_id');$payments=PaymentTransaction::query()->where('course_id',$course->id)->where('status',PaymentTransaction::STATUS_PAID)->whereBetween('paid_at',[$from,$to]);$buyers=(clone $payments)->distinct()->count('user_id');return ['course'=>$course,'trials'=>$trials,'buyers'=>$buyers,'revenue'=>(float)(clone $payments)->sum('amount'),'rate'=>$trials?round(($buyers/$trials)*100,1):null];});
        $sources=(clone $registrations)->selectRaw("COALESCE(NULLIF(acquisition_source,''),'Não identificada') as source, COUNT(*) as total")->groupBy('source')->orderByDesc('total')->limit(10)->get();
        $campaigns=(clone $registrations)->selectRaw("COALESCE(NULLIF(acquisition_campaign,''),'Sem campanha') as campaign, COUNT(*) as total")->groupBy('campaign')->orderByDesc('total')->limit(10)->get();
        $rate=fn($n,$d)=>$d?round(($n/$d)*100,1):null;
        $courses=Course::query()->active()->orderBy('sort_order')->orderBy('title')->get();
        return view('admin.reports.commercial-funnel',compact('from','to','courseId','funnel','summary','byCourse','sources','campaigns','rate','courses'));
    }
}
