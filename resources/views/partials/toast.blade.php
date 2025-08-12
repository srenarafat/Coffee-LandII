<div id="toast" class="position-fixed top-0 start-50 translate-middle-x px-3 mt-3" style="z-index: 1100; display: none;">
    <div id="toastBody" class="alert alert-danger shadow rounded mb-0 py-2 px-4 text-sm text-black bg-[#6f2e18] border-0"></div>
</div>

@push('scripts')
<script>
function showToast(message) {
    const toast = document.getElementById('toast');
    const toastBody = document.getElementById('toastBody');
    if (!toast || !toastBody) return;
    toastBody.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
</script>
@endpush