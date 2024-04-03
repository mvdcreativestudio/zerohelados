@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Store')

@section('content')

<div class="container">



  @if(session('success'))
<script>
  let timerInterval;
Swal.fire({
  title: "¡Correcto!",
  position: "top-end",
  text: '{{ session('success')}}',
  timer: 2000,
  icon: 'success',
  timerProgressBar: true,
  showConfirmButton: false,
  customClass: {
        popup: 'added-to-cart-popup'
    },
  didOpen: () => {
    const timer = Swal.getPopup().querySelector("b");
    timerInterval = setInterval(() => {
      timer.textContent = `${Swal.getTimerLeft()}`;
    }, 100);
  },
  willClose: () => {
    clearInterval(timerInterval);
  }
}).then((result) => {
  /* Read more about handling dismissals below */
  if (result.dismiss === Swal.DismissReason.timer) {
    console.log("I was closed by the timer");
  }
});
</script>
@endif


@if(session('error'))
<script>
    Swal.fire({
        title: 'Error',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif

<div class="container vh-100">


  <!-- Categories -->
  <div class="row mt-4">
    <div class="card card-category col-md-3 col-6">
      <img src="assets\img\ecommerce\Cat-1.png" alt="Helados">
      <div class="category-card-text">
        <h5 class="category-name light">Helados</h5>
      </div>
    </div>
    <div class="card card-category col-md-3 col-6">
      <img src="assets\img\ecommerce\Cat-2.png" alt="Helados">
      <div class="category-card-text">
        <h5 class="category-name light">Milkshakes</h5>
      </div>
    </div>
    <div class="card card-category col-md-3 col-6">
      <img src="assets\img\ecommerce\Cat-3.png" alt="Helados">
      <div class="category-card-text">
        <h5 class="category-name light">Paletas</h5>
      </div>
    </div>
    <div class="card card-category col-md-3 col-6">
      <img src="assets\img\ecommerce\Cat-4.png" alt="Helados">
      <div class="category-card-text">
        <h5 class="category-name light">Tortas</h5>
      </div>
    </div>
  </div>

  <!-- End Categories -->

  <!-- Categories and Products -->
  @foreach($categories as $category)
    @if($category->products->isNotEmpty())
      <div class="category-section mt-5">
        <h2 class="store-category-title bold text-center mb-5">{{ $category->name }}</h2>
        <div class="products-container">
          <div class="row">
            @foreach ($category->products as $product)
              <div class="col-md-2 col-6" data-bs-toggle="modal" data-bs-target="#modalCenter"
                  data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-img="{{ $product->image }}" data-price="{{ $product->price }}">
                <div class="card card-product">
                  <img src="{{ asset($product->image) }}" alt="Product">
                  <div class="product-card-text">
                    <h5 class="product-name light">{{ $product->name }}</h5>
                    <div class="d-flex justify-content-center">
                      @if($product->price == null)
                        <p class="product-price bold mx-1">${{ $product->old_price }}</p>
                      @else
                        <s class="text-muted mx-1">${{ $product->old_price }}</s>
                        <p class="product-price bold mx-1">${{ $product->price }}</p>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif
  @endforeach


  <div class="whatsapp-box">
    <a href="https://wa.me/59899999999" target="_blank">
      <img class="whatsapp-box-img" src="assets\img\ecommerce\whatsapp-icon.png" alt="Whatsapp">
    </a>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="mt-3 cart-box">
      <img class="cart-box-img" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd" src="{{ asset('assets/img/ecommerce/cart-icon.png') }}" alt="Cart">
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
        <div class="offcanvas-header">
          <h5 id="offcanvasEndLabel" class="offcanvas-title">Carrito</h5>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
          <div class="cart-product-list">
            @if(session('cart'))
                @foreach(session('cart') as $id => $details)
                    <div class="card cart-product-item">
                        <img src="{{$details['image']}}" alt="{{ $details['name'] }}" class="cart-product-img">
                        <div class="product-card-text">
                            <h5 class="product-name">{{ $details['name'] }}</h5>
                            <div class="cart-product-variants-container text-center justify-content-center">
                              @if ($details['price'] == null)
                                <small class="cart-product-variants">{{ $details['quantity'] }} x ${{ $details['old_price'] }}</small>
                              @else
                                <small class="cart-product-variants">{{ $details['quantity'] }} x ${{ $details['price'] }}</small>
                              @endif
                            </div>
                            @if ($details['price'] == null)
                              <p class="product-price">${{ $details['old_price'] * $details['quantity'] }}</p>
                            @else
                              <p class="product-price">${{ $details['price'] * $details['quantity'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <p>Tu carrito está vacío.</p>
            @endif
        </div>
    </div>
    <div class="offcanvas-footer offcanvas-cart-footer">
      @if(session('cart'))
          <div class="cart-total-price">
              @php $total = 0; @endphp
              @foreach(session('cart') as $id => $details)
                  {{-- Seleccionar price o old_price según la disponibilidad de price --}}
                  @php
                      $priceToUse = isset($details['price']) && $details['price'] != null ? $details['price'] : $details['old_price'];
                      $total += $priceToUse * $details['quantity'];
                  @endphp
              @endforeach
              <h6>Subtotal: ${{ $total }}</h6>
              <h6>Envío: $90</h6>
              <h5 class="bold">Total: ${{ $total + 90 }}</h5>
          </div>
      @endif
      <button type="button" class="btn btn-label-secondary d-grid offcanvas-cart-button" data-bs-dismiss="offcanvas">Continuar comprando</button>
      <a href="{{ route('checkout.index') }}"><button type="button" class="btn btn-primary mb-2 d-grid offcanvas-cart-button">Finalizar compra</button></a>
      @if(session()->has('store'))
        <h6 class="text-center mt-3">Tienda seleccionada: <b>{{ session('store')['name'] }}</b></h6>
      @endif
      </div>
  </div>

</div>

</div>

</div>
</div>

<!-- Add to cart modal -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Product Name</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="add-to-cart-img-container justify-content-center text-center mb-5">
          <img src="" alt="Product" class="add-to-cart-img">
        </div>
        <!-- Product options like flavors could be dynamically generated as well -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
        <form action="{{ route('cart.add', $product->id) }}" method="POST">
          @csrf
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Añadir al Carrito</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      var exampleModal = document.getElementById('modalCenter');
      exampleModal.addEventListener('show.bs.modal', function (event) {
          // Elemento que disparó el modal
          var button = event.relatedTarget;

          // Extracción de la información del atributo data-*
          var productName = button.getAttribute('data-name');
          var productImg = button.getAttribute('data-img');
          var productId = button.getAttribute('data-id');

          // Actualización de los contenidos del modal
          var modalTitle = exampleModal.querySelector('.modal-title');
          var modalImg = exampleModal.querySelector('.add-to-cart-img');
          var form = exampleModal.querySelector('.modal-footer form');

          modalTitle.textContent = productName;
          modalImg.src = productImg;

          // Asegúrate de que la URL se construye correctamente
          form.action = '{{ url('cart/add') }}/' + productId;
      });
  });
</script>





@endsection
