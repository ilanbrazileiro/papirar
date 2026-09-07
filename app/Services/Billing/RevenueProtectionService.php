<?php
namespace App\Services\Billing;

use App\Models\CourseAccess;
use App\Models\PaymentTransaction;
use Illuminate\Support\Collection;

class RevenueProtectionService
{
    public function notices(int $userId): Collection
    {
        $transactions = PaymentTransaction::query()->with(['course','subscription'])
            ->where('user_id',$userId)->whereNotNull('course_id')
            ->whereIn('status',[PaymentTransaction::STATUS_PENDING,PaymentTransaction::STATUS_FAILED,PaymentTransaction::STATUS_CANCELED])
            ->where('created_at','>=',now()->subDays(30))->latest('id')->get()->unique('course_id')
            ->map(function($transaction){
                $cycle=$transaction->subscription?->billing_cycle ?? data_get($transaction->payload,'billing_cycle','monthly');
                return ['type'=>$transaction->status === PaymentTransaction::STATUS_PENDING ? 'pending' : 'failed','priority'=>$transaction->status === PaymentTransaction::STATUS_PENDING ? 0 : 1,'course'=>$transaction->course,'transaction'=>$transaction,'access'=>null,'billing_cycle'=>$cycle];
            })->filter(fn($item)=>$item['course']);

        $expiring=CourseAccess::query()->with('course')->where('user_id',$userId)
            ->where('status',CourseAccess::STATUS_ACTIVE)->where('access_type',CourseAccess::TYPE_PAID)
            ->whereBetween('ends_at',[now(),now()->addDays(7)->endOfDay()])->get()
            ->map(fn($access)=>['type'=>'expiring','priority'=>2,'course'=>$access->course,'transaction'=>null,'access'=>$access,'billing_cycle'=>'monthly'])
            ->filter(fn($item)=>$item['course']);

        return $transactions->concat($expiring)->sortBy('priority')->unique(fn ($item) => (int) $item['course']->id)->values();
    }
}
