<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::query()
            ->with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderByRaw("CASE WHEN status IN ('open', 'in_progress') THEN 0 ELSE 1 END")
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'messages' => fn ($q) => $q->orderBy('created_at'),
            'messages.user',
            'messages.adminUser',
            'messages.attachments',
        ]);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:10000'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $data, $ticket) {
            $message = SupportTicketMessage::query()->create([
                'ticket_id' => $ticket->id,
                'admin_user_id' => auth()->id(),
                'message' => $data['message'],
                'sender_type' => 'admin',
            ]);

            $this->storeAttachments($request, $message);

            if (in_array($ticket->status, ['open', 'resolved', 'closed'], true)) {
                $ticket->status = 'in_progress';
            }

            $ticket->last_message_at = now();
            $ticket->save();
        });

        return back()->with('success', 'Resposta enviada com sucesso.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket->update([
            'status' => $data['status'],
            'last_message_at' => now(),
        ]);

        return back()->with('success', 'Status atualizado com sucesso.');
    }

    private function storeAttachments(Request $request, SupportTicketMessage $message): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $path = $file->store('support/tickets', 'public');

            $message->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                'extension' => $file->getClientOriginalExtension(),
                'file_size' => (int) $file->getSize(),
            ]);
        }
    }
}
