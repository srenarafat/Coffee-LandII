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
    const routePrefix = 'cashier';

    // Use the existing "View" buttons from partials.sales-print
    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            document.getElementById('reportPdfLink').href = `/${routePrefix}/invoice/${saleId}/pdf`;

            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const bodyText = doc.body ? doc.body.innerText : '';

                    // Extract "Label: value"
                    const grab = (label) => {
                        const rx = new RegExp(label + '\\s*:?\\s*(.+)');
                        const m = bodyText.match(rx);
                        return (m && m[1]) ? m[1].trim() : '—';
                    };

                    // Header
                    let invoiceNo = '—';
                    const mNo = bodyText.match(/No\:\s*([A-Za-z0-9\-\_]+)/);
                    if (mNo) invoiceNo = mNo[1];
                    document.getElementById('docInvoiceNo').textContent = invoiceNo;
                    document.getElementById('docDate').textContent     = grab('Date');
                    document.getElementById('docCashier').textContent  = grab('Cashier');

                    // Summary
                    document.getElementById('docGrand').textContent      = grab('Grand Total');
                    document.getElementById('docSubtotal').textContent   = grab('Subtotal');
                    document.getElementById('docDiscount').textContent   = grab('Discount');
                    document.getElementById('docCashUsd').textContent    = grab('Cash Received \\(USD\\)');
                    document.getElementById('docCashRiel').textContent   = grab('Cash Received \\(Riel\\)');
                    document.getElementById('docChangeUsd').textContent  = grab('Change \\(USD\\)');
                    document.getElementById('docChangeRiel').textContent = grab('Change \\(Riel\\)');
                    document.getElementById('docPayment').textContent    = grab('Payment Method');
                    document.getElementById('docTableName').textContent  = grab('Table');

                    // ===== Build table from invoice content (robust) =====
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = '';

                    const tables = Array.from(doc.querySelectorAll('table'));
                    const norm = s => (s || '').replace(/\s+/g,' ').trim().toLowerCase();

                    let invoiceTable = null;
                    for (const t of tables) {
                        let headers = Array.from(t.querySelectorAll('thead th'));
                        if (!headers.length) {
                            const firstRow = t.querySelector('tr');
                            if (firstRow) headers = Array.from(firstRow.querySelectorAll('th,td'));
                        }
                        const headerText = headers.map(h => norm(h.textContent));
                        const hasItem = headerText.some(h => /(item|ឥវ៉ាន់|មុខទំនិញ)/.test(h));
                        const hasQty  = headerText.some(h => /(qty|quantity|បរិមាណ)/.test(h));
                        const hasPrice= headerText.some(h => /(price|unit|តម្លៃ)/.test(h));
                        const hasTot  = headerText.some(h => /(total|សរុប)/.test(h));
                        if (hasItem && hasQty && hasPrice && hasTot) { invoiceTable = t; break; }
                    }
                    if (!invoiceTable) invoiceTable = tables[0];

                    const headerCells = (() => {
                        let hs = Array.from(invoiceTable.querySelectorAll('thead th'));
                        if (!hs.length) {
                            const firstRow = invoiceTable.querySelector('tr');
                            if (firstRow) hs = Array.from(firstRow.querySelectorAll('th,td'));
                        }
                        return hs;
                    })();

                    const findIndex = (reArray, def=-1) => {
                        const idx = headerCells.findIndex(h => reArray.some(re => re.test(norm(h.textContent))));
                        return idx >= 0 ? idx : def;
                    };

                    const idxSN    = findIndex([/(sn|s\/n|ល\.រ)/i], 0);
                    const idxItem  = findIndex([/(item|ឥវ៉ាន់|មុខទំនិញ)/i], 1);
                    const idxQty   = findIndex([/(qty|quantity|បរិមាណ)/i], 2);
                    const idxPrice = findIndex([/(price|unit|តម្លៃ)/i], 3);
                    const idxTotal = findIndex([/(total|សរុប)/i], 4);

                    let rows = Array.from(invoiceTable.querySelectorAll('tbody tr'));
                    if (!rows.length) {
                        const all = Array.from(invoiceTable.querySelectorAll('tr'));
                        rows = all.slice(1);
                    }

                    let snAuto = 0;
                    rows.forEach(r => {
                        const cells = Array.from(r.querySelectorAll('td,th')).map(td => td.innerText.trim());
                        if (!cells.length) return;

                        const sn    = (cells[idxSN]    ?? (++snAuto)).toString();
                        const item  = (cells[idxItem]  ?? '').toString() || '—';
                        const qty   = (cells[idxQty]   ?? '').toString() || '1';
                        const price = (cells[idxPrice] ?? '').toString();
                        const total = (cells[idxTotal] ?? price).toString();

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${sn}</td>
                            <td>${item}</td>
                            <td class="text-center">${qty}</td>
                            <td class="text-end">${price}</td>
                            <td class="text-end">${total}</td>
                        `;
                        docTbody.appendChild(tr);

                        const bullets = Array.from(r.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
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

                    const allBullets = Array.from(doc.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
                    document.getElementById('docOptions').innerHTML = allBullets.length
                      ? `<ul class="mb-0">${allBullets.map(b => `<li>${b}</li>`).join('')}</ul>`
                      : '—';

                    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                    modal.show();
                })
                .catch(() => {
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load sale details.</td></tr>`;
                    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                    modal.show();
                });
        });
    });

    // Clear on close
    const reportModal = document.getElementById('reportModal');
    reportModal.addEventListener('hidden.bs.modal', function () {
        document.querySelector('#docTable tbody').innerHTML =
            '<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>';
        document.getElementById('docOptions').textContent = '—';
        [
          'docInvoiceNo','docDate','docCashier','docGrand','docSubtotal','docDiscount',
          'docCashUsd','docCashRiel','docChangeUsd','docChangeRiel','docPayment','docTableName'
        ].forEach(id => document.getElementById(id).textContent = '—');
    });
});
</script>
@endpush
