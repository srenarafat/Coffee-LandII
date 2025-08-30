<script>
    const cartContainer = document.getElementById('cart-container');
    let selectedTableNumber = @json(session('table_number'));
    const customizerForm = document.getElementById('customizerForm');
    const customizerAddAction = customizerForm ? customizerForm.action : '';
    const priceEl = document.getElementById('customizerPrice');
    const sizeInput = document.getElementById('sizeValue');
    const sugarInput = document.getElementById('sugarValueInput');
    const iceInput   = document.getElementById('iceValue');

    // open customizer with DEFAULTS (size=medium, sugar=100, ice=normal, note empty)
    function attachCustomizerButtons() {
        document.querySelectorAll('.open-customizer').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function () {
                if (!customizerForm) return;

                // reset to ADD mode
                customizerForm.action = customizerAddAction;
                customizerForm.dataset.mode = 'add';
                const isFood  = this.dataset.isFood === 'true';
                const isWater = this.dataset.isWater === 'true';
                customizerForm.dataset.isFood  = isFood ? 'true' : 'false';
                customizerForm.dataset.isWater = isWater ? 'true' : 'false';

                // payload
                document.getElementById('customizerCartKey').value = '';
                document.getElementById('customizerProductId').value = this.dataset.id;
                document.getElementById('customizerQty').value = 1;
                document.getElementById('customizerImage').src = this.dataset.image;
                document.getElementById('customizerName').textContent = this.dataset.name || '';

                // set price text + capture clean base price (no prefix)
                priceEl.textContent = this.dataset.priceMedium || this.dataset.price || '';
                priceEl.dataset.basePrice = (this.dataset.priceMedium || this.dataset.price || '').replace(/^[A-Z]:\s*/, '');

                // expose per-size prices for the customizer script unless Food
                if (!isFood) {
                    customizerForm.dataset.priceSmall  = this.dataset.priceSmall  || '';
                    customizerForm.dataset.priceMedium = this.dataset.priceMedium || this.dataset.price || '';
                    customizerForm.dataset.priceLarge  = this.dataset.priceLarge  || '';
                } else {
                    customizerForm.dataset.priceSmall = '';
                    customizerForm.dataset.priceMedium = '';
                    customizerForm.dataset.priceLarge = '';
                }

                // defaults for silent options
                const sizeSel  = sizeInput;
                const sugarInp = sugarInput;
                const iceSel   = iceInput;
                const noteInp  = document.getElementById('customizerNote');

                if (sizeSel) sizeSel.value  = 'medium';
                if (sugarInp) sugarInp.value = '100';
                if (iceSel)   iceSel.value   = 'normal';
                if (noteInp)  noteInp.value  = '';

                const drinkOptions = document.querySelector('.drink-options');
                const sugarBlock = document.querySelector('.drink-options [data-group="sugar"]')?.closest('div.mb-2');
                const iceBlock   = document.querySelector('.drink-options [data-group="ice"]')?.closest('div.mb-2');
                const foodOptions = document.querySelector('.food-options');
                const foodChecks  = foodOptions?.querySelectorAll('input[type="checkbox"]');
                foodChecks?.forEach(cb => cb.checked = false);
                if (isFood) {
                    drinkOptions?.classList.add('d-none');
                    sizeSel?.remove();
                    sugarInp?.remove();
                    iceSel?.remove();
                    foodOptions?.classList.remove('d-none');
                } else {
                    drinkOptions?.classList.remove('d-none');
                    foodOptions?.classList.add('d-none');
                    if (sizeSel && !customizerForm.contains(sizeSel)) customizerForm.appendChild(sizeSel);
                    if (isWater) {
                        sugarBlock?.classList.add('d-none');
                        iceBlock?.classList.add('d-none');
                        sugarInp?.remove();
                        iceSel?.remove();
                    } else {
                        sugarBlock?.classList.remove('d-none');
                        iceBlock?.classList.remove('d-none');
                        if (sugarInp && !customizerForm.contains(sugarInp)) customizerForm.appendChild(sugarInp);
                        if (iceSel && !customizerForm.contains(iceSel)) customizerForm.appendChild(iceSel);
                    }
                }

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

                // build FD and strip defaults (except size) so they are not added to the cart line
                const sizeSel  = sizeInput;
                const sugarInp = sugarInput;
                const iceSel   = iceInput;
                const noteInp  = document.getElementById('customizerNote');

                const fd = new FormData(form);
                // always submit the selected size, even if it's the default "medium"
                if (sugarInp && String(sugarInp.value) === '100') fd.delete('sugar_level');
                if (iceSel && iceSel.value === 'normal') fd.delete('ice_option');
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
                const isFood  = btn.dataset.isFood === 'true';
                const isWater = btn.dataset.isWater === 'true';
                customizerForm.dataset.isFood  = isFood ? 'true' : 'false';
                customizerForm.dataset.isWater = isWater ? 'true' : 'false';
                const row = btn.closest('tr');
                const updateUrl = row.querySelector('.update-url')?.value || customizerAddAction;

                // switch to EDIT mode
                customizerForm.action = updateUrl;
                customizerForm.dataset.mode = 'edit';

                // payload
                document.getElementById('customizerCartKey').value = btn.dataset.cartKey;
                document.getElementById('customizerProductId').value = item.product_id;
                document.getElementById('customizerQty').value = item.quantity || 1;
                document.getElementById('customizerImage').src = item.image_url || '';
                document.getElementById('customizerName').textContent = item.name || '';

                // price text + base
                priceEl.textContent = item.price_display || '';
                priceEl.dataset.basePrice = (item.price_display || '').replace(/^[A-Z]:\s*/, '');

                if (!isFood) {
                    customizerForm.dataset.priceSmall  = item.price_small_display  || '';
                    customizerForm.dataset.priceMedium = item.price_medium_display || item.price_display || '';
                    customizerForm.dataset.priceLarge  = item.price_large_display  || '';
                } else {
                    customizerForm.dataset.priceSmall = '';
                    customizerForm.dataset.priceMedium = '';
                    customizerForm.dataset.priceLarge = '';
                }

                const sizeSel  = sizeInput;
                const sugarInp = sugarInput;
                const iceSel   = iceInput;
                const noteInp  = document.getElementById('customizerNote');
                const drinkOptions = document.querySelector('.drink-options');
                const sugarBlock = document.querySelector('.drink-options [data-group="sugar"]')?.closest('div.mb-2');
                const iceBlock   = document.querySelector('.drink-options [data-group="ice"]')?.closest('div.mb-2');
                const foodOptions = document.querySelector('.food-options');
                const foodChecks  = foodOptions?.querySelectorAll('input[type="checkbox"]');
                foodChecks?.forEach(cb => cb.checked = false);

                if (isFood) {
                    drinkOptions?.classList.add('d-none');
                    sizeSel?.remove();
                    sugarInp?.remove();
                    iceSel?.remove();
                    foodOptions?.classList.remove('d-none');
                    (item.options || []).forEach(opt => {
                        foodChecks?.forEach(cb => { if (cb.value === opt) cb.checked = true; });
                    });
                } else {
                    drinkOptions?.classList.remove('d-none');
                    foodOptions?.classList.add('d-none');
                    if (sizeSel && !customizerForm.contains(sizeSel)) customizerForm.appendChild(sizeSel);
                    if (isWater) {
                        sugarBlock?.classList.add('d-none');
                        iceBlock?.classList.add('d-none');
                        sugarInp?.remove();
                        iceSel?.remove();
                    } else {
                        sugarBlock?.classList.remove('d-none');
                        iceBlock?.classList.remove('d-none');
                        if (sugarInp && !customizerForm.contains(sugarInp)) customizerForm.appendChild(sugarInp);
                        if (iceSel && !customizerForm.contains(iceSel)) customizerForm.appendChild(iceSel);
                    }
                }

                const size  = item.size || 'medium';
                const sugar = item.sugar_level != null ? String(item.sugar_level) : '100';
                const ice   = item.ice_option || 'normal';

                if (sizeSel) sizeSel.value = size;
                if (sugarInp) sugarInp.value = sugar;
                if (iceSel)   iceSel.value = ice;
                if (noteInp)  noteInp.value = item.note || '';

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
