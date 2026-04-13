<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::query()
            ->with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'messages.user', 'messages.adminUser']);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'admin_user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'pending']);
        }

        return back()->with('success', 'Resposta enviada com sucesso.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,pending,resolved,closed'],
        ]);

        $ticket->update(['status' => $data['status']]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }
}
