<script>
    const container = document.getElementById('product-container');
    const cartContainer = document.getElementById('cart-container');
    let selectedTableNumber = @json(session('table_number'));

    function scrollCartToBottom() {
        const cartPanel = cartContainer.querySelector('.cart-panel');
        if (cartPanel) {
            cartPanel.scrollTop = cartPanel.scrollHeight;
        }
    }

    function highlightTableButtons(number) {
        document.querySelectorAll('.table-btn').forEach(btn => {
            if (btn.dataset.number == number) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'active');
            } else {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-primary');
            }
        });
    }

    function renderNotes(notes) {
        const list = document.getElementById('currentNotes');
        if (!list) return;
        list.innerHTML = '';
        notes.forEach(n => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.textContent = n;
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-danger remove-note';
            btn.textContent = '{{ __('messages.remove_command') }}';
            btn.dataset.note = n;
            li.appendChild(btn);
            list.appendChild(li);
        });
        attachNoteHandlers();
    }

    function attachNoteHandlers() {
        document.querySelectorAll('#currentNotes .remove-note').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                const productId = document.getElementById('commentProductId').value;
                const note = btn.dataset.note;
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('remove_note', note);
                fetch('{{ route($routePrefix . '.pos.note') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => {
                    if (data.cart) {
                        const modalEl = document.getElementById('commentModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                        const notes = JSON.parse(document.querySelector(`[data-product-id="${productId}"]`).dataset.notes || '[]');
                        renderNotes(notes);
                    }
                });
            });
        });
    }


    function attachCartFormHandlers() {
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            if (form.dataset.listener) return;
            form.dataset.listener = 'true';
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                if (e.submitter && e.submitter.name) {
                    formData.append(e.submitter.name, e.submitter.value);
                }
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                        const qtyInput = form.querySelector('input[name="quantity"]');
                        if (qtyInput) qtyInput.value = 1;
                    } else {
                        console.error("Cart data missing in response", data);
                    }
                })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        });

        document.querySelectorAll('.update-quantity-form').forEach(form => {
            if (form.dataset.listener) return;
            form.dataset.listener = 'true';

            const increaseBtn = form.querySelector('.increase-btn');
            const decreaseBtn = form.querySelector('.decrease-btn');
            const actionInput = form.querySelector('input[name="action"]');

            if (increaseBtn) {
                increaseBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (actionInput) actionInput.value = 'increase';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                });
            }

            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (actionInput) actionInput.value = 'decrease';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                });
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                const actionUrl = form.querySelector('.update-url')?.value;
fetch(actionUrl, {

                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                    } else {
                        console.error("Cart data missing in response", data);
                    }
                })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        });

        document.querySelectorAll('.remove-item').forEach(link => {
            if (link.dataset.listener) return;
            link.dataset.listener = 'true';
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetch(link.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                        if (data.cart) {
                            cartContainer.innerHTML = data.cart;
                            attachCartFormHandlers();
                            
                        } else {
                            console.error("Cart data missing in response", data);
                        }
                    })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        });

        document.querySelectorAll('.note-btn').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                const productId = btn.dataset.productId;
                const notes = JSON.parse(btn.dataset.notes || '[]');
                document.getElementById('commentProductId').value = productId;
                document.getElementById('commentInput').value = '';
                renderNotes(notes);
                const modal = new bootstrap.Modal(document.getElementById('commentModal'));
                modal.show();
            });
        });
        const openTable = document.getElementById('openTableModal');
        if (openTable && !openTable.dataset.listener) {
            openTable.dataset.listener = 'true';
            openTable.addEventListener('click', function () {
                const modal = new bootstrap.Modal(document.getElementById('tableModal'));
                modal.show();
                highlightTableButtons(selectedTableNumber);
            });
        }

        document.querySelectorAll('.table-btn').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                const number = btn.dataset.number;
                const formData = new FormData();
                formData.append('table_number', number);
                fetch('{{ route($routePrefix . '.pos.table') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                    selectedTableNumber = data.table_number;
                    const currentEl = document.getElementById('currentTable');
                    if (currentEl) {
                        currentEl.textContent = '{{ __('messages.table') }}: ' + data.table_number;
                    }
                    highlightTableButtons(selectedTableNumber);
                    const modalEl = document.getElementById('tableModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        });


        const form = document.getElementById('commentForm');
        if (form && !form.dataset.listener) {
            form.dataset.listener = 'true';
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                    const modalEl = document.getElementById('commentModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                        const productId = document.getElementById('commentProductId').value;
                        const btn = document.querySelector(`[data-product-id="${productId}"]`);
                        const notes = btn ? JSON.parse(btn.dataset.notes || '[]') : [];
                        renderNotes(notes);
                    }
            
                })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        }
        
        attachSaveCommentHandler();
    }
    attachCartFormHandlers();
    if (selectedTableNumber) {
        highlightTableButtons(selectedTableNumber);
    }

    function attachSaveCommentHandler() {
    
        const saveBtn = document.getElementById('saveComment');
        if (saveBtn && !saveBtn.dataset.listener) {
            saveBtn.dataset.listener = 'true';
            saveBtn.addEventListener('click', function () {
                const text = document.getElementById('commentInput').value.trim();
                if (!text) return;
                fetch('{{ route('comments.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ text })
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(comment => {
                    const list = document.getElementById('commentList');
                    const opt = document.createElement('option');
                    opt.value = comment.text;
                    list.appendChild(opt);


                    const form = document.getElementById('commentForm');
                    if (form) {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                        }
                    }
                })
                .catch(error => {
    try {
        const msg = JSON.parse(error.message);
        showToast(msg.error || 'Error updating cart');
    } catch {
        showToast(error.message || 'Error updating cart');
    }
});

            });
        }
        }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        const query = this.value;
        const category = document.querySelector('input[name="category"]')?.value || '';

        fetch(`{{ route($routePrefix . '.pos.liveSearch') }}?query=${encodeURIComponent(query)}&category=${category}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('product-grid').innerHTML = html;
                attachCartFormHandlers();
            });
    });

</script>