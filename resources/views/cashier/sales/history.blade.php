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
                <a href="#" id="reportPdfLink" class="btn btn-primary" target="_blank" rel="noopener">⬇️ Export PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/report-print.css')
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
@vite('resources/js/saleReportModal.js')
<script>
document.addEventListener('DOMContentLoaded', function () {
saleReportModal({ routePrefix: 'cashier' });
});
</script>
@endpush
