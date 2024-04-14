@extends('layouts/layoutMaster')

@section('title', 'Productos')

@section('page-script')
@vite([
  'resources/assets/js/custom-js/app-coupons-list.js'
])
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('content')

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Marketing /</span> Cupones
</h4>

<div class="row">
  <div class="col-sm-12 col-lg-4 mb-4">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-check"></i></span>
          </div>
          <h4 class="ms-1 mb-0">42</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Total Cupones</p>
      </div>
    </div>
  </div>
  <div class="col-sm-12 col-lg-4 mb-4">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-info"><i class='bx bx-time'></i></span>
          </div>
          <h4 class="ms-1 mb-0">8</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Cupones Activos</p>
      </div>
    </div>
  </div>
  <div class="col-sm-12 col-lg-4 mb-4">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-danger"><i class='bx bx-error-circle'></i></span>
          </div>
          <h4 class="ms-1 mb-0">34</h4>
        </div>
        <p class="mb-1">Cupones Inactivos</p>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <h5 class="card-header">Cupones</h5>
  <div class="card-datatable table-responsive pt-0">
    <table class="table datatables-coupons">
      <thead>
        <tr>
          <th>ID</th>
          <th>Código</th>
          <th>Tipo</th>
          <th>Valor</th>
          <th>Categorías de Producto</th>
          <th>Productos</th>
          <th>Fecha de Expiración</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <!-- Datos llenados por DataTables -->
      </tbody>
    </table>
  </div>
</div>





@endsection
