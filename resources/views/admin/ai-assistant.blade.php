@extends('layouts.app')

@section('content')
<style>
    .chat-wrapper {
        max-width: 700px;
        margin: 20px auto;
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    .chat-bubble {
        margin-bottom: 10px;
        padding: 12px 16px;
        border-radius: 20px;
        max-width: 80%;
        display: inline-block;
        white-space: pre-wrap;
    }

    .chat-user {
        background-color: #007bff;
        color: white;
        float: right;
        clear: both;
        border-bottom-right-radius: 0;
    }

    .chat-bot {
        background-color: #f1f1f1;
        float: left;
        clear: both;
        border-bottom-left-radius: 0;
    }

    .chat-input {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .chat-input input {
        flex: 1;
        border-radius: 20px;
        padding: 10px;
        border: 1px solid #ccc;
    }

    .chat-input button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 0 20px;
        border-radius: 20px;
    }
</style>

<div class="chat-wrapper">
    <h4 class="mb-3">🤖 AI POS Assistant</h4>

    <div id="chat-box" style="height: 400px; overflow-y: auto;"></div>

    <div class="chat-input">
        <input type="text" id="chat-input" placeholder="Ask something like: 'Top products this week'" />
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
function sendMessage() {
    const input = document.getElementById('chat-input');
    const chatBox = document.getElementById('chat-box');
    const message = input.value.trim();
    if (!message) return;

    const userBubble = document.createElement('div');
    userBubble.className = 'chat-bubble chat-user';
    userBubble.textContent = 'You: ' + message;
    chatBox.appendChild(userBubble);
    input.value = '';

    fetch("{{ route('admin.ai.chat') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ message }),
    })
    .then(res => res.json())
    .then(data => {
        const botBubble = document.createElement('div');
        botBubble.className = 'chat-bubble chat-bot';
        botBubble.innerHTML = '🤖 ' + data.reply.replace(/\n/g, "<br>");
        chatBox.appendChild(botBubble);
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}
</script>
@endsection
