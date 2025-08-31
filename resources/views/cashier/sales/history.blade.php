@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
        <div class="card-body print-area">
            @include('partials.sales-print', [
                'sales'        => $sales,
                'totalAmount'  => $totalAmount,
                'exportRoute'  => route('cashier.sales.history', array_merge(request()->except('page'), ['export' => 'csv'])),
                'printRoute'   => route('cashier.sales.history', array_merge(request()->all(), ['print' => 1])),
                'filter'       => view('cashier.sales.filter', compact('categories'))->render(),
            ])
        </div>
    </div>
</div>

<!-- ===== Document-Style Report Modal (Cashier) ===== -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold">📑 Sale Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- compact header; table appears immediately below -->
                <div class="doc-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
                    <div>
                        <div class="h6 fw-bold mb-1">COFFEE LAND</div>
                        <div class="text-muted small">Sale detail (document view)</div>
                    </div>
                    <div class="text-end small text-muted">
                        <div><span class="fw-semibold">Date:</span> <span id="docDate">—</span></div>
                        <div><span class="fw-semibold">Cashier:</span> <span id="docCashier">—</span></div>
                        <div><span class="fw-semibold">Invoice #:</span> <span id="docInvoiceNo">—</span></div>
                        <div>Table: <span id="docTableName">—</span></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" id="docTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:70px">SN</th>
                                <th>Item</th>
                                <th style="width:90px">Qty</th>
                                <th style="width:120px">Price</th>
                                <th style="width:140px">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3 g-3">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="fw-semibold mb-2">Notes / Options</div>
                                <div id="docOptions" class="small text-muted">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Subtotal</span><span id="docSubtotal">—</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Discount</span><span id="docDiscount">—</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fs-6 fw-bold">
                                    <span>Grand Total</span><span id="docGrand">—</span>
                                </div>
                                <hr class="my-2">
                                <div class="small text-muted">
                                    <div>Cash (USD): <span id="docCashUsd">—</span></div>
                                    <div>Cash (Riel): <span id="docCashRiel">—</span></div>
                                    <div>Change (USD): <span id="docChangeUsd">—</span></div>
                                    <div>Change (Riel): <span id="docChangeRiel">—</span></div>
                                    <div>Payment Method: <span id="docPayment">—</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>

            <div class="modal-footer bg-light">
                <a href="#" id="reportPdfLink" class="btn btn-primary" target="_blank">⬇️ Export PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* header spacing */
#reportModal .doc-header { margin-bottom: .5rem !important; }

/* table: tidy borders + sticky header */
#reportModal .table { border-color: #e5e7eb; }
#reportModal .table th, #reportModal .table td { vertical-align: middle; border-color: #e5e7eb; }
#reportModal .table thead th { position: sticky; top: 0; background-color: #f8f9fa; }
#reportModal .table tbody tr + tr td { border-top: 4px solid #f8f9fa; }

/* alignment: Item left; SN/Qty/Price/Total centered */
#reportModal #docTable th:nth-child(2), 
#reportModal #docTable td:nth-child(2) { text-align: left; word-break: break-word; white-space: normal; }
#reportModal #docTable th:nth-child(1),
#reportModal #docTable td:nth-child(1),
#reportModal #docTable th:nth-child(3),
#reportModal #docTable td:nth-child(3),
#reportModal #docTable th:nth-child(4),
#reportModal #docTable td:nth-child(4),
#reportModal #docTable th:nth-child(5),
#reportModal #docTable td:nth-child(5) { text-align: center; }

/* chips under item stay left with the item */
#reportModal .chipline { display: flex; gap: .375rem; justify-content: flex-start; flex-wrap: wrap; }

