@extends('admin.layout')

@section('content')
    <style>
        .ticket-thread { display: grid; gap: 14px; }
        .ticket-message { display: flex; gap: 12px; align-items: flex-start; }
        .ticket-message.user { justify-content: flex-end; }
        .ticket-bubble {
            max-width: 820px; border-radius: 18px; padding: 16px;
            border: 1px solid #e5e7eb; background: #fff;
        }
        .ticket-message.user .ticket-bubble {
            background: #eff6ff; border-color: #bfdbfe;
        }
        .ticket-avatar {
            width: 40px; height: 40px; min-width: 40px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; background: #e5e7eb; color: #111827;
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

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h1 class="page-title">Ticket #{{ $ticket->id }}</h1>
            <div class="small-muted mb-1">{{ $ticket->subject }}</div>
            <div class="small-muted">Cliente: {{ $ticket->user->name ?? '-' }}</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.tickets.status.update', $ticket) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="open">
                <button class="btn btn-outline-primary" @disabled($ticket->status === 'open')>Reabrir</button>
            </form>

            <form method="POST" action="{{ route('admin.tickets.status.update', $ticket) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="in_progress">
                <button class="btn btn-outline-warning" @disabled($ticket->status === 'in_progress')>Marcar em andamento</button>
            </form>

            <form method="POST" action="{{ route('admin.tickets.status.update', $ticket) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="resolved">
                <button class="btn btn-outline-success" @disabled($ticket->status === 'resolved')>Marcar resolvido</button>
            </form>

            <form method="POST" action="{{ route('admin.tickets.status.update', $ticket) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="closed">
                <button class="btn btn-outline-danger" @disabled($ticket->status === 'closed')>Fechar</button>
            </form>

            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="card-soft p-4 mb-4">
        <div class="mb-3">
            @if($ticket->status === 'open')
                <span class="badge text-bg-primary">Aberto</span>
            @elseif($ticket->status === 'in_progress')
                <span class="badge text-bg-warning">Em andamento</span>
            @elseif($ticket->status === 'resolved')
                <span class="badge text-bg-success">Resolvido</span>
            @else
                <span class="badge text-bg-secondary">Fechado</span>
            @endif
        </div>

        @if($ticket->messages->count())
            <div class="ticket-thread">
                @foreach($ticket->messages as $message)
                    @php
                        $isUser = $message->sender_type === 'user';
                        $displayName = $isUser
                            ? ($message->user->name ?? 'Aluno')
                            : ($message->adminUser->name ?? 'Equipe Papirar');

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

                            <div style="white-space: pre-line;">{{ $message->message }}</div>

                            @if($message->attachments->count())
                                <div class="mt-3">
                                    <div class="small text-muted mb-2">Anexos:</div>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($message->attachments as $attachment)
                                            <a
                                                href="{{ asset('storage/' . $attachment->file_path) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-secondary text-start"
                                            >
                                                {{ $attachment->original_name }}
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
            <div class="small-muted">Ainda não há mensagens neste ticket.</div>
        @endif
    </div>

    <div class="card-soft p-4">
        <div class="section-title">Responder ticket</div>

        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data">
            @csrf

            <textarea class="form-control" name="message" rows="5" required>{{ old('message') }}</textarea>
            @error('message')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            <div class="mt-3">
                <label class="form-label">Anexos</label>
                <input
                    type="file"
                    name="attachments[]"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.pdf"
                    multiple
                >
                <div class="form-text">
                    Você pode enviar imagens JPG/PNG e arquivos PDF. Limite de 5 MB por arquivo.
                </div>
                @error('attachments.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary">Responder</button>
            </div>
        </form>
    </div>
@endsection
