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
    const routePrefix = "{{ auth()->user()->role === 'superadmin' ? 'superadmin' : 'admin' }}";
    document.querySelectorAll('.view-invoice').forEach(btn => {
        btn.addEventListener('click', function () {
            const saleId = this.dataset.saleId;
            fetch(`/${routePrefix}/sales/${saleId}/invoice`)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Scope or remove any style tags to prevent leaking styles
                    doc.querySelectorAll('style').forEach(style => {
                        const scopedCSS = style.textContent.split('}').map(rule => {
                            const parts = rule.split('{');
                            if (parts.length < 2) return '';
                            const selectors = parts[0];
                            const declarations = parts[1];
                            if (selectors.trim().startsWith('@')) {
                                return selectors + '{' + declarations + '}';
                            }
                            const prefixed = selectors.split(',').map(sel => {
                                sel = sel.trim();
                                if (sel === 'body' || sel === 'html') {
                                    return '.invoice-content';
                                }
                                return `.invoice-content ${sel}`;
                            }).join(', ');
                            return `${prefixed}{${declarations}}`;
                        }).join('');
                        const scopedStyle = doc.createElement('style');
                        scopedStyle.textContent = scopedCSS;
                        style.replaceWith(scopedStyle);
                    });

                    const modalBody = document.querySelector('#invoiceModal .modal-body');
                    modalBody.innerHTML = `<div class="invoice-content">${doc.body.innerHTML}</div>`;

                    document.getElementById('invoicePdfLink').href = `/${routePrefix}/invoice/${saleId}/pdf`;
                    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
                    modal.show();
                });
        });
    });
    
    const invoiceModal = document.getElementById('invoiceModal');
    invoiceModal.addEventListener('hidden.bs.modal', function () {
        invoiceModal.querySelector('.modal-body').innerHTML = '';
    });
});
</script>
@endpush

