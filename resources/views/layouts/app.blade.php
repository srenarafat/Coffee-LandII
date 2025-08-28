<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.pos') }} System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanuman:wght@100;300;400;700;900&family=Noto+Sans+Khmer:wght@100..900&display=swap" rel="stylesheet">
<style>
    body {
        background-color: #f2f2f2;
        margin: 0;
        padding: 0;
        display: flex;
        min-height: 100vh;
        font-family: 'Battambang', 'Noto Sans Khmer', sans-serif;
    }
     /* 🔽 ADD THIS PART: */
  .sidebar-icon.active {
      background-color: #198754;
      border-radius: 8px;
  }


  .sidebar-icon.active i,
  .sidebar-icon.active small {
      color: #ffffff !important;
  }




    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 80px;
        background-color: #fff;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 1060;
        border-right: 1px solid #eaeaea;
        overflow: visible;
    }


    .sidebar-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        padding: 8px 0;
        width: 100%;
        transition: background 0.2s ease-in-out;
        border-radius: 8px;
    }


    .sidebar-icon i {
        color: #372626;
        font-size: 16px;
    }


    .sidebar-icon small {
        font-size: 12px;
        font-weight: 600;
        color: #372626;
        line-height: 1;
        margin-top: 2px;
    }


    .sidebar-icon:hover {
    background-color:  #0a660a; /* light green background */
}


.sidebar-icon:hover i,
.sidebar-icon:hover small {
    color: #ffffff !important; /* Bootstrap success green */
}




    .main-content {
        margin-left: 72px;
        padding: 20px;
        width: calc(100% - 72px);
        transition: all 0.3s ease;
    }


    /* Removed expandable sidebar support */


       .sidebar .text-center img {
        width: 38px;
        height: 38px;
    }


    .logout-section img {
        width: 34px;
        height: 34px;
    }


.logout-section small {
        font-size: 10px;
        line-height: 1.1;
    }


    /* Chat message bubbles */
    .message {
        margin-bottom: 6px;
        padding: 6px 10px;
        border-radius: 12px;
        max-width: 75%;
        word-wrap: break-word;
    }


    .user-message {
        background: #d1e7dd;
        align-self: flex-end;
    }


    .bot-message {
        background: #f8f9fa;
        align-self: flex-start;
    }


    @media print {
         @page {
        size: A4;
        margin: 1cm;
    }


    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }


    body * {
        visibility: hidden !important;
    }

    /* Ensure report content is visible when printing */
    .main-content, .main-content * {
        visibility: visible !important;
    }

        .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 0 !important;
    }




    .sidebar, .navbar, .btn, .logout-section,
    form[action*="logout"], .dropdown,
    .dropdown-menu, .d-print-none {
        display: none !important;
    }
    /* Prevent horizontal scroll */
html, body {
    overflow-x: hidden;
}

/* Responsive content area */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        width: 100%;
        padding: 1rem !important;
    }
    .sidebar {
        display: none;
    }
    .mobile-toggle {
        display: block !important;
    }
    .table-responsive {
    overflow-x: auto;
    }

}

}
</style>


    @vite('resources/css/dashboard.css')
    @stack('styles')
</head>
<body>
<div id="sidebar" class="sidebar d-flex flex-column bg-white text-center py-3" style="height: 100vh; position: fixed; overflow: visible; z-index: 1060;">
     <!-- Sidebar no longer expandable -->
    <div class="d-flex flex-column align-items-center">
        <div class="text-center pt-3 mb-3">
            <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.pos.index') : (auth()->user()->role === 'admin' ? route('admin.pos.index') : route('cashier.pos.index')) }}">
                <img src="{{ asset('images/coffeeland-logo.png') }}" alt="CoffeeLand Logo"
                     class="rounded-circle shadow" style="width: 48px; height: 48px; object-fit: cover;">
            </a>
        </div>
