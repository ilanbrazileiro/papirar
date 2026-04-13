<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('student.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('student.tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'in:suggestion,technical,financial,question_submission'],
            'message' => ['required', 'string', 'min:10', 'max:10000'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => auth()->id(),
            'subject' => $this->categoryLabel($data['category']),
            'category' => $data['category'],
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'sender_type' => 'user',
        ]);

        return redirect()
            ->route('student.tickets.show', $ticket)
            ->with('success', 'Ticket aberto com sucesso.');
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless((int) $ticket->user_id === (int) auth()->id(), 403);

        $ticket->load([
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.user',
        ]);

        return view('student.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless((int) $ticket->user_id === (int) auth()->id(), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:10000'],
        ]);

        SupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'sender_type' => 'user',
        ]);

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->status = 'open';
        }

        $ticket->last_message_at = now();
        $ticket->save();

        return redirect()
            ->route('student.tickets.show', $ticket)
            ->with('success', 'Mensagem enviada com sucesso.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        abort_unless((int) $ticket->user_id === (int) auth()->id(), 403);

        $ticket->update([
            'status' => 'closed',
            'last_message_at' => now(),
        ]);

        return redirect()
            ->route('student.tickets.show', $ticket)
            ->with('success', 'Ticket fechado com sucesso.');
    }

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            'suggestion' => 'Sugestão',
            'technical' => 'Problema técnico',
            'financial' => 'Problema financeiro',
            'question_submission' => 'Enviar questões para avaliação',
            default => 'Atendimento',
        };
    }
}
