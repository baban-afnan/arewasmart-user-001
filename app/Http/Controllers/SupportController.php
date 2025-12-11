<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('support.index', compact('tickets'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'ticket_reference' => 'TKT-' . strtoupper(Str::random(8)),
            'subject' => $request->subject,
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('uploads/support', 'public');
        }

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
            'is_admin_reply' => false,
        ]);

        // Send Auto Reply
        $this->sendAutoReply($ticket);

        return redirect()->route('support.show', $ticket->ticket_reference)
            ->with('success', 'Ticket created successfully! Ticket ID: ' . $ticket->ticket_reference);
    }

    public function show($reference)
    {
        $ticket = SupportTicket::where('ticket_reference', $reference)
            ->where('user_id', Auth::id())
            ->with('messages.user')
            ->firstOrFail();

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, $reference)
    {
        $ticket = SupportTicket::where('ticket_reference', $reference)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('uploads/support', 'public');
        }

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
            'is_admin_reply' => false,
        ]);

        $ticket->update(['status' => 'customer_reply', 'updated_at' => now()]);

        // Send Auto Reply
        $this->sendAutoReply($ticket);

        if ($request->wantsJson()) {
            // Eager load user for the response
            $message = SupportMessage::with('user')->find($message->id);
            return response()->json([
                'success' => true,
                'message' => $message,
                'ticket_status' => $ticket->status
            ]);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    private function sendAutoReply(SupportTicket $ticket)
    {
        // Only send auto reply if this is the first message
        $messageCount = SupportMessage::where('support_ticket_id', $ticket->id)->count();
        if ($messageCount > 1) {
            return;
        }

        $now = now();
        $isWeekend = $now->isSaturday() || $now->isSunday();
        
        if ($isWeekend) {
            $message = "Thank you for reaching out. Please note that our support team is currently unavailable as we don't work on weekends. We will attend to your request on the next working day.\n\nFor urgent matters, please chat with us on WhatsApp: https://wa.me/2349110501995";
        } else {
            $message = "Thank you for contacting us. Your request has been received, and a support agent will respond to you shortly. Please hold on.";
        }

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => null, // System message
            'message' => $message,
            'is_admin_reply' => true,
        ]);
    }

    public function fetchUpdates(Request $request, $reference)
    {
        $ticket = SupportTicket::where('ticket_reference', $reference)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check for new messages since the last loaded message ID
        $lastMessageId = $request->input('last_message_id', 0);
        
        $messages = SupportMessage::where('support_ticket_id', $ticket->id)
            ->where('id', '>', $lastMessageId)
            ->with('user') 
            ->orderBy('created_at', 'asc')
            ->get();

       
        // Key format assumption: admin_typing_TICKETID
        $isTyping = \Illuminate\Support\Facades\Cache::get('admin_typing_' . $ticket->id, false);

        return response()->json([
            'messages' => $messages,
            'is_typing' => $isTyping,
        ]);
    }
}
