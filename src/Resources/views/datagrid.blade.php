@push('styles')
    <link href="{{ asset('css/datagrid.min.css') }}" rel="stylesheet">
@endpush
<x-datagrid::datagrid 
    :uid="$uid"
    :baseUrl="$baseUrl"
    :columns="$columns"
    :actions="$actions"
    :massActions="$massActions"
    :massActionTitle="$massActionTitle ?? 'Select action'"
    :data="$data">
</x-datagrid::datagrid>
<!-- Add Scripts -->
@push('scripts')
    <script src="{{ asset('js/datagrid.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data Grid
            const dataGridNonAjax = new ZkDataGrid();
            dataGridNonAjax.setGrid('grid-{{ $uid }}');
        });
    </script>
@endpush