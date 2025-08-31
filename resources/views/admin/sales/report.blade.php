@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
        <div class="card-body print-area">
            @include('partials.sales-print', [
                'sales'        => $sales,
                'totalAmount'  => $totalAmount,
                'exportRoute'  => auth()->user()->role === 'superadmin'
                    ? route('superadmin.reports.sales.export', request()->except('page'))
                    : route('admin.reports.sales.export', request()->except('page')),
                'printRoute'   => auth()->user()->role === 'superadmin'
                    ? route('superadmin.reports.sales.print', request()->all())
                    : route('admin.reports.sales.print', request()->all()),
                'filter'       => view('admin.sales.filter', ['users' => $users, 'categories' => $categories])->render(),
            ])
        </div>
    </div>
</div>

<!-- ===== Document-Style Report Modal ===== -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold">📑 Sale Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="doc-header d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="h6 fw-bold mb-1">COFFEE LAND</div>
                        <div class="text-muted small">Sale detail (document view)</div>
                    </div>
                    <div class="text-end small text-muted">
                        <div><span class="fw-semibold">Date:</span> <span id="docDate">—</span></div>
                        <div><span class="fw-semibold">Cashier:</span> <span id="docCashier">—</span></div>
                        <div><span class="fw-semibold">Invoice #:</span> <span id="docInvoiceNo">—</span></div>
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
                            <tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>
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
                                    <div>Table: <span id="docTableName">—</span></div>
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
#reportModal .table th, 
#reportModal .table td { vertical-align: middle; }
#reportModal .card { border-radius: 12px; }
#reportModal .modal-content { border-radius: 14px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const routePrefix = "{{ auth()->user()->role === 'superadmin' ? 'superadmin' : 'admin' }}";

    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            document.getElementById('reportPdfLink').href = `/${routePrefix}/invoice/${saleId}/pdf`;

            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser   = new DOMParser();
                    const doc      = parser.parseFromString(html, 'text/html');
                    const bodyText = doc.body ? doc.body.innerText : '';

                    // ---- helpers ----
                    const grab = (label) => {
                        const rx = new RegExp(label + '\\s*:?\\s*(.+)', 'i');
                        const m  = bodyText.match(rx);
                        return (m && m[1]) ? m[1].trim() : '—';
                    };
                    const grabLines = (label) => bodyText.split('\n')
                        .filter(l => l.toLowerCase().includes(label.toLowerCase()));
                    const parseAmount = (line) => {
                        const m = line.match(/([^0-9\s:])?\s*([0-9.,]+)\s*([^0-9\s])?/);
                        if (!m) return '—';
                        const sym = m[1] || m[3] || '';
                        const amt = m[2];
                        return m[1] ? `${sym}${amt}` : `${amt}${m[3] ? ' ' + sym : ''}`;
                    };
                    const hasLetters = (s) => /[\p{L}]/u.test(s || '');

                    // ---- header fields ----
                    let invoiceNo = '—';
                    const mNo = bodyText.match(/No\:\s*([A-Za-z0-9\-\_]+)/);
                    if (mNo) invoiceNo = mNo[1];
                    document.getElementById('docInvoiceNo').textContent = invoiceNo;
                    document.getElementById('docDate').textContent     = grab('Date');
                    document.getElementById('docCashier').textContent  = grab('Cashier');

                    document.getElementById('docGrand').textContent      = grab('Grand Total');
                    document.getElementById('docSubtotal').textContent   = grab('Subtotal');
                    document.getElementById('docDiscount').textContent   = grab('Discount');
                    document.getElementById('docPayment').textContent    = grab('Payment Method');
                    document.getElementById('docTableName').textContent  = grab('Table');

                    grabLines('Cash Received').forEach(line => {
                        const amount = parseAmount(line);
                        if (/riel/i.test(line) || line.includes('៛')) {
                            document.getElementById('docCashRiel').textContent = amount;
                        } else {
                            document.getElementById('docCashUsd').textContent = amount;
                        }
                    });
                    grabLines('Change').forEach(line => {
                        const amount = parseAmount(line);
                        if (/riel/i.test(line) || line.includes('៛')) {
                            document.getElementById('docChangeRiel').textContent = amount;
                        } else {
                            document.getElementById('docChangeUsd').textContent = amount;
                        }
                    });

                    // ---- build table rows (robust) ----
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = '';

                    const tables = Array.from(doc.querySelectorAll('table'));
                    const norm   = s => (s || '').replace(/\s+/g, ' ').trim().toLowerCase();

                    let invoiceTable = null;
                    // Prefer table with recognizable headers
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
                    // Fallback: pick the widest table
                    if (!invoiceTable) {
                        invoiceTable = tables.sort((a,b) => {
                            const ca = (a.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            const cb = (b.querySelector('tr')?.querySelectorAll('td,th').length) || 0;
                            return cb - ca;
                        })[0];
                    }

                    // Collect body rows
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

                        // SN = first pure integer from the left, else auto
                        let sn = texts.find(x => /^\d+$/.test(x));
                        if (!sn) sn = String(++snAuto);

                        // Item = first cell that contains letters (Khmer/Latin), not pure number or money
                        let itemCell = texts.find(x => hasLetters(x) && !/^[\d\$\s.,៛]+$/.test(x));
                        if (!itemCell) {
                            // fallback: take second cell if it exists
                            itemCell = texts[1] || '—';
                        }

                        // Qty = the integer that is NOT the SN and typically small
                        let qty = texts.find(x => /^\d+$/.test(x) && x !== sn) || '1';

                        // Money-like cells
                        const moneyCells = texts.filter(x => /[\$៛]|^\d+([.,]\d{2,})$/.test(x));
                        const price = moneyCells[0] || '';
                        const total = moneyCells[moneyCells.length - 1] || price;

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${sn}</td>
                            <td>${itemCell}</td>
                            <td class="text-center">${qty}</td>
                            <td class="text-end">${price}</td>
                            <td class="text-end">${total}</td>
                        `;
                        docTbody.appendChild(tr);

                        // Append bullets (options) under this row
                        const bullets = Array.from(r.querySelectorAll('li'))
                            .map(li => li.textContent.trim())
                            .filter(Boolean);
                        if (bullets.length) {
                            const opt = document.createElement('tr');
                            opt.innerHTML = `
                              <td></td>
                              <td colspan="4">
                                <ul class="mb-0">${bullets.map(b => `<li>${b}</li>`).join('')}</ul>
                              </td>
                            `;
                            docTbody.appendChild(opt);
                        }
                    });

                    if (!docTbody.children.length) {
                        docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No items found in invoice.</td></tr>`;
                    }

                    // Notes / Options summary (hide default flags)
                    const hideFlags = new Set(['No Vegetables','No Sweet','No Salty','No Spicy']);
                    const allBullets = Array.from(doc.querySelectorAll('li'))
                        .map(li => li.textContent.trim())
                        .filter(t => t && !hideFlags.has(t));
                    document.getElementById('docOptions').innerHTML = allBullets.length
                        ? `<ul class="mb-0">${allBullets.map(b => `<li>${b}</li>`).join('')}</ul>` : '—';

                    // Subtotal auto-calc if missing
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
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load sale details.</td></tr>`;
                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                });
        });
    });

    // Reset when closed
    const reportModal = document.getElementById('reportModal');
    reportModal.addEventListener('hidden.bs.modal', function () {
        document.querySelector('#docTable tbody').innerHTML =
            '<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>';
        document.getElementById('docOptions').textContent = '—';
        ['docInvoiceNo','docDate','docCashier','docGrand','docSubtotal','docDiscount',
         'docCashUsd','docCashRiel','docChangeUsd','docChangeRiel','docPayment','docTableName']
         .forEach(id => document.getElementById(id).textContent = '—');
    });
});
</script>
@endpush
