@extends('admin.layout')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Tickets</h1>
            <div class="small-muted">Acompanhe os chamados, filtre por status e abra a conversa completa.</div>
        </div>

        <form method="GET" action="{{ route('admin.tickets.index') }}" class="d-flex gap-2">
            <select name="status" class="form-select">
                <option value="">Todos os status</option>
                <option value="open" @selected(request('status') === 'open')>Aberto</option>
                <option value="in_progress" @selected(request('status') === 'in_progress')>Em andamento</option>
                <option value="resolved" @selected(request('status') === 'resolved')>Resolvido</option>
                <option value="closed" @selected(request('status') === 'closed')>Fechado</option>
            </select>
            <button class="btn btn-outline-primary">Filtrar</button>
        </form>
    </div>

    <div class="card-soft p-4">
        @if($tickets->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Assunto</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Última atualização</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>#{{ $ticket->id }}</td>
                                <td class="fw-semibold">{{ $ticket->subject }}</td>
                                <td>{{ $ticket->user->name ?? '-' }}</td>
                                <td>
                                    @if($ticket->status === 'open')
                                        <span class="badge text-bg-primary">Aberto</span>
                                    @elseif($ticket->status === 'in_progress')
                                        <span class="badge text-bg-warning">Em andamento</span>
                                    @elseif($ticket->status === 'resolved')
                                        <span class="badge text-bg-success">Resolvido</span>
                                    @else
                                        <span class="badge text-bg-secondary">Fechado</span>
                                    @endif
                                </td>
                                <td>{{ optional($ticket->last_message_at ?? $ticket->updated_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tickets.show', $ticket) }}">
                                        Abrir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="small-muted">Nenhum ticket encontrado.</div>
        @endif
    </div>
@endsection
