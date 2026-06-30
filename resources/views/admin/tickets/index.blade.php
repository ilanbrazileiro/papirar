@extends('layouts.admin')

@section('title', 'Tickets | Papirar')
@section('page_title', 'Tickets')

@section('content')
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $openTicketsCount ?? 0 }}</h3>
                    <p>Abertos</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $inProgressTicketsCount ?? 0 }}</h3>
                    <p>Em andamento</p>
                </div>
                <div class="icon"><i class="fas fa-comments"></i></div>
                <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $resolvedTicketsCount ?? 0 }}</h3>
                    <p>Resolvidos</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $closedTicketsCount ?? 0 }}</h3>
                    <p>Fechados</p>
                </div>
                <div class="icon"><i class="fas fa-lock"></i></div>
                <a href="{{ route('admin.tickets.index', ['status' => 'closed']) }}" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h3 class="card-title mb-0">Central de tickets</h3>
                    <div class="text-muted small mt-1">Tickets abertos e em andamento aparecem primeiro.</div>
                </div>

                <form method="GET" action="{{ route('admin.tickets.index') }}" class="form-inline mt-3 mt-lg-0">
                    <select name="status" class="form-control mr-2">
                        <option value="">Todos os status</option>
                        <option value="open" @selected(request('status') === 'open')>Aberto</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>Em andamento</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolvido</option>
                        <option value="closed" @selected(request('status') === 'closed')>Fechado</option>
                    </select>
                    <button class="btn btn-outline-primary">Filtrar</button>
                    @if(request()->filled('status'))
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary ml-2">Limpar</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if($tickets->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Assunto</th>
                                <th>Categoria</th>
                                <th>Cliente</th>
                                <th>Status</th>
                                <th>Última atualização</th>
                                <th class="text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr class="{{ $ticket->status === 'open' ? 'table-danger' : ($ticket->status === 'in_progress' ? 'table-warning' : '') }}">
                                    <td>#{{ $ticket->id }}</td>
                                    <td class="font-weight-bold">{{ $ticket->subject }}</td>
                                    <td>{{ $ticket->category_label }}</td>
                                    <td>{{ $ticket->user->name ?? '-' }}</td>
                                    <td>
                                        @if($ticket->status === 'open')
                                            <span class="badge badge-danger">Aberto</span>
                                        @elseif($ticket->status === 'in_progress')
                                            <span class="badge badge-warning">Em andamento</span>
                                        @elseif($ticket->status === 'resolved')
                                            <span class="badge badge-success">Resolvido</span>
                                        @else
                                            <span class="badge badge-secondary">Fechado</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($ticket->last_message_at ?? $ticket->updated_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-right">
                                        <a class="btn btn-sm {{ in_array($ticket->status, ['open', 'in_progress'], true) ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.tickets.show', $ticket) }}">
                                            {{ in_array($ticket->status, ['open', 'in_progress'], true) ? 'Atender' : 'Abrir' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-muted">Nenhum ticket encontrado.</div>
            @endif
        </div>

        @if($tickets->hasPages())
            <div class="card-footer">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
@endsection
