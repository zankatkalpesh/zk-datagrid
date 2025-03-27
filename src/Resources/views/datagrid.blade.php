@props(['grid'])
@push('styles')
    <link href="{{ asset('css/datagrid.min.css') }}" rel="stylesheet">
@endpush
<x-datagrid::datagrid :grid="$grid"/>
<!-- Add Scripts -->
@push('scripts')
    <script src="{{ asset('js/datagrid.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data Grid
            const dataGridNonAjax = new ZkDataGrid();
            dataGridNonAjax.setGrid('grid-{{ $grid["uid"] }}');
        });
    </script>
@endpush