<div class="d-flex flex-column gap-0">
    <!-- 🔁 Shared POS Icon -->
    <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.pos.index') : (auth()->user()->role === 'admin' ? route('admin.pos.index') : route('cashier.pos.index')) }}"
       class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
              {{ request()->routeIs(auth()->user()->role . '.pos.index') ? 'active' : '' }}">
        <i class="bi bi-basket2-fill fs-5"></i>
        <small class="mt-1 fw-bold" style="font-size: 12px;">{{ __('messages.pos') }}</small>
    </a>


    {{-- 🌟 Admin --}}
    @if (auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.dashboard') }}</small>
        </a>


        <a href="{{ route('admin.categories.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.category') }}</small>
        </a>


        <a href="{{ route('admin.products.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.menu_items') }}</small>
        </a>


        <a href="{{ route('admin.ingredient-stock.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.ingredient-stock.*') ? 'active' : '' }}">
            <i class="bi bi-box-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.product') }}</small>
        </a>


        <a href="{{ route('admin.sales.report') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.Sale report') }}</small>
        </a>


        <a href="{{ route('admin.reports.topQuantitySales') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-star-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.top_qty') }}</small>
        </a>


        <a href="{{ route('admin.users.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.users') }}</small>
        </a>


    {{-- 🛠️ SuperAdmin --}}
    @elseif (auth()->user()->role === 'superadmin')
        <a href="{{ route('superadmin.dashboard') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.dashboard') }}</small>
        </a>


        <a href="{{ route('superadmin.categories.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.category') }}</small>
        </a>


        <a href="{{ route('superadmin.products.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none {{ request()->routeIs('superadmin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.menu_items') }}</small>
        </a>


        <a href="{{ route('superadmin.ingredient-stock.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.ingredient-stock.*') ? 'active' : '' }}">
            <i class="bi bi-box-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.product') }}</small>
        </a>


        <a href="{{ route('superadmin.sales.report') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.sales.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.Sale report') }}</small>
        </a>


        <a href="{{ route('superadmin.reports.topQuantitySales') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-star-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.top_qty') }}</small>
        </a>


        <a href="{{ route('superadmin.users.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.users') }}</small>
        </a>


        <a href="{{ route('superadmin.settings.index') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.setting') }}</small>
        </a>


    {{-- 👨‍🍳 Cashier --}}
     @else
            <a href="{{ route('cashier.sales.history') }}"
           class="sidebar-icon d-flex flex-column align-items-center text-decoration-none
                  {{ request()->routeIs('cashier.sales.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill fs-5"></i>
            <small class="mt-1 fw-bold">{{ __('messages.Sale report') }}</small>
        </a>
    @endif
</div>


    </div>




    <div class="mt-auto d-flex flex-column justify-content-center align-items-center text-center">
        <div class="dropdown mt-auto d-flex flex-column justify-content-center align-items-center text-center w-100 mb-3" style="position: relative;">
            <button class="btn dropdown-toggle text-dark d-flex flex-column align-items-center p-0 border-0 bg-transparent"
                    type="button" id="sidebarProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ asset(Auth::user()->profile_image ?? 'images/default-avatar.png') }}"
                     class="rounded-circle shadow mb-1" width="38" height="38" style="object-fit: cover;">
                <small class="fw-bold">{{ Auth::user()->name }}</small>
                <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
            </button>
            <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="sidebarProfileDropdown" style="z-index: 1080; position: absolute;">
                <li>
                    <a class="dropdown-item {{ request()->routeIs('profile.info') ? 'active' : '' }}" href="{{ route('profile.info') }}">👤 {{ __('messages.user_profile') }}</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">🚪 {{ __('messages.logout') }}</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>


<div class="main-content" id="mainContent">
    @yield('content')
    <!-- Floating Chatbot Button -->
<div id="chatbotButton" class="d-print-none" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; cursor: grab;">
    <img src="{{ asset('images/chatbot-icon.png') }}" alt="Chatbot" style="width: 60px; height: 60px;" draggable="false">
</div>


<!-- Chatbot Modal -->
<div id="chatbotPopup" class="d-print-none" style="display: none; position: fixed; bottom: 100px; right: 30px; z-index: 9998; background: #fff; width: 500px; height: 600px; box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 12px; overflow: hidden;">
    <div id="chatbotHeader" style="background: #198754;
 color: white; padding: 10px; font-weight: bold; cursor: grab; display:flex; justify-content:space-between; align-items:center;">
        <span>{{ __('messages.chatbot_pos_assistant') }}</span>
        <button type="button" id="chatbotCancel" style="background:transparent; border:none; color:white; font-size:1.5rem;">&times;</button>
    </div>
     <div style="padding: 10px; height: 480px; overflow-y: auto; display:flex; flex-direction:column;" id="chatbotMessages">
        <p>{{ __('messages.chatbot_greeting') }}</p>
        <p class="mb-2">{{ __('messages.chatbot_prompt') }}</p>
        <ul class="list-unstyled mb-3 row row-cols-2 g-1" id="chatbotSuggestions">
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_top_products_this_week') }}</li>
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_first_sale_today') }}</li>
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_slow_selling_this_month') }}</li>
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_fastest_selling_products') }}</li>
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_daily_sales_last_7_days') }}</li>
            <li class="suggestion-item col" style="cursor:pointer; color:#0d6efd;">{{ __('messages.chatbot_suggestion_total_sales_this_month') }}</li>


        </ul>
        <!-- Chat messages will be appended here -->
    </div>
    <div style="padding: 10px; border-top: 1px solid #eaeaea;">
        <div class="input-group mb-2">
            <input type="text" id="chatbotInput" class="form-control" placeholder="{{ __('messages.chatbot_input_placeholder') }}">
            <button class="btn" id="chatbotSend" style="background-color:  #198754; color: white; border: none;">{{ __('messages.chatbot_send') }}</button>
        </div>
    </div>
</div>
</div>
<!-- Sidebar toggle removed -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@push('scripts')
<script>
    const button = document.getElementById('chatbotButton');
    const popup = document.getElementById('chatbotPopup');
    const popupHeader = document.getElementById('chatbotHeader');
    const messages = document.getElementById('chatbotMessages');
    const input = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSend');
    const cancelBtn = document.getElementById('chatbotCancel');
    const suggestions = document.querySelectorAll('#chatbotSuggestions .suggestion-item');
    const unauthorizedMsg = "{{ __('messages.chatbot_unauthorized') }}";
    const failedMsg = "{{ __('messages.chatbot_failed_to_send') }}";




    // Restore chat history and open state from localStorage
    let history = JSON.parse(localStorage.getItem('chatbotHistory') || '[]');
    history.forEach(msg => {
        const div = document.createElement('div');
        div.classList.add('message');
        if (msg.from === 'user') {
            div.classList.add('user-message');
            div.textContent = msg.text;
        } else {
            div.classList.add('bot-message');
            div.innerHTML = '🤖 ' + msg.text.replace(/\n/g, '<br>');
        }
        messages.appendChild(div);
    });
    if (history.length) {
        messages.scrollTop = messages.scrollHeight;
    }
    if (localStorage.getItem('chatbotOpen') === 'true') {
        popup.style.display = 'block';
        button.style.display = 'none';
    }


    // Open popup and hide button on click if not dragged
    button.addEventListener('click', () => {
        if (!dragMoved) {
            popup.style.display = 'block';
            button.style.display = 'none';
            localStorage.setItem('chatbotOpen', 'true');
        }
        dragMoved = false; // reset after click
    });


    // Close popup and show button on cancel
    cancelBtn.addEventListener('click', () => {
        popup.style.display = 'none';
        button.style.display = 'block';
        localStorage.setItem('chatbotOpen', 'false');
        messages.querySelectorAll('.message').forEach(msg => msg.remove());
        history = [];
        localStorage.removeItem('chatbotHistory');
    });


    function appendMessage(text, from = 'user') {
        const div = document.createElement('div');
        div.classList.add('message');
        if (from === 'user') {
             div.classList.add('user-message');
            div.textContent = text;
        } else {
            div.classList.add('bot-message');
            div.innerHTML = '🤖 ' + text.replace(/\n/g, '<br>');
        }
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        history.push({ text, from });
        localStorage.setItem('chatbotHistory', JSON.stringify(history));
    }


    function sendMessage(msg) {
        const message = msg || input.value.trim();
        if (!message) return;
        appendMessage(message, 'user');
        input.value = '';
        fetch("{{ route(Auth::user()->role . '.ai.chat') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ message }),
        })
        .then(res => {
            if (!res.ok) {
                if (res.status === 401) {
                    throw new Error('unauthorized');
                }
                throw new Error('network');
            }
            return res.json();
        })
        .then(data => {
            appendMessage(data.reply, 'bot');
            })
        .catch(err => {
            console.error(err);
            const msg = err.message === 'unauthorized'
                ? unauthorizedMsg
                : failedMsg;
            appendMessage(msg, 'bot');
        });
    }


    sendBtn.addEventListener('click', () => sendMessage());
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    suggestions.forEach((item) => {
        item.addEventListener('click', () => {
            input.value = item.textContent;
            sendMessage(item.textContent);
        });
    });


    // Make button draggable
    let isDragging = false;
    let dragMoved = false;
    let offsetX, offsetY;
    let isPopupDragging = false;
    let popupOffsetX, popupOffsetY;


    button.addEventListener('mousedown', function(e) {
        isDragging = true;
        dragMoved = false;
        offsetX = e.clientX - button.getBoundingClientRect().left;
        offsetY = e.clientY - button.getBoundingClientRect().top;
        button.style.cursor = 'grabbing';
    });
    button.addEventListener('touchstart', function(e) {
        isDragging = true;
        dragMoved = false;
        offsetX = e.touches[0].clientX - button.getBoundingClientRect().left;
        offsetY = e.touches[0].clientY - button.getBoundingClientRect().top;
        button.style.cursor = 'grabbing';
    });


    document.addEventListener('mousemove', function(e) {
        if (isDragging) {
             dragMoved = true;
            const x = e.clientX - offsetX;
            const y = e.clientY - offsetY;
            button.style.left = `${x}px`;
            button.style.top = `${y}px`;
            button.style.right = 'auto';
            button.style.bottom = 'auto';
        }
    });
    document.addEventListener('touchmove', function(e) {
        if (isDragging) {
            dragMoved = true;
            const x = e.touches[0].clientX - offsetX;
            const y = e.touches[0].clientY - offsetY;
            button.style.left = `${x}px`;
            button.style.top = `${y}px`;
            button.style.right = 'auto';
            button.style.bottom = 'auto';
        }
    });


    document.addEventListener('mouseup', function() {
        isDragging = false;
        button.style.cursor = 'grab';
    });
    document.addEventListener('touchend', function() {
        isDragging = false;
        button.style.cursor = 'grab';
    });


    // Make popup draggable using its header
    popupHeader.addEventListener('mousedown', function(e) {
        isPopupDragging = true;
        popupOffsetX = e.clientX - popup.getBoundingClientRect().left;
        popupOffsetY = e.clientY - popup.getBoundingClientRect().top;
        popupHeader.style.cursor = 'grabbing';
    });
    popupHeader.addEventListener('touchstart', function(e) {
        isPopupDragging = true;
        popupOffsetX = e.touches[0].clientX - popup.getBoundingClientRect().left;
        popupOffsetY = e.touches[0].clientY - popup.getBoundingClientRect().top;
        popupHeader.style.cursor = 'grabbing';
    });


    document.addEventListener('mousemove', function(e) {
        if (isPopupDragging) {
            const x = e.clientX - popupOffsetX;
            const y = e.clientY - popupOffsetY;
            popup.style.left = `${x}px`;
            popup.style.top = `${y}px`;
            popup.style.right = 'auto';
            popup.style.bottom = 'auto';
        }
    });
    document.addEventListener('touchmove', function(e) {
        if (isPopupDragging) {
            const x = e.touches[0].clientX - popupOffsetX;
            const y = e.touches[0].clientY - popupOffsetY;
            popup.style.left = `${x}px`;
            popup.style.top = `${y}px`;
            popup.style.right = 'auto';
            popup.style.bottom = 'auto';
        }
    });


    document.addEventListener('mouseup', function() {
        isPopupDragging = false;
        popupHeader.style.cursor = 'grab';
    });
    document.addEventListener('touchend', function() {
        isPopupDragging = false;
        popupHeader.style.cursor = 'grab';
    });
</script>
@endpush


@stack('scripts')
<script>
    localStorage.setItem('locale', '{{ app()->getLocale() }}');
</script>
</body>
</html>



