<x-app-layout>
    <title>Support Chat - Ticket #{{ $ticket->ticket_reference }}</title>
    
    <style>
        /* WhatsApp-like & Security Styles */
        body {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
        
        @media print {
            html, body {
                display: none !important;
            }
        }

        .chat-background {
            background-color: #efe7dd;
            background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
            background-repeat: repeat;
        }

        .message-bubble {
            max-width: 80%;
            padding: 8px 12px;
            border-radius: 7.5px;
            position: relative;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
            margin-bottom: 10px;
            font-size: 14.2px;
            line-height: 19px;
        }

        .message-in {
            background-color: #fff;
            align-self: flex-start;
            border-top-left-radius: 0;
        }

        .message-out {
            background-color: #d9fdd3;
            align-self: flex-end;
            border-top-right-radius: 0;
            margin-left: auto;
        }

        .message-meta {
            font-size: 11px;
            color: rgba(0, 0, 0, 0.45);
            text-align: right;
            margin-top: 4px;
            display: block;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 75vh;
        }

        .typing-indicator {
            background: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
            align-self: flex-start;
            display: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .typing-dot {
            height: 6px;
            width: 6px;
            background-color: #666;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: typing 1.4s infinite ease-in-out both;
        }
        
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        .avatar-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>

    <div class="page-body">
        <div class="container-fluid">
            <!-- Header -->
            <div class="page-title mb-3">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('support.index') }}" class="btn btn-icon btn-light rounded-circle">
                                <i class="ti ti-arrow-left"></i>
                            </a>
                            <div>
                                <h4 class="fw-bold text-primary mb-0">
                                    @if($ticket->user)
                                        {{ $ticket->user->first_name }} {{ $ticket->user->last_name }} {{ $ticket->user->middle_name }}
                                    @else
                                        User
                                    @endif
                                </h4>
                                <small class="text-muted">Ticket #{{ $ticket->ticket_reference }} • {{ ucfirst($ticket->status) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 chat-container">
                        
                        <!-- Chat Area -->
                        <div class="card-body chat-background overflow-auto p-4" id="messages-area" style="flex: 1;">
                            
                            @foreach($ticket->messages as $message)
                                <div class="d-flex mb-3 {{ $message->is_admin_reply ? 'justify-content-start' : 'justify-content-end' }} message-wrapper" data-id="{{ $message->id }}">
                                    @if($message->is_admin_reply)
                                        <div class="me-2 mt-1">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="ti ti-headset"></i>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="message-bubble {{ $message->is_admin_reply ? 'message-in' : 'message-out' }}">
                                        @if($message->is_admin_reply)
                                            <div class="fw-bold text-primary mb-1" style="font-size: 0.8rem;">Admin Support</div>
                                        @endif
                                        
                                        <div class="message-content text-dark">
                                            {!! nl2br(e($message->message)) !!}
                                        </div>

                                        @if($message->attachment)
                                            <div class="mt-2 p-2 bg-light rounded border">
                                                <a href="{{ Storage::url($message->attachment) }}" target="_blank" class="d-flex align-items-center text-decoration-none text-dark">
                                                    <i class="ti ti-file me-2 text-primary"></i>
                                                    <span class="small text-truncate" style="max-width: 150px;">View Attachment</span>
                                                </a>
                                            </div>
                                        @endif

                                        <span class="message-meta">
                                            {{ $message->created_at->format('h:i A') }}
                                            @if(!$message->is_admin_reply)
                                                <i class="ti ti-check-double text-primary ms-1" style="font-size: 10px;"></i>
                                            @endif
                                        </span>
                                    </div>

                                    @if(!$message->is_admin_reply)
                                        <div class="ms-2 mt-1">
                                            <!-- Specific User Image URL as requested -->
                                            <img src="http://127.0.0.1:8000/storage/uploads/tin/passport/1phLlk2TKVB5pSplQXbLHfjKKSofR7zSPA0N0NA6.jpg" alt="User" class="avatar-img">
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <!-- Typing Indicator -->
                            <div id="typing-indicator" class="typing-indicator">
                                <span>Admin is typing</span>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>

                        <!-- Reply Input Area -->
                        <div class="card-footer bg-light p-3">
                            @if($ticket->status !== 'closed')
                                <form id="reply-form" action="{{ route('support.reply', $ticket->ticket_reference) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="d-flex align-items-end gap-2">
                                        <button type="button" class="btn btn-link text-secondary p-2" onclick="document.getElementById('replyAttachment').click()">
                                            <i class="ti ti-paperclip fs-4"></i>
                                        </button>
                                        <input type="file" name="attachment" id="replyAttachment" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                                        
                                        <div class="flex-grow-1 position-relative">
                                            <textarea name="message" id="message-input" class="form-control border-0 rounded-pill px-4 py-2" rows="1" placeholder="Type a message" style="resize: none; overflow: hidden; min-height: 45px; max-height: 120px;" required></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="ti ti-send fs-4 ms-1"></i>
                                        </button>
                                    </div>
                                    <small class="text-primary ms-5" id="fileNameDisplay"></small>
                                </form>
                            @else
                                <div class="alert alert-secondary mb-0 text-center rounded-pill">
                                    <i class="ti ti-lock"></i> This ticket is closed.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const messagesArea = document.getElementById('messages-area');
                const replyForm = document.getElementById('reply-form');
                const typingIndicator = document.getElementById('typing-indicator');
                const messageInput = document.getElementById('message-input');
                let lastMessageId = {{ $ticket->messages->last()?->id ?? 0 }};
                
                // Auto-scroll to bottom
                function scrollToBottom() {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
                scrollToBottom();

                // Auto-resize textarea
                if(messageInput){
                    messageInput.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                }

                // File Attachment Display
                document.getElementById('replyAttachment')?.addEventListener('change', function(e) {
                    const fileName = e.target.files[0]?.name;
                    document.getElementById('fileNameDisplay').textContent = fileName ? 'Attached: ' + fileName : '';
                });

                // Polling for Updates
                setInterval(fetchUpdates, 5000);

                function fetchUpdates() {
                    fetch(`{{ route('support.updates', $ticket->ticket_reference) }}?last_message_id=${lastMessageId}`)
                        .then(response => response.json())
                        .then(data => {
                            // Update Typing Status
                            if(data.is_typing) {
                                typingIndicator.style.display = 'block';
                                scrollToBottom();
                            } else {
                                typingIndicator.style.display = 'none';
                            }

                            // Append New Messages
                            if(data.messages && data.messages.length > 0) {
                                data.messages.forEach(msg => {
                                    appendMessage(msg);
                                    lastMessageId = msg.id;
                                });
                                scrollToBottom();
                                typingIndicator.style.display = 'none'; // Hide typing if message arrived
                            }
                        })
                        .catch(console.error);
                }

                function appendMessage(msg) {
                    const isAdmin = msg.is_admin_reply == 1; // flexible check
                    const alignClass = isAdmin ? 'justify-content-start' : 'justify-content-end';
                    const bubbleClass = isAdmin ? 'message-in' : 'message-out';
                    
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    let html = `
                        <div class="d-flex mb-3 ${alignClass} message-wrapper" data-id="${msg.id}">
                            ${isAdmin ? `
                                <div class="me-2 mt-1">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="ti ti-headset"></i>
                                    </div>
                                </div>
                            ` : ''}

                            <div class="message-bubble ${bubbleClass}">
                                ${isAdmin ? `<div class="fw-bold text-primary mb-1" style="font-size: 0.8rem;">Admin Support</div>` : ''}
                                
                                <div class="message-content text-dark">
                                    ${msg.message.replace(/\n/g, '<br>')}
                                </div>

                                ${msg.attachment ? `
                                    <div class="mt-2 p-2 bg-light rounded border">
                                        <a href="/storage/${msg.attachment}" target="_blank" class="d-flex align-items-center text-decoration-none text-dark">
                                            <i class="ti ti-file me-2 text-primary"></i>
                                            <span class="small">View Attachment</span>
                                        </a>
                                    </div>
                                ` : ''}

                                <span class="message-meta">
                                    ${time}
                                    ${!isAdmin ? '<i class="ti ti-check-double text-primary ms-1" style="font-size: 10px;"></i>' : ''}
                                </span>
                            </div>

                            ${!isAdmin ? `
                                <div class="ms-2 mt-1">
                                    <img src="http://127.0.0.1:8000/storage/uploads/tin/passport/1phLlk2TKVB5pSplQXbLHfjKKSofR7zSPA0N0NA6.jpg" alt="User" class="avatar-img">
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    // Insert before typing indicator
                    typingIndicator.insertAdjacentHTML('beforebegin', html);
                }

                // SECURITY: Restrict Screenshots & Interactions
                
                // 1. Disable Right Click
                document.addEventListener('contextmenu', event => event.preventDefault());

                // 2. Disable PrintScreen and other shortcuts
                document.addEventListener('keydown', function(e) {
                    // PrintScreen
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        e.preventDefault();
                        alert('Screenshots are disabled for security reasons.');
                        copyToClipboard(' '); // Clear clipboard
                        return false;
                    }
                    
                    // Ctrl+P (Print)
                    if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                        e.preventDefault();
                        return false;
                    }

                    // Ctrl+Shift+I (DevTools) - Optional but added for "very strong"
                    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i')) {
                        e.preventDefault();
                        return false;
                    }
                });

                // 3. Clear clipboard on keyup if suspected screenshot
                document.addEventListener('keyup', function(e) {
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        navigator.clipboard.writeText(''); 
                    }
                });

                function copyToClipboard(text) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text);
                    }
                }
            });
        </script>
    </div>
</x-app-layout>
