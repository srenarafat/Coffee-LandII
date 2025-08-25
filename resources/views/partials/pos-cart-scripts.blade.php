<script>
    const container = document.getElementById('product-container');
    const cartContainer = document.getElementById('cart-container');
    let selectedTableNumber = @json(session('table_number'));

    // open customizer with DEFAULTS (size=medium, sugar=100, ice=normal, note empty)
    function attachCustomizerButtons() {
        document.querySelectorAll('.open-customizer').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                document.getElementById('customizerProductId').value = this.dataset.id;
                document.getElementById('customizerQty').value = 1;
                document.getElementById('customizerImage').src = this.dataset.image;

                // defaults for silent options
                const sizeSel  = document.getElementById('customizerSize');
                const sugarInp = document.getElementById('customizerSugar');
                const iceSel   = document.getElementById('customizerIce');
                const noteInp  = document.getElementById('customizerNote');

                sizeSel.value  = 'medium';
                sugarInp.value = 100;
                document.getElementById('sugarValue').textContent = 100;
                iceSel.value   = 'normal';
                noteInp.value  = '';

                bootstrap.Modal.getOrCreateInstance(document.getElementById('customizerModal')).show();
            });
        });
    }

    function attachCustomizerForm() {
        const form = document.getElementById('customizerForm');
        if (form && !form.dataset.listener) {
            form.dataset.listener = 'true';
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // build FD and strip defaults so they are not added to the cart line
                const sizeSel  = document.getElementById('customizerSize');
                const sugarInp = document.getElementById('customizerSugar');
                const iceSel   = document.getElementById('customizerIce');
                const noteInp  = document.getElementById('customizerNote');

                const fd = new FormData(form);
                if (sizeSel.value === 'medium') fd.delete('size');
                if (String(sugarInp.value) === '100') fd.delete('sugar_level');
                if (iceSel.value === 'normal') fd.delete('ice_option');
                if (!noteInp.value.trim()) fd.delete('note');

                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: fd
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('customizerModal'));
                    if (modal) modal.hide();
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                    }
                });
            });
        }

        const qtyInput = document.getElementById('customizerQty');
        if (qtyInput && !qtyInput.dataset.listener) {
            qtyInput.dataset.listener = 'true';
            qtyInput.addEventListener('input', function () {
                if (this.value < 1) this.value = 1;
            });
        }

        const qtyMinus = document.getElementById('qtyMinus');
        if (qtyMinus && qtyInput && !qtyMinus.dataset.listener) {
            qtyMinus.dataset.listener = 'true';
            qtyMinus.addEventListener('click', function () {
                const current = parseInt(qtyInput.value) || 1;
                qtyInput.value = Math.max(1, current - 1);
            });
        }

        const qtyPlus = document.getElementById('qtyPlus');
        if (qtyPlus && qtyInput && !qtyPlus.dataset.listener) {
            qtyPlus.dataset.listener = 'true';
            qtyPlus.addEventListener('click', function () {
                const current = parseInt(qtyInput.value) || 1;
                qtyInput.value = Math.max(1, current + 1);
            });
        }

        const sugarInput = document.getElementById('customizerSugar');
        if (sugarInput && !sugarInput.dataset.listener) {
            sugarInput.dataset.listener = 'true';
            sugarInput.addEventListener('input', function () {
                document.getElementById('sugarValue').textContent = this.value;
            });
        }
    }

    function scrollCartToBottom() {
        const cartPanel = cartContainer.querySelector('.cart-panel');
        if (cartPanel) cartPanel.scrollTop = cartPanel.scrollHeight;
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
                const cartKey = document.getElementById('commentCartKey').value;
                const note = btn.dataset.note;
                const formData = new FormData();
                formData.append('cart_key', cartKey);
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
                        const notes = JSON.parse(document.querySelector(`[data-cart-key="${cartKey}"]`).dataset.notes || '[]');
                        renderNotes(notes);
                    }
                });
            });
        });
    }

    function attachCartFormHandlers() {
        // add/increase/decrease in cart
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.ok ? res.json() : res.text().then(t => Promise.reject(new Error(t || res.statusText))))
                .then(data => {
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                        const qtyInput = form.querySelector('input[name="quantity"]');
                        if (qtyInput) qtyInput.value = 1;
                    }
                })
                .catch(error => {
                    try { const msg = JSON.parse(error.message); showToast(msg.error || 'Error updating cart'); }
                    catch { showToast(error.message || 'Error updating cart'); }
                });
            });
        });

        // remove line
        document.querySelectorAll('.remove-item-form').forEach(form => {
            if (form.dataset.listener) return;
            form.dataset.listener = 'true';
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: new FormData(form)
                })
                .then(res => res.ok ? res.json() : res.text().then(t => Promise.reject(new Error(t || res.statusText))))
                .then(data => {
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                    }
                })
                .catch(error => {
                    try { const msg = JSON.parse(error.message); showToast(msg.error || 'Error updating cart'); }
                    catch { showToast(error.message || 'Error updating cart'); }
                });
            });
        });

        // open notes modal
        document.querySelectorAll('.note-btn').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                const cartKey = btn.dataset.cartKey;
                const notes = JSON.parse(btn.dataset.notes || '[]');
                document.getElementById('commentCartKey').value = cartKey;
                document.getElementById('commentInput').value = '';
                renderNotes(notes);
                const modal = new bootstrap.Modal(document.getElementById('commentModal'));
                modal.show();
            });
        });

        // table modal opener
        const openTable = document.getElementById('openTableModal');
        if (openTable && !openTable.dataset.listener) {
            openTable.dataset.listener = 'true';
            openTable.addEventListener('click', function () {
                const modal = new bootstrap.Modal(document.getElementById('tableModal'));
                modal.show();
                highlightTableButtons(selectedTableNumber);
            });
        }

        // table buttons
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.ok ? res.json() : res.text().then(t => Promise.reject(new Error(t || res.statusText))))
                .then(data => {
                    selectedTableNumber = data.table_number;
                    const currentEl = document.getElementById('currentTable');
                    if (currentEl) currentEl.textContent = '{{ __('messages.table') }}: ' + data.table_number;
                    highlightTableButtons(selectedTableNumber);
                    const modalEl = document.getElementById('tableModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                })
                .catch(error => {
                    try { const msg = JSON.parse(error.message); showToast(msg.error || 'Error updating cart'); }
                    catch { showToast(error.message || 'Error updating cart'); }
                });
            });
        });

        // comment form submit
        const form = document.getElementById('commentForm');
        if (form && !form.dataset.listener) {
            form.dataset.listener = 'true';
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.ok ? res.json() : res.text().then(t => Promise.reject(new Error(t || res.statusText))))
                .then(data => {
                    const modalEl = document.getElementById('commentModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    if (data.cart) {
                        cartContainer.innerHTML = data.cart;
                        attachCartFormHandlers();
                        scrollCartToBottom();
                        const cartKey = document.getElementById('commentCartKey').value;
                        const btn = document.querySelector(`[data-cart-key="${cartKey}"]`);
                        const notes = btn ? JSON.parse(btn.dataset.notes || '[]') : [];
                        renderNotes(notes);
                    }
                })
                .catch(error => {
                    try { const msg = JSON.parse(error.message); showToast(msg.error || 'Error updating cart'); }
                    catch { showToast(error.message || 'Error updating cart'); }
                });
            });
        }

        attachSaveCommentHandler();
    }

    attachCustomizerButtons();
    attachCustomizerForm();
    attachCartFormHandlers();
    if (selectedTableNumber) highlightTableButtons(selectedTableNumber);

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
                .then(res => res.ok ? res.json() : res.text().then(t => Promise.reject(new Error(t || res.statusText))))
                .then(comment => {
                    const list = document.getElementById('commentList');
                    const opt = document.createElement('option');
                    opt.value = comment.text;
                    list.appendChild(opt);

                    const form = document.getElementById('commentForm');
                    if (form) {
                        if (typeof form.requestSubmit === 'function') form.requestSubmit();
                        else form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                })
                .catch(error => {
                    try { const msg = JSON.parse(error.message); showToast(msg.error || 'Error updating cart'); }
                    catch { showToast(error.message || 'Error updating cart'); }
                });
            });
        }
    }

    // live search keeps handlers
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const query = this.value;
        const category = document.querySelector('input[name="category"]')?.value || '';
        fetch(`{{ route($routePrefix . '.pos.liveSearch') }}?query=${encodeURIComponent(query)}&category=${category}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('product-grid').innerHTML = html;
            attachCustomizerButtons();
            attachCartFormHandlers();
        });
    });
</script>
