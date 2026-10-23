@extends('layouts.admin')
@section('title','Dashboard Admin | Papirar')
@section('page_title','Dashboard administrativo')
@section('content')
<div class="row">
@foreach([
 ['value'=>$questionsCount??0,'label'=>'Questões','class'=>'bg-info','icon'=>'fa-question-circle','url'=>url('/admin/questions'),'action'=>'Gerenciar'],
 ['value'=>$customersCount??0,'label'=>'Clientes','class'=>'bg-success','icon'=>'fa-users','url'=>url('/admin/customers'),'action'=>'Ver clientes'],
 ['value'=>$examsCount??0,'label'=>'Concursos','class'=>'bg-warning','icon'=>'fa-landmark','url'=>url('/admin/exams'),'action'=>'Gerenciar'],
 ['value'=>$ticketsCount??0,'label'=>'Tickets que exigem atenção','class'=>(($ticketsCount??0)>0?'bg-danger':'bg-secondary'),'icon'=>'fa-headset','url'=>route('admin.tickets.index'),'action'=>'Atender']
] as $metric)
<div class="col-lg-3 col-6"><div class="small-box {{ $metric['class'] }}"><div class="inner"><h3>{{ $metric['value'] }}</h3><p>{{ $metric['label'] }}</p></div><div class="icon"><i class="fas {{ $metric['icon'] }}"></i></div><a href="{{ $metric['url'] }}" class="small-box-footer">{{ $metric['action'] }} <i class="fas fa-arrow-circle-right"></i></a></div></div>
@endforeach
</div>
<div class="row">
<div class="col-lg-4 col-12"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-envelope-open-text"></i></span><div class="info-box-content"><span class="info-box-text">Tickets abertos</span><span class="info-box-number">{{ $openTicketsCount??0 }}</span><a href="{{ route('admin.tickets.index',['status'=>'open']) }}" class="small">Ver abertos</a></div></div></div>
<div class="col-lg-4 col-12"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-comments"></i></span><div class="info-box-content"><span class="info-box-text">Em andamento</span><span class="info-box-number">{{ $inProgressTicketsCount??0 }}</span><a href="{{ route('admin.tickets.index',['status'=>'in_progress']) }}" class="small">Ver em andamento</a></div></div></div>
<div class="col-lg-4 col-12"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-route"></i></span><div class="info-box-content"><span class="info-box-text">Atalho operacional</span><span class="info-box-number">Suporte</span><a href="{{ route('admin.tickets.index') }}" class="small">Abrir central de tickets</a></div></div></div>
</div>
@include('admin.dashboard.partials.traffic')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Tickets recentes que exigem atenção</h3><a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-primary ml-auto">Ver todos</a></div><div class="card-body p-0">
@if(($recentTickets??collect())->isNotEmpty())<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>ID</th><th>Assunto</th><th>Cliente</th><th>Status</th><th>Última atualização</th><th class="text-right">Ação</th></tr></thead><tbody>
@foreach($recentTickets as $ticket)<tr class="{{ $ticket->status==='open'?'table-danger':'' }}"><td>#{{ $ticket->id }}</td><td class="font-weight-bold">{{ $ticket->subject }}</td><td>{{ $ticket->user->name??'-' }}</td><td>
@if($ticket->status==='open')<span class="badge badge-danger">Aberto</span>
@elseif($ticket->status==='in_progress')<span class="badge badge-warning">Em andamento</span>
@else<span class="badge badge-secondary">{{ $ticket->status_label }}</span>
@endif</td><td>{{ \App\Support\DisplayDate::format($ticket->last_message_at??$ticket->updated_at) }}</td><td class="text-right"><a href="{{ route('admin.tickets.show',$ticket) }}" class="btn btn-sm btn-primary">Atender</a></td></tr>
@endforeach
</tbody></table></div>
@else<div class="p-4 text-muted">Nenhum ticket aberto ou em andamento.</div>
@endif
</div></div>
@endsection
