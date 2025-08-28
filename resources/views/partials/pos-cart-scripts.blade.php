<script>
    const cartContainer = document.getElementById('cart-container');
    let selectedTableNumber = @json(session('table_number'));
    const customizerForm = document.getElementById('customizerForm');
    const customizerAddAction = customizerForm ? customizerForm.action : '';
    const priceEl = document.getElementById('customizerPrice');

    // open customizer with DEFAULTS (size=medium, sugar=100, ice=normal, note empty)
    function attachCustomizerButtons() {
        document.querySelectorAll('.open-customizer').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                if (!customizerForm) return;
                customizerForm.action = customizerAddAction;
                customizerForm.dataset.mode = 'add';
                document.getElementById('customizerCartKey').value = '';
                document.getElementById('customizerProductId').value = this.dataset.id;
                document.getElementById('customizerQty').value = 1;
                document.getElementById('customizerImage').src = this.dataset.image;
                document.getElementById('customizerName').textContent = this.dataset.name || '';
                priceEl.textContent = this.dataset.priceMedium || '';
                customizerForm.dataset.priceSmall  = this.dataset.priceSmall || '';
                customizerForm.dataset.priceMedium = this.dataset.priceMedium || '';
                customizerForm.dataset.priceLarge  = this.dataset.priceLarge || '';

                // defaults for silent options
                const sizeSel  = document.getElementById('sizeValue');
                const sugarInp = document.getElementById('sugarValueInput');
                const iceSel   = document.getElementById('iceValue');
                const noteInp  = document.getElementById('customizerNote');

                sizeSel.value  = 'medium';
                sugarInp.value = '100';
                iceSel.value   = 'normal';
                noteInp.value  = '';

                bootstrap.Modal.getOrCreateInstance(document.getElementById('customizerModal')).show();
            });
        });
    }

    function attachCustomizerForm() {
        const form = customizerForm;
        if (form && !form.dataset.listener) {
            form.dataset.listener = 'true';
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // build FD and strip defaults so they are not added to the cart line
                const sizeSel  = document.getElementById('sizeValue');
                const sugarInp = document.getElementById('sugarValueInput');
                const iceSel   = document.getElementById('iceValue');
                const noteInp  = document.getElementById('customizerNote');

                const fd = new FormData(form);
                if (sizeSel.value === 'medium') fd.delete('size');
                if (String(sugarInp.value) === '100') fd.delete('sugar_level');
                if (iceSel.value === 'normal') fd.delete('ice_option');
                if (!noteInp.value.trim()) fd.delete('note');
                if (form.dataset.mode === 'edit') {
                    fd.append('action', 'overwrite');
                }

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
                    form.action = customizerAddAction;
                    form.dataset.mode = 'add';
                    document.getElementById('customizerCartKey').value = '';
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

        // Quantity plus/minus controls are handled in
        // `product-customizer.blade.php`. The listeners previously here
        // caused the value to increment twice (e.g., 1 → 3). By removing them
        // we ensure the customizer is the sole source of quantity changes.

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

        // edit cart item options
        document.querySelectorAll('.edit-item-btn').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                if (!customizerForm) return;
                const item = JSON.parse(btn.dataset.item || '{}');
                const row = btn.closest('tr');
                const updateUrl = row.querySelector('.update-url')?.value || customizerAddAction;
                customizerForm.action = updateUrl;
                customizerForm.dataset.mode = 'edit';
                document.getElementById('customizerCartKey').value = btn.dataset.cartKey;
                document.getElementById('customizerProductId').value = item.product_id;
                document.getElementById('customizerQty').value = item.quantity || 1;
                document.getElementById('customizerImage').src = item.image_url || '';
                document.getElementById('customizerName').textContent = item.name || '';
                document.getElementById('customizerPrice').textContent = item.price_display || '';
                priceEl.textContent = item.price_display || '';
                customizerForm.dataset.priceSmall  = item.price_small_display || '';
                customizerForm.dataset.priceMedium = item.price_medium_display || item.price_display || '';
                customizerForm.dataset.priceLarge  = item.price_large_display || '';
                const sizeSel  = document.getElementById('sizeValue');
                const sugarInp = document.getElementById('sugarValueInput');
                const iceSel   = document.getElementById('iceValue');
                const noteInp  = document.getElementById('customizerNote');
                const size  = item.size || 'medium';
                const sugar = item.sugar_level != null ? String(item.sugar_level) : '100';
                const ice   = item.ice_option || 'normal';
                sizeSel.value = size;
                sugarInp.value = sugar;
                iceSel.value = ice;
                noteInp.value = item.note || '';
                ['size','sugar','ice'].forEach(g=>{
                    document.querySelectorAll(`#customizerModal .opt-tile[data-group="${g}"]`).forEach(x=>x.classList.remove('active'));
                });
                document.querySelector(`#customizerModal .opt-tile[data-group="size"][data-value="${size}"]`)?.classList.add('active');
                document.querySelector(`#customizerModal .opt-tile[data-group="sugar"][data-value="${sugar}"]`)?.classList.add('active');
                document.querySelector(`#customizerModal .opt-tile[data-group="ice"][data-value="${ice}"]`)?.classList.add('active');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('customizerModal')).show();
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

    }

    attachCustomizerButtons();
    attachCustomizerForm();
    attachCartFormHandlers();
    if (selectedTableNumber) highlightTableButtons(selectedTableNumber);


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
