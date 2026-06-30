@extends('layouts.admin')

@section('title', 'Dashboard Admin | Papirar')
@section('page_title', 'Dashboard administrativo')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $questionsCount ?? 0 }}</h3>
                <p>Questões</p>
            </div>
            <div class="icon"><i class="fas fa-circle-question"></i></div>
            <a href="{{ url('/admin/questions') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $customersCount ?? 0 }}</h3>
                <p>Clientes</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ url('/admin/customers') }}" class="small-box-footer">Ver clientes <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $examsCount ?? 0 }}</h3>
                <p>Concursos</p>
            </div>
            <div class="icon"><i class="fas fa-landmark"></i></div>
            <a href="{{ url('/admin/exams') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box {{ ($ticketsCount ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}">
            <div class="inner">
                <h3>{{ $ticketsCount ?? 0 }}</h3>
                <p>Tickets que exigem atenção</p>
            </div>
            <div class="icon"><i class="fas fa-headset"></i></div>
            <a href="{{ route('admin.tickets.index') }}" class="small-box-footer">Atender <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-envelope-open-text"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tickets abertos</span>
                <span class="info-box-number">{{ $openTicketsCount ?? 0 }}</span>
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="small">Ver abertos</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-comments"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em andamento</span>
                <span class="info-box-number">{{ $inProgressTicketsCount ?? 0 }}</span>
                <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}" class="small">Ver em andamento</a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-route"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Atalho operacional</span>
                <span class="info-box-number">Suporte</span>
                <a href="{{ route('admin.tickets.index') }}" class="small">Abrir central de tickets</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Tickets recentes que exigem atenção</h3>
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-primary ml-auto">Ver todos</a>
    </div>

    <div class="card-body p-0">
        @if(($recentTickets ?? collect())->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Assunto</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Última atualização</th>
                            <th class="text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                            <tr class="{{ $ticket->status === 'open' ? 'table-danger' : '' }}">
                                <td>#{{ $ticket->id }}</td>
                                <td class="font-weight-bold">{{ $ticket->subject }}</td>
                                <td>{{ $ticket->user->name ?? '-' }}</td>
                                <td>
                                    @if($ticket->status === 'open')
                                        <span class="badge badge-danger">Aberto</span>
                                    @elseif($ticket->status === 'in_progress')
                                        <span class="badge badge-warning">Em andamento</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $ticket->status_label }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($ticket->last_message_at ?? $ticket->updated_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-primary">Atender</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-muted">Nenhum ticket aberto ou em andamento.</div>
        @endif
    </div>
</div>
@endsection
