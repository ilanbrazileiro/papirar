@extends('layouts.student')

@section('title', 'Ticket de suporte')

@section('content')
    <style>
        .ticket-thread { display: grid; gap: 14px; }
        .ticket-message { display: flex; gap: 12px; align-items: flex-start; }
        .ticket-message.user { justify-content: flex-end; }
        .ticket-bubble {
            max-width: 760px; border-radius: 18px; padding: 16px;
            border: 1px solid #e5e7eb; background: #fff;
        }
        .ticket-message.user .ticket-bubble {
            background: #eff6ff; border-color: #bfdbfe;
        }
        .ticket-avatar {
            width: 40px; height: 40px; min-width: 40px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; background: #dbeafe; color: #1d4ed8;
        }
        .ticket-message.user .ticket-avatar {
            background: #bfdbfe; color: #1e3a8a;
        }
        .ticket-meta {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 6px;
        }
        .ticket-name { font-weight: 700; }
        .ticket-time { color: #6b7280; font-size: .92rem; }
    </style>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="page-title">{{ $ticket->subject }}</h1>
            <p class="page-subtitle">
                @switch($ticket->category)
                    @case('suggestion') Sugestão @break
                    @case('technical') Problema técnico @break
                    @case('financial') Problema financeiro @break
                    @case('question_submission') Envio de questões para avaliação @break
                    @default {{ $ticket->category }}
                @endswitch
                ·
                @if($ticket->status === 'open')
                    Aberto
                @elseif($ticket->status === 'in_progress')
                    Em andamento
                @elseif($ticket->status === 'resolved')
                    Resolvido
                @else
                    Fechado
                @endif
            </p>
        </div>

        <div class="d-flex gap-2">
            @if($ticket->status !== 'closed')
                <form method="POST" action="{{ route('student.tickets.close', $ticket) }}">
                    @csrf
                    <button class="btn btn-outline-danger" onclick="return confirm('Deseja fechar este ticket?');">Fechar ticket</button>
                </form>
            @endif

            <a href="{{ route('student.tickets.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="card-soft p-4 mb-4">
        <div class="ticket-thread">
            @foreach($ticket->messages as $message)
                @php
                    $isUser = (int) ($message->user_id ?? 0) === (int) auth()->id();
                    $displayName = $isUser ? 'Você' : ($message->user->name ?? 'Equipe Papirar');
                    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
                @endphp

                <div class="ticket-message {{ $isUser ? 'user' : '' }}">
                    @if(!$isUser)
                        <div class="ticket-avatar">{{ $initial }}</div>
                    @endif

                    <div class="ticket-bubble">
                        <div class="ticket-meta">
                            <span class="ticket-name">{{ $displayName }}</span>
                            <span class="ticket-time">{{ optional($message->created_at)->format('d/m/Y H:i') }}</span>
                        </div>

                        <div>{{ $message->message }}</div>
                    </div>

                    @if($isUser)
                        <div class="ticket-avatar">{{ $initial }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @if(!in_array($ticket->status, ['closed'], true))
        <div class="card-soft p-4">
            <div class="section-title">Responder ticket</div>

            <form method="POST" action="{{ route('student.tickets.reply', $ticket) }}">
                @csrf
                <textarea name="message" rows="6" class="form-control" required>{{ old('message') }}</textarea>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-primary">Enviar resposta</button>
                </div>
            </form>
        </div>
    @endif
@endsection
