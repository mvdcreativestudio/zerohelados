@extends('layouts/layoutMaster')

@section('title', 'Listado de Tiendas')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
    window.storeAdd = "{{ route('stores.create') }}";
    window.storeEditTemplate = "{{ route('stores.edit', ':id') }}";
    window.storeDeleteTemplate = "{{ route('stores.destroy', ':id') }}";
    window.storeManageUsersTemplate = "{{ route('stores.manageUsers', ':id') }}";
    window.storeManageHours = "{{ route('stores.manageHours', ':id') }}";
    window.toggleStoreStatus= "{{ route('stores.toggle-status', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
    var stores = @json($stores);
</script>
@vite(['resources/assets/js/app-stores-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Administración /</span> Tiendas
</h4>

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de Tiendas</h6>
              <h4 class="mb-2">{{ $stores->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-store bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>

        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Tiendas Activas</h6>
              <h4 class="mb-2">{{ $stores->filter(fn($store) => $store->status == true)->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Activas</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-check-circle bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>

        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-3 pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Tiendas Inactivas</h6>
              <h4 class="mb-2">{{ $stores->filter(fn($store) => $store->status == false)->count() }}</h4>
              <p class="mb-0 text-muted">Inactivas</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="bx bx-block bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
      </div>
    </div>
  </div>
</div>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
  <div class="alert alert-danger">
    {{ $error }}
  </div>
@endforeach
@endif


<div class="card">
  <div class="card-header">
    <h5 class="card-title">Tiendas</h5>
  </div>
  <div id="dataTableInit" style="display:none;"></div>
  <div class="card-datatable table-responsive">
    <table class="table datatables-stores border-top">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Dirección</th>
          <th>RUT</th>
          <th>Estado</th>
          <th>Miembros</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection
