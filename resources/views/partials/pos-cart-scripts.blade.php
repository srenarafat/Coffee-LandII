<script>
(function(cfg){
    const container = document.getElementById('product-container');
    const cartContainer = document.getElementById('cart-container');
    let selectedTable = cfg.selectedTable || null;
    const currency = cfg.currency || '$';
    const noteUrl = cfg.noteUrl;
    const removeLabel = cfg.removeLabel;
    const tableUrl = cfg.tableUrl;
    const tableLabel = cfg.tableLabel;
    const liveSearchUrl = cfg.liveSearchUrl;
    const commentStoreUrl = cfg.commentStoreUrl;
    const csrfToken = cfg.csrfToken;
    const emptyLabel = cfg.emptyLabel || '';

    // --- Product Customizer -------------------------------------------------
    function attachCustomizerButtons(){
        document.querySelectorAll('.open-customizer').forEach(btn => {
            if (btn.dataset.listener) return;
            btn.dataset.listener = 'true';
            btn.addEventListener('click', function(){
                document.getElementById('customizerProductId').value = this.dataset.id;
                document.getElementById('customizerQty').value = 1;
                document.getElementById('customizerImage').src = this.dataset.image;
                document.getElementById('customizerName').textContent = this.dataset.name;
                document.getElementById('customizerPrice').textContent = this.dataset.price;
                document.getElementById('customizerSugar').value = 0;
                document.getElementById('sugarValue').textContent = 0;
                document.getElementById('customizerNote').value = '';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('customizerModal')).show();
            });
        });
    }

    function attachCustomizerForm(){
        const form = document.getElementById('customizerForm');
        if (form && !form.dataset.listener){
            form.dataset.listener = 'true';
            form.addEventListener('submit', function(e){
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
        if (qtyInput && !qtyInput.dataset.listener){
            qtyInput.dataset.listener = 'true';
            qtyInput.addEventListener('input', function(){ if(this.value < 1) this.value = 1; });
        }

        const sugarInput = document.getElementById('customizerSugar');
        if (sugarInput && !sugarInput.dataset.listener){
            sugarInput.dataset.listener = 'true';
            sugarInput.addEventListener('input', function(){ document.getElementById('sugarValue').textContent = this.value; });
        }
    }

    function scrollCartToBottom(){
        const cartPanel = cartContainer.querySelector('.cart-panel');
        if (cartPanel) cartPanel.scrollTop = cartPanel.scrollHeight;
    }

    // --- Add to cart forms --------------------------------------------------
    function attachCartFormHandlers(){
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            if (form.dataset.listener) return;
            form.dataset.listener = 'true';
            form.addEventListener('submit', function(e){
                e.preventDefault();
                const fd = new FormData(form);
                if (e.submitter && e.submitter.name) fd.append(e.submitter.name, e.submitter.value);
                fetch(form.action, {
                    method:'POST',
                    headers:{
                        'X-Requested-With':'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body:formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(text || res.statusText); });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.cart){
                        cartContainer.innerHTML = data.cart;
                        scrollCartToBottom();
                    }
                })
                .catch(err => {
                    try{ const msg = JSON.parse(err); showToast(msg.error || 'Error updating cart'); }
                    catch{ showToast(err || 'Error updating cart'); }
                });
            });
        });
    }

        // --- Save comment suggestions -----------------------------------------
    function attachSaveCommentHandler(){
        const saveBtn = document.getElementById('saveComment');
        if (saveBtn && !saveBtn.dataset.listener){
            saveBtn.dataset.listener = 'true';
            saveBtn.addEventListener('click', function(){
                const text = document.getElementById('commentInput').value.trim();
                if (!text) return;
                fetch(commentStoreUrl, {
                    method:'POST',
                    headers:{
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With':'XMLHttpRequest',
                        'Content-Type':'application/json'
                    },
                    body: JSON.stringify({ text })
                })
                .then(res => res.ok ? res.json() : res.text().then(t=>Promise.reject(t)))
                .then(comment => {
                    const list = document.getElementById('commentList');
                    const opt = document.createElement('option');
                    opt.value = comment.text;
                    list.appendChild(opt);
                    const form = document.getElementById('commentForm');
                    if (form){
                        if (typeof form.requestSubmit === 'function') form.requestSubmit();
                        else form.dispatchEvent(new Event('submit', {cancelable:true,bubbles:true}));
                    }
                })
                .catch(err => {
                    try{ const msg = JSON.parse(err); showToast(msg.error || 'Error updating cart'); }
                    catch{ showToast(err || 'Error updating cart'); }
                });
            });
        }
    }
        // --- Live search -------------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    if (searchInput){
        searchInput.addEventListener('keyup', function(){
            const query = this.value;
            const category = document.querySelector('input[name="category"]')?.value || '';
            fetch(`${liveSearchUrl}?query=${encodeURIComponent(query)}&category=${category}`, {
                headers:{'X-Requested-With':'XMLHttpRequest'}
            })
            .then(r => r.text())
            .then(html => {
                document.getElementById('product-grid').innerHTML = html;
                attachCustomizerButtons();
                attachCartFormHandlers();
            });
        });
    }

        // --- Helpers for cart interactions ------------------------------------
    const $ = (sel, ctx=document) => ctx.querySelector(sel);
    const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
    const fmt = n => Number(n||0).toFixed(2);

    function getCsrf(){
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('#commentForm input[name=_token]')?.value
            || '';
    }

    function renderNotes(notes){
        const list = $('#currentNotes');
        if(!list) return;
        list.innerHTML='';
        notes.forEach(n=>{
            const li=document.createElement('li');
            li.className='list-group-item d-flex justify-content-between align-items-center';
            li.textContent=n;
            const btn=document.createElement('button');
            btn.className='btn btn-sm btn-danger remove-note';
            btn.textContent=removeLabel;
            btn.dataset.note=n;
            li.appendChild(btn);
            list.appendChild(li);
        });
    }

        function highlightTableButtons(number){
        $$('.table-btn').forEach(btn=>{
            if(btn.dataset.number == number){
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary','active');
            }else{
                btn.classList.remove('btn-primary','active');
                btn.classList.add('btn-outline-primary');
            }
        });
    }

            function rowOf(el){ return el.closest('tr[data-row-id]'); }
    function unitOf(row){ return Number($('.row-price', row)?.dataset.unit || 0); }
    function qtyEl(row){ return $('.qty', row); }
    function setQty(row, q, confirmed=false){
        const qEl = qtyEl(row);
        qEl.dataset.qty = q;
        qEl.textContent = q;
        if(confirmed) qEl.dataset.confirmed = q;
    }
    function lineTotal(row){
        const line = unitOf(row) * Number(qtyEl(row).dataset.qty || 0);
        $('.row-price', row).textContent = fmt(line);
        return line;
    }
    function recalcTotals(){
        let total = 0, items = 0;
        $$('#cartBody tr[data-row-id]').forEach(r=>{
            const q = Number(qtyEl(r).dataset.qty || 0);
            const line = Number($('.row-price', r).textContent.replace(/,/g,'') || 0);
            items += q; total += line;
        });
        $('#grandTotal').textContent = fmt(total);
        $('#totalItems').textContent = items;
    }
    function checkEmptyCart(){
        if ($$('#cartBody tr[data-row-id]').length === 0){
            const wrapper = $('.cart-wrapper');
            if(!wrapper) return;
            const panel = $('.cart-panel', wrapper);
            if(panel) panel.remove();
            const checkout = $('#checkoutSection', wrapper);
            if(checkout) checkout.remove();
            if(!$('.cart-empty', wrapper)){
                const body = document.createElement('div');
                body.className = 'card-body cart-empty';
                body.innerHTML = `<p class="text-muted text-center">${emptyLabel}</p>`;
                wrapper.appendChild(body);
            }
        }
    }

    // ---- optimistic update core -----------------------------------------
    const pending = new Map(); // cartKey -> {timer, controller}

        function getIds(row){
        const cartKey = row.getAttribute('data-row-id')
            || $('input[name=cart_key]', row)?.value;
        const url = $('.update-url', row)?.value;
        const token = $('input[name=_token]', row)?.value;
        return { cartKey, url, token };
    }

            async function syncQuantity(row, targetQty){
        const { cartKey, url, token } = getIds(row);
        if(!cartKey || !url || !token) return;

        const confirmedQty = Number(qtyEl(row).dataset.confirmed || 0);

        if (pending.get(cartKey)?.controller) {
            pending.get(cartKey).controller.abort();
        }
        const controller = new AbortController();
        pending.set(cartKey, { controller, timer:null });

        const fd = new FormData();
        fd.append('_token', token);
        fd.append('cart_key', cartKey);
        fd.append('action', 'set_quantity');
        fd.append('quantity', String(targetQty));

        let json = null;
        try{
            const res = await fetch(url, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd, signal:controller.signal });
            json = await res.json().catch(()=>null);
            if(!res.ok){
                setQty(row, confirmedQty, true);
                lineTotal(row);
                recalcTotals();
                if(json?.error) showToast(json.error);
                return;
            }
        }catch(e){
            if (controller.signal.aborted) return;
            setQty(row, confirmedQty, true);
            lineTotal(row);
            recalcTotals();
            showToast('Error updating quantity');
            return;
        }

        if (json && json.ok) {
            if (json.item) {
                const q = Number(json.item.quantity ?? targetQty);
                setQty(row, q, true);
                $('.row-price', row).textContent = fmt(json.item.line_total ?? unitOf(row)*q);
            } else {
                lineTotal(row);
            }
            if (json.totals) {
                if (json.totals.grand_total != null) $('#grandTotal').textContent = fmt(json.totals.grand_total);
                if (json.totals.total_items != null) $('#totalItems').textContent = json.totals.total_items;
            } else {
                recalcTotals();
            }
            return;
        }

        let delta = targetQty - confirmedQty;
        let workingQty = confirmedQty;
        while (delta !== 0) {
            const step = delta > 0 ? 'increase' : 'decrease';
            const body2 = new FormData();
            body2.append('_token', token);
            body2.append('cart_key', cartKey);
            body2.append('action', step);
            try{
                const res2 = await fetch(url, { method:'POST', body:body2, signal:controller.signal });
                const j2 = await res2.json().catch(()=>null);
                if(!res2.ok){
                    setQty(row, confirmedQty, true);
                    lineTotal(row);
                    recalcTotals();
                    if(j2?.error) showToast(j2.error);
                    return;
                }
                const newQty = Number(j2?.item?.quantity ?? (step==='increase'? workingQty+1 : Math.max(1, workingQty-1)));
                workingQty = newQty;
                setQty(row, newQty, true);
                if (j2?.item?.line_total != null) $('.row-price', row).textContent = fmt(j2.item.line_total);
                else lineTotal(row);
                if (j2?.totals){
                    if (j2.totals.grand_total != null) $('#grandTotal').textContent = fmt(j2.totals.grand_total);
                    if (j2.totals.total_items != null) $('#totalItems').textContent = j2.totals.total_items;
                } else { recalcTotals(); }
                delta += (step==='increase' ? -1 : 1);
            }catch(e){
                if (controller.signal.aborted) return;
                setQty(row, confirmedQty, true);
                lineTotal(row);
                recalcTotals();
                showToast('Error updating quantity');
                return;
            }
        }
    }
    
    function scheduleSync(row){
        const { cartKey } = getIds(row);
        const state = pending.get(cartKey) || {};
        if (state.timer) clearTimeout(state.timer);
        const target = Number(qtyEl(row).dataset.qty || 0);
        state.timer = setTimeout(()=> syncQuantity(row, target), 200);
        pending.set(cartKey, state);
    }

    // ---- Event handlers ---------------------------------------------------
    document.addEventListener('click', async (e)=>{
        const openTable = e.target.closest('#openTableModal');
        const clearTable = e.target.closest('#clearTable');
        const tableBtn = e.target.closest('.table-btn');
        const noteBtn = e.target.closest('.note-btn');
        const removeNoteBtn = e.target.closest('.remove-note');
        const plus = e.target.closest('.increase-btn');
        const minus = e.target.closest('.decrease-btn');
        const removeForm = e.target.closest('.remove-item-form');

        if(openTable){
            bootstrap.Modal.getOrCreateInstance($('#tableModal')).show();
            highlightTableButtons(selectedTable);
            return;
        }
                    if(clearTable){
            e.preventDefault();
            const fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('clear', '1');
            try{
                const res = await fetch(tableUrl,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});
                const json = await res.json().catch(()=>null);
                if(res.ok){
                    selectedTable = null;
                    const current = $('#currentTable');
                    if(current) current.textContent = '';
                    const btn = $('#clearTable');
                    if(btn) btn.classList.add('d-none');
                    highlightTableButtons(null);
                }
            }catch(err){ showToast('Error clearing table'); }
            return;
        }

            if(tableBtn){
            e.preventDefault();
            const number = tableBtn.dataset.number;
            const fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('table_number', number);
            try{
                const res = await fetch(tableUrl,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});
                const json = await res.json().catch(()=>null);
                if(res.ok){
                    selectedTable = json?.table_number ?? number;
                    const current = $('#currentTable');
                    if(current) current.textContent = `${tableLabel}: ${selectedTable}`;
                    highlightTableButtons(selectedTable);
                    const clearBtn = $('#clearTable');
                    if(clearBtn) clearBtn.classList.remove('d-none');
                    const modal = bootstrap.Modal.getInstance($('#tableModal'));
                    if(modal) modal.hide();
                }
            }catch(err){ showToast('Error setting table'); }
            return;
        }
        
        if (noteBtn){
            const cartKey = noteBtn.dataset.cartKey;
            const notes = JSON.parse(noteBtn.dataset.notes || '[]');
            $('#commentCartKey').value = cartKey;
            $('#commentInput').value = '';
            renderNotes(notes);
            bootstrap.Modal.getOrCreateInstance($('#commentModal')).show();
            return;
        }

    if (removeNoteBtn){
            const cartKey = $('#commentCartKey').value;
            const fd = new FormData();
            fd.append('_token', getCsrf());
            fd.append('cart_key', cartKey);
            fd.append('remove_note', removeNoteBtn.dataset.note || '');
            try{
                const res = await fetch(noteUrl, {
                    method:'POST',
                    headers:{'X-Requested-With':'XMLHttpRequest'},
                    body:fd
                });
                const json = await res.json().catch(()=>null);
                if(res.ok && json?.cart){
                    const modal = bootstrap.Modal.getInstance($('#commentModal'));
                    if(modal) modal.hide();
                    cartContainer.innerHTML = json.cart;
                    const btn = cartContainer.querySelector(`[data-cart-key="${cartKey}"]`);
                    if(btn){
                        const newNotes = JSON.parse(btn.dataset.notes || '[]');
                        btn.dataset.notes = JSON.stringify(newNotes);
                    }
                }
            }catch(err){ showToast('Error updating note'); }
            return;
        }

        if (removeForm){
            e.preventDefault();
            const row = rowOf(removeForm);
            const action = removeForm.getAttribute('action');
            const fd = new FormData(removeForm);
            row.remove();
            recalcTotals();
            checkEmptyCart();
            fetch(action, {
                method:'POST',
                headers:{
                    'X-Requested-With':'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body:fd
            }).catch(()=>{});
            return;
        }

        if (!plus && !minus) return;

        const row = rowOf(plus || minus);
        const qNow = Number(qtyEl(row).dataset.qty || 0);
        const qNew = Math.max(1, qNow + (plus ? 1 : -1));
        setQty(row, qNew);
        lineTotal(row);
        recalcTotals();
        scheduleSync(row);
    });

    document.addEventListener('submit', async (e)=>{
        if(e.target.matches('#commentForm')){
            e.preventDefault();
            const form = e.target;
            const cartKey = $('#commentCartKey').value;
            const fd = new FormData(form);
            try{
                const res = await fetch(noteUrl, {
                    method:'POST',
                    headers:{'X-Requested-With':'XMLHttpRequest'},
                    body:fd
                });
                const json = await res.json().catch(()=>null);
                if(res.ok && json?.cart){
                    const modal = bootstrap.Modal.getInstance($('#commentModal'));
                    if(modal) modal.hide();
                    cartContainer.innerHTML = json.cart;
                    const btn = cartContainer.querySelector(`[data-cart-key="${cartKey}"]`);
                    if(btn){
                        const notes = JSON.parse(btn.dataset.notes || '[]');
                        btn.dataset.notes = JSON.stringify(notes);
                    }
                    form.reset();
                }
            })
            }catch(err){ showToast('Error updating note'); }
        }
    });

    // Initial bindings
    attachCustomizerButtons();
    attachCustomizerForm();
    attachCartFormHandlers();
    attachSaveCommentHandler();
})(@json($config));
</script>