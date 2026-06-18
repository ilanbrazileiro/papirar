@extends('layouts.student')

@section('title', 'Ticket de suporte')

@section('content')
    <style>
        .support-header {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 72%, #facc15 100%);
            border-radius: 22px;
            color: #fff;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
        }
        .support-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .82rem;
            font-weight: 700;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .24);
        }
        .ticket-thread { display: grid; gap: 16px; }
        .ticket-message { display: flex; gap: 12px; align-items: flex-start; }
        .ticket-message.user { justify-content: flex-end; }
        .ticket-bubble {
            max-width: 790px;
            border-radius: 18px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        }
        .ticket-message.user .ticket-bubble {
            background: #eff6ff;
            border-color: #bfdbfe;
        }
        .ticket-avatar {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            background: #dbeafe;
            color: #1d4ed8;
        }
        .ticket-message.user .ticket-avatar {
            background: #1d4ed8;
            color: #fff;
        }
        .ticket-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 6px;
        }
        .ticket-name { font-weight: 800; }
        .ticket-time { color: #6b7280; font-size: .9rem; }
        .ticket-attachment {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 8px 10px;
            color: #334155;
            text-decoration: none;
            background: #f8fafc;
        }
        .ticket-attachment:hover { background: #eef2ff; color: #1d4ed8; text-decoration: none; }
    </style>

    <div class="support-header mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="support-chip">#{{ $ticket->id }}</span>
                    <span class="support-chip">{{ $ticket->category_label }}</span>
                    <span class="support-chip">{{ $ticket->status_label }}</span>
                </div>
                <h1 class="mb-2" style="font-weight: 900; letter-spacing: -.03em;">{{ $ticket->subject }}</h1>
                <p class="mb-0" style="max-width: 760px; opacity: .92;">
                    Use esta conversa para acompanhar o atendimento da equipe Papirar. Envie detalhes, prints ou PDF quando isso ajudar a resolver mais rápido.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('student.tickets.close', $ticket) }}">
                        @csrf
                        <button class="btn btn-light" onclick="return confirm('Deseja fechar este ticket?');">Fechar ticket</button>
                    </form>
                @endif

                <a href="{{ route('student.tickets.index') }}" class="btn btn-outline-light">Voltar</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card-soft p-4 mb-4">
        @if($ticket->messages->count())
            <div class="ticket-thread">
                @foreach($ticket->messages as $ticketMessage)
                    @php
                        $isUser = $ticketMessage->sender_type === 'user';
                        $displayName = $isUser
                            ? ($ticketMessage->user->name ?? 'Você')
                            : ($ticketMessage->adminUser->name ?? 'Equipe Papirar');

                        if ($isUser && (int) ($ticketMessage->user_id ?? 0) === (int) auth()->id()) {
                            $displayName = 'Você';
                        }

                        $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
                    @endphp

                    <div class="ticket-message {{ $isUser ? 'user' : '' }}">
                        @if(!$isUser)
                            <div class="ticket-avatar">{{ $initial }}</div>
                        @endif

                        <div class="ticket-bubble">
                            <div class="ticket-meta">
                                <span class="ticket-name">{{ $displayName }}</span>
                                <span class="ticket-time">{{ optional($ticketMessage->created_at)->format('d/m/Y H:i') }}</span>
                            </div>

                            <div style="white-space: pre-line; line-height: 1.65;">{{ $ticketMessage->message }}</div>

                            @if($ticketMessage->attachments->count())
                                <div class="mt-3">
                                    <div class="small text-muted mb-2">Anexos</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($ticketMessage->attachments as $attachment)
                                            <a href="{{ $attachment->public_url }}" target="_blank" class="ticket-attachment">
                                                <span>📎</span>
                                                <span>{{ $attachment->original_name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($isUser)
                            <div class="ticket-avatar">{{ $initial }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted">Ainda não há mensagens neste ticket.</div>
        @endif
    </div>

    @if($ticket->status !== 'closed')
        <div class="card-soft p-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <div class="section-title mb-1">Responder atendimento</div>
                    <div class="text-muted small">A equipe Papirar receberá sua mensagem neste mesmo ticket.</div>
                </div>
            </div>

            <form method="POST" action="{{ route('student.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                @csrf

                <textarea name="message" rows="6" class="form-control" placeholder="Descreva sua dúvida, problema ou atualização..." required>{{ old('message') }}</textarea>
                @error('message')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <div class="mt-3">
                    <label class="form-label">Anexos</label>
                    <input type="file" name="attachments[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                    <div class="form-text">JPG, PNG ou PDF. Limite de 5 MB por arquivo.</div>
                    @error('attachments.*')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-primary">Enviar resposta</button>
                </div>
            </form>
        </div>
    @else
        <div class="alert alert-secondary">
            Este ticket está fechado. Abra um novo chamado se precisar de novo atendimento.
        </div>
    @endif
@endsection
