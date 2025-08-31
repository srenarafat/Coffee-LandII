@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
        <div class="card-body print-area">
            @include('partials.sales-print', [
                'sales' => $sales,
                'totalAmount' => $totalAmount,
                'exportRoute' => auth()->user()->role === 'superadmin'
                    ? route('superadmin.reports.sales.export', request()->except('page'))
                    : route('admin.reports.sales.export', request()->except('page')),
                'printRoute' => auth()->user()->role === 'superadmin'
                    ? route('superadmin.reports.sales.print', request()->all())
                    : route('admin.reports.sales.print', request()->all()),
                'filter' => view('admin.sales.filter', ['users' => $users, 'categories' => $categories])->render(),
            ])
        </div>
    </div>
</div>

<!-- ===== Document-Style Report Modal (replaces invoice look) ===== -->
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
/* Subtle, clean document look */
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

    // Reuse existing buttons (they already have .view-invoice and data-sale-id)
    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            const pdfHref = `/${routePrefix}/invoice/${saleId}/pdf`;
            document.getElementById('reportPdfLink').href = pdfHref;

            // Fetch the existing invoice HTML (server code unchanged)
            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    // Parse the invoice HTML we already have and map it to a doc-style view
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const bodyText = doc.body ? doc.body.innerText : '';

                    // ===== Try to extract basics from text (robust to markup changes) =====
                    const grab = (label) => {
                        const rx = new RegExp(label + '\\s*:?\\s*(.+)');
                        const m = bodyText.match(rx);
                        return (m && m[1]) ? m[1].trim() : '—';
                    };

                    // Invoice No (your invoice view usually shows "No: 827")
                    let invoiceNo = '—';
                    const mNo = bodyText.match(/No\:\s*([A-Za-z0-9\-\_]+)/);
                    if (mNo) invoiceNo = mNo[1];

                    // Fill header fields
                    document.getElementById('docInvoiceNo').textContent = invoiceNo;
                    document.getElementById('docDate').textContent = grab('Date');
                    document.getElementById('docCashier').textContent = grab('Cashier');

                    // Payment/summary fields (will gracefully show "—" if not found)
                    document.getElementById('docGrand').textContent = grab('Grand Total');
                    document.getElementById('docSubtotal').textContent = grab('Subtotal');
                    document.getElementById('docDiscount').textContent = grab('Discount');
                    document.getElementById('docCashUsd').textContent = grab('Cash Received \\(USD\\)');
                    document.getElementById('docCashRiel').textContent = grab('Cash Received \\(Riel\\)');
                    document.getElementById('docChangeUsd').textContent = grab('Change \\(USD\\)');
                    document.getElementById('docChangeRiel').textContent = grab('Change \\(Riel\\)');
                    document.getElementById('docPayment').textContent = grab('Payment Method');
                    document.getElementById('docTableName').textContent = grab('Table');

                    // ===== Build table rows from invoice content =====
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = ''; // clear

                    // Try to locate rows in the source invoice (common patterns)
                    // 1) If the invoice has a table, use it
                    let sourceRows = [];
                    const sourceTbody = doc.querySelector('tbody');
                    if (sourceTbody) {
                        sourceRows = Array.from(sourceTbody.querySelectorAll('tr')).filter(tr => tr.children.length >= 3);
                    }

                    // 2) Fallback: parse text lines for items if no structured table
                    if (sourceRows.length === 0) {
                        // Very generic fallback: look for numbered lines like "1  Vanilla Frappe  1  $3.80  $3.80"
                        const lines = bodyText.split('\n').map(s => s.trim()).filter(Boolean);
                        let sn = 0;
                        lines.forEach(line => {
                            const m = line.match(/^(\d+)\s+(.+?)\s+(\d+)\s+\$?([\d\.\,]+)\s+\$?([\d\.\,]+)$/);
                            if (m) {
                                sn++;
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${sn}</td>
                                    <td>${m[2]}</td>
                                    <td class="text-center">${m[3]}</td>
                                    <td class="text-end">${m[4]}</td>
                                    <td class="text-end">${m[5]}</td>
                                `;
                                docTbody.appendChild(tr);
                            }
                        });
                    } else {
                        // Map invoice table rows to the document table shape
                        let sn = 0;
                        sourceRows.forEach(row => {
                            const cells = Array.from(row.children).map(td => td.innerText.trim());
                            if (!cells.length) return;
                            sn++;
                            // Try to guess columns: item name usually in col 1, qty in col 2 or 3, totals at end
                            const guessQty = (arr) => {
                                for (let i = 1; i < arr.length; i++) {
                                    if (/^\d+$/.test(arr[i])) return arr[i];
                                }
                                return '1';
                            };
                            const qty = guessQty(cells);
                            const price = cells.find(t => /\$/.test(t)) || '';
                            const total = cells.reverse().find(t => /\$/.test(t)) || price;
                            cells.reverse(); // restore

                            const itemName = cells[0] || '—';

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${sn}</td>
                                <td>${itemName}</td>
                                <td class="text-center">${qty}</td>
                                <td class="text-end">${price}</td>
                                <td class="text-end">${total}</td>
                            `;
                            docTbody.appendChild(tr);
                        });
                    }

                    // ===== Options/notes block (collect bullet lines like "• No Salty")
                    const bulletLines = bodyText.split('\n').filter(l => /^[•\-]\s/.test(l));
                    document.getElementById('docOptions').innerHTML = bulletLines.length
                        ? `<ul class="mb-0">${bulletLines.map(l => `<li>${l.replace(/^([•\-]\s)/,'')}</li>`).join('')}</ul>`
                        : '—';

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                    modal.show();
                })
                .catch(() => {
                    // Simple error message in table area if fetch fails
                    const docTbody = document.querySelector('#docTable tbody');
                    docTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load sale details.</td></tr>`;
                    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                    modal.show();
                });
        });
    });

    // Clear the modal contents when closed
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
