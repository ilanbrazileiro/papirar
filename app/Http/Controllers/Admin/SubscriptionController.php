<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\Subscription; use Illuminate\Http\Request;
class SubscriptionController extends Controller {
 public function index(){ $items=Subscription::with(['user','plan'])->latest('id')->paginate(20); return view('admin.subscriptions.index', compact('items')); }
 public function show(Subscription $subscription){ $subscription->load(['user','plan','transactions']); return view('admin.subscriptions.show', compact('subscription')); }
 public function update(Request $request, Subscription $subscription){ $data=$request->validate(['status'=>'required|in:pending,active,expired,canceled,failed']); $subscription->update(['status'=>$data['status']]); return redirect()->route('admin.subscriptions.show',$subscription)->with('success','Assinatura atualizada.'); }
}
