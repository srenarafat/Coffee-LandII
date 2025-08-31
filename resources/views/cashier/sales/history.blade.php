@extends('layouts.app')

@section('content')
<div class="container-fluid mt-0">
    <div class="card shadow-sm">
    <div class="card-body print-area">
        @include('partials.sales-print', [
            'sales' => $sales,
            'totalAmount' => $totalAmount,
            'exportRoute' => route('cashier.sales.history', array_merge(request()->except('page'), ['export' => 'csv'])),
            'printRoute' => route('cashier.sales.history', array_merge(request()->all(), ['print' => 1])),
            'filter' => view('cashier.sales.filter', compact('categories'))->render(),
        ])
    </div>
</div>
</div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <a href="#" id="invoicePdfLink" class="btn btn-primary" target="_blank">Print PDF</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            fetch(`/cashier/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const styles = [...doc.querySelectorAll('style')];
                    styles.forEach(style => {
                        style.textContent = style.textContent.replace(/(^|\})\s*([^@\}][^{]+)/g, (match, p1, p2) => {
                            const selectors = p2.split(',').map(s => `.invoice-content ${s.trim()}`).join(', ');
                            return `${p1} ${selectors}`;
                        });
                        style.remove();
                    });

                    const wrapper = document.createElement('div');
                    wrapper.className = 'invoice-content';
                    wrapper.innerHTML = doc.body.innerHTML;
                    styles.forEach(style => wrapper.prepend(style));

                    const modalBody = document.querySelector('#invoiceModal .modal-body');
                    modalBody.innerHTML = '';
                    modalBody.appendChild(wrapper);

                    document.getElementById('invoicePdfLink').href = `/cashier/invoice/${saleId}/pdf`;
                    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
                    modal.show();
                });
        });
    });
    
    document.getElementById('invoiceModal').addEventListener('hidden.bs.modal', function () {
        document.querySelector('#invoiceModal .modal-body').innerHTML = '';
    });
});
</script>
@endpush