@extends('layouts.student')

@section('title', 'Meus tickets')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Suporte</h1>
            <p class="page-subtitle">Abra e acompanhe solicitações financeiras, técnicas, sugestões e envio de questões para avaliação.</p>
        </div>
        <a href="{{ route('student.tickets.create') }}" class="btn btn-primary">Novo ticket</a>
    </div>

    <div class="card-soft p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-1">Sugestão</div>
                    <div class="small-muted">Ideias para melhorar a plataforma.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-1">Problema técnico</div>
                    <div class="small-muted">Erros de acesso, tela, simulado ou funcionamento.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-1">Problema financeiro</div>
                    <div class="small-muted">Cobrança, assinatura, pagamento ou renovação.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-1">Enviar questões</div>
                    <div class="small-muted">Envie questões para avaliação da equipe.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-soft p-4">
        @if($tickets->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Assunto</th>
                            <th>Categoria</th>
                            <th>Status</th>
                            <th>Última atualização</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td class="fw-semibold">{{ $ticket->subject }}</td>
                                <td>
                                    @switch($ticket->category)
                                        @case('suggestion') Sugestão @break
                                        @case('technical') Problema técnico @break
                                        @case('financial') Problema financeiro @break
                                        @case('question_submission') Envio de questões @break
                                        @default {{ $ticket->category }}
                                    @endswitch
                                </td>
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
                                    <a href="{{ route('student.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
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
            <div class="small-muted">Você ainda não abriu tickets.</div>
        @endif
    </div>
@endsection