#reportModal .card { border-radius: 12px; }
#reportModal .modal-content { border-radius: 14px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const routePrefix = 'cashier';
    const loadingRow = '<tr><td colspan="5" class="text-center text-muted"><div class="spinner-border spinner-border-sm text-secondary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    const hideFlags = new Set(['No Vegetables','No Sweet','No Salty','No Spicy']);

    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            const docTbody = document.querySelector('#docTable tbody');
            docTbody.innerHTML = loadingRow;

            document.getElementById('reportPdfLink').href = `/${routePrefix}/invoice/${saleId}/pdf`;

            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser   = new DOMParser();
                    const doc      = parser.parseFromString(html, 'text/html');
                    const bodyText = doc.body ? doc.body.innerText : '';

                    // helpers
                    const grab = (label) => {
                        const rx = new RegExp(label + '\\s*:?\\s*(.+)', 'i');
                        const m  = bodyText.match(rx);
                        return (m && m[1]) ? m[1].trim() : '—';
                    };
                    const parseAmountLoose = (line) => {
                        const num = (line.match(/[0-9][0-9.,]*/) || [''])[0].replace(/,/g,'');
                        const sym = (line.match(/[\$៛]/) || [''])[0];
                        return num ? `${sym || ''}${Number(num).toFixed(2)}` : '—';
                    };
                    const hasLetters = (s) => /[\p{L}]/u.test(s || '');

                    // header fields
                    const mNo = bodyText.match(/No\:\s*([A-Za-z0-9\-\_]+)/);
                    document.getElementById('docInvoiceNo').textContent = mNo ? mNo[1] : '—';
                    document.getElementById('docDate').textContent     = grab('Date');
                    document.getElementById('docCashier').textContent  = grab('Cashier');
                    document.getElementById('docTableName').textContent= grab('Table');

                    // amounts
                    document.getElementById('docGrand').textContent     = grab('Grand Total');
                    document.getElementById('docSubtotal').textContent  = grab('Subtotal');
                    const disc = grab('Discount');
                    document.getElementById('docDiscount').textContent  = disc === '—' ? '$0.00' : disc;
                    document.getElementById('docPayment').textContent   = grab('Payment Method');

                    // robust cash/change parsing
                    const cashUsdMatch    = bodyText.match(/Cash\s*Received\s*\((?:USD|\$)\)\s*:\s*([^\n]+)/i);
                    const cashRielMatch   = bodyText.match(/Cash\s*Received\s*\((?:Riel|៛)\)\s*:\s*([^\n]+)/i);
                    const changeUsdMatch  = bodyText.match(/Change\s*\((?:USD|\$)\)\s*:\s*([^\n]+)/i);
                    const changeRielMatch = bodyText.match(/Change\s*\((?:Riel|៛)\)\s*:\s*([^\n]+)/i);

                    document.getElementById('docCashUsd').textContent   = cashUsdMatch    ? parseAmountLoose(cashUsdMatch[0])   : '$0.00';
                    document.getElementById('docCashRiel').textContent  = cashRielMatch   ? parseAmountLoose(cashRielMatch[0])  : '៛0.00';
                    document.getElementById('docChangeUsd').textContent = changeUsdMatch  ? parseAmountLoose(changeUsdMatch[0]) : '$0.00';
                    document.getElementById('docChangeRiel').textContent= changeRielMatch ? parseAmountLoose(changeRielMatch[0]): '៛0.00';

                    // ===== Build table (robust) =====
                    docTbody.innerHTML = '';

                    const tables = Array.from(doc.querySelectorAll('table'));
                    const norm   = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();

                    let invoiceTable = null;
                    for (const t of tables) {
                        let hs = Array.from(t.querySelectorAll('thead th'));
                        if (!hs.length) {
                            const fr = t.querySelector('tr');
                            if (fr) hs = Array.from(fr.querySelectorAll('th,td'));
                        }
                        const headerText = hs.map(h => norm(h.textContent));
                        const hasItem  = headerText.some(h => /(item|ឥវ៉ាន់|មុខទំនិញ)/.test(h));
                        const hasQty   = headerText.some(h => /(qty|quantity|បរិមាណ)/.test(h));
                        const hasPrice = headerText.some(h => /(price|unit|តម្លៃ)/.test(h));
                        const hasTot   = headerText.some(h => /(total|សរុប)/.test(h));
                        if (hasItem && hasQty && hasPrice && hasTot) { invoiceTable = t; break; }
                    }
                    if (!invoiceTable) {
                        invoiceTable = tables.sort((a,b) => {
                            const ca = (a.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            const cb = (b.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            return cb - ca;
                        })[0];
                    }

                    let rows = Array.from(invoiceTable?.querySelectorAll('tbody tr') || []);
                    if (!rows.length) {
                        const all = Array.from(invoiceTable?.querySelectorAll('tr') || []);
                        rows = all.slice(1);
                    }

                    let snAuto = 0;
                    rows.forEach(r => {
                        const tds = Array.from(r.querySelectorAll('td,th'));
                        if (!tds.length) return;

                        const texts = tds.map(td => td.innerText.trim());

                        // SN
                        let sn = texts.find(x => /^\d+$/.test(x));
                        if (!sn) sn = String(++snAuto);

                        // Item name (strip inline modifiers)
                        let itemTdEl = tds.find(td => hasLetters(td.innerText) && !/^[\d\$\s.,៛]+$/.test(td.innerText)) || tds[1];
                        let itemName = '—';
                        if (itemTdEl) {
                            const clone = itemTdEl.cloneNode(true);
                            clone.querySelectorAll('ul,ol,li,small,span.badge').forEach(n => n.remove());
                            itemName = (clone.innerText || '').replace(/\s+/g,' ').trim()
                                .replace(/\b(Small|Medium|Large)\s*Size\b/ig,'')
                                .replace(/\bSugar\s*:?\s*\d+%/ig,'')
                                .replace(/\bLess\s*Ice\b/ig,'')
                                .replace(/\bNo\s+(Salty|Spicy|Sweet|Vegetables)\b/ig,'')
                                .replace(/\s{2,}/g,' ')
                                .trim();
                        }

                        // Qty
                        let qty = texts.find(x => /^\d+$/.test(x) && x !== sn) || '1';

                        // Price & Total
                        const moneyCells = texts.filter(x => /[\$៛]|^\d+([.,]\d{2,})$/.test(x));
                        const price = moneyCells[0] || '';
                        const total = moneyCells[moneyCells.length - 1] || price;

                        // Row modifiers → chips (left with item)
                        const bullets = Array.from(r.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
                        let modifiersHtml = '';
                        if (bullets.length) {
                            let size = '';
                            const sugarIce = [];
                            const others = [];
                            bullets.forEach(b => {
                                const lower = b.toLowerCase();
                                if (/small/.test(lower)) size = 'S';
                                else if (/medium/.test(lower)) size = 'M';
                                else if (/large/.test(lower)) size = 'L';
                                else if (/sugar|ice/.test(lower)) sugarIce.push(b);
                                else others.push(b);
                            });
                            const chips = [];
                            if (size) chips.push(`<span class="badge rounded-pill bg-body-secondary text-muted border lh-sm">${size}</span>`);
                            if (sugarIce.length) chips.push(`<span class="small text-muted lh-sm">${sugarIce.join(' • ')}</span>`);
                            if (others.length) chips.push(others.map(o => `<span class="badge rounded-pill bg-body-secondary text-muted border lh-sm">${o}</span>`).join(''));
                            modifiersHtml = chips.length ? `<div class="chipline mt-1">${chips.join('')}</div>` : '';
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${sn}</td>
                            <td>${itemName}${modifiersHtml}</td>
                            <td>${qty}</td>
                            <td>${price}</td>
                            <td>${total}</td>
                        `;
                        docTbody.appendChild(tr);
                    });

                    if (!docTbody.children.length) {
                        docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No items found in invoice.</td></tr>`;
                    }

                    // Order-level notes (exclude default flags)
                    const allBullets = Array.from(doc.querySelectorAll('li'))
                        .filter(li => !invoiceTable?.contains(li))
                        .map(li => li.textContent.trim())
                        .filter(t => t && !hideFlags.has(t));
                    document.getElementById('docOptions').innerHTML = allBullets.length
                        ? `<ul class="mb-0">${allBullets.map(b => `<li>${b}</li>`).join('')}</ul>` : '—';

                    // Subtotal if missing
                    const subtotalEl = document.getElementById('docSubtotal');
                    if (subtotalEl.textContent === '—') {
                        let subtotal = 0;
                        document.querySelectorAll('#docTable tbody tr').forEach(tr => {
                            const t = tr.children[4];
                            if (t) subtotal += parseFloat((t.textContent || '').replace(/[^0-9.\-]/g,'')) || 0;
                        });
                        const sym = (document.getElementById('docGrand').textContent.match(/[^0-9.,\-\s]/) || [''])[0];
                        subtotalEl.textContent = `${sym || ''}${subtotal.toFixed(2)}`;
                    }

                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                })
                .catch(() => {
                    docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load sale details.</td></tr>`;
                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                });
        });
    });

    // reset on close
    const reportModal = document.getElementById('reportModal');
    reportModal.addEventListener('hidden.bs.modal', function () {
        document.querySelector('#docTable tbody').innerHTML = loadingRow;
        document.getElementById('docOptions').textContent = '—';
        ['docInvoiceNo','docDate','docCashier','docGrand','docSubtotal','docDiscount',
         'docCashUsd','docCashRiel','docChangeUsd','docChangeRiel','docPayment','docTableName']
         .forEach(id => document.getElementById(id).textContent = '—');
    });
});
</script>
@endpush
