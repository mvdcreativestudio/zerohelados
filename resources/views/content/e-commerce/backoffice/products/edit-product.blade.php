@extends('layouts/layoutMaster')

@section('title', 'Editar Producto')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/tagify/tagify.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-product-edit.js'])
@endsection

@section('content')
<div>
    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-4 mb-3 rounded shadow sticky-top">

            <!-- Título del formulario -->
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-0">
                    <i class="bx bx-edit-alt me-2"></i> Editar Producto
                </h4>
            </div>

            <!-- Botones de acciones -->
            <div class="d-flex justify-content-end gap-3">
                <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                    <i class="bx bx-x me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save me-1"></i> Guardar Cambios
                </button>
            </div>

        </div>


        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="app-ecommerce" data-raw-materials='@json($rawMaterials)'
            data-recipes='@json($product->recipes)' data-flavors='@json($flavors)'>

            <!-- Add Product -->



            <div class="row">

                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Product Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Información del producto</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-name">Nombre</label>
                                <input type="text" class="form-control" id="ecommerce-product-name"
                                    placeholder="Nombre del producto" name="name" value="{{ $product->name }}"
                                    aria-label="Nombre del producto">
                            </div>
                            <div class="row mb-3">
                                <div class="col"><label class="form-label" for="ecommerce-product-sku">SKU</label>
                                    <input type="number" class="form-control" id="ecommerce-product-sku" placeholder="SKU"
                                        name="sku" value="{{ $product->sku }}" aria-label="SKU">
                                </div>
                            </div>
                            <!-- Description -->
                            <div>
                                <label class="form-label">Descripción <span class="text-muted">(Opcional)</span></label>
                                <div class="form-control p-0 pt-1">
                                    <div class="comment-toolbar border-0 border-bottom">
                                        <div class="d-flex justify-content-start">
                                            <span class="ql-formats me-0">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="comment-editor border-0 pb-4" id="ecommerce-category-description"></div>
                                    <!-- Campo oculto para enviar la descripción -->
                                    <input type="hidden" name="description" id="hiddenDescription"
                                        value="{{ old('description', $product->description) }}">
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /Product Information -->
                    <!-- Variants -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Tipo de producto y variaciones</h5>
                        </div>
                        <div class="card-body">
                            <div data-repeater-list="group-a">
                                <div data-repeater-item>
                                    <div class="row">
                                        <div class="mb-3 col-4">
                                            <label class="form-label" for="form-repeater-1-1">Tipo de producto</label>
                                            <select id="productType" class="select2 form-select" name="type">
                                                <option value="simple" @selected($product->type == 'simple')>Simple</option>
                                                <option value="configurable" @selected($product->type == 'configurable')>Variable</option>
                                            </select>
                                        </div>
                                        <div id="flavorsQuantityContainer" class="mb-3 col-4">
                                            <label class="form-label" for="max-flavors">Variaciones</label>
                                            <input type="text" class="form-control" id="max_flavors"
                                                value="{{ $product->max_flavors }}"
                                                placeholder="Cantidad máxima de variaciones" name="max_flavors"
                                                aria-label="Cantidad máxima de variaciones">
                                        </div>
                                    </div>
                                </div>
                                <div id="flavorsContainer" class="mb-3 col-8">
                                    <label class="form-label">Variaciones disponibles</label>
                                    <select class="select2 form-select variationOptions" multiple="multiple"
                                        name="flavors[]"
                                        data-selected="{{ json_encode($product->flavors->pluck('id')->toArray()) }}">
                                        @foreach ($flavors as $flavor)
                                            <option value="{{ $flavor->id }}"
                                                {{ in_array($flavor->id, $product->flavors->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                {{ $flavor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Variants -->
                    {{-- <!-- Recipe -->
      <div class="card mb-4" id="recipeCard" style="display: none;">
        <div class="card-header">
          <h5 class="card-title mb-0">Receta</h5>
        </div>
        <div class="card-body">
          <div data-repeater-list="recipes">
          </div>
          <button type="button" class="btn btn-primary" id="addRawMaterial">Agregar Materia Prima</button>
          <button type="button" class="btn btn-secondary" id="addUsedFlavor">Agregar Sabor Usado</button>
        </div>
      </div> --}}

                </div>
                <!-- /Second column -->

                <!-- Second column -->
                <div class="col-12 col-lg-4">

                    <!-- Pricing Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Precio</h5>
                        </div>
                        <div class="card-body">
                            <!-- Base Price -->
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-price">Precio normal - <small>IVA
                                        INCLUÍDO</small></label>
                                <input type="number" class="form-control" step=".01" min="0"
                                    id="ecommerce-product-price" placeholder="Precio" name="old_price"
                                    value="{{ $product->old_price }}" aria-label="Product price" required>
                            </div>
                            <!-- Discounted Price -->
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-discount-price">Precio oferta -
                                    <small>IVA INCLUÍDO</small></label>
                                <input type="number" class="form-control" min="0" step=".01"
                                    id="ecommerce-product-discount-price" placeholder="Precio oferta" name="price"
                                    value="{{ $product->price }}" aria-label="Introduzca el precio rebajado">
                            </div>
                            <!-- Build Price -->
                            <div class="mb-3">
                                <label class="form-label" for="build_price">Costo</label>
                                <input type="number" step=".01" min="0" class="form-control"
                                    id="build_price" placeholder="Introduzca el costo del producto" name="build_price"
                                    aria-label="Introduzca el costo"
                                    value="{{ $product->build_price }}">
                            </div>
                            <!-- Hidden field for disabled status -->
                            <input type="hidden" name="status" value="2">

                            <!-- Instock switch -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                                <span class="mb-0 h6">Estado</span>
                                <div class="w-25 d-flex justify-content-end">
                                    <label class="switch switch-primary switch-sm me-4 pe-2">
                                        <input type="checkbox" class="switch-input" id="statusSwitch"
                                            name="status" value="1" data-status="{{ $product->status }}"
                                            {{ $product->status == 1 ? 'checked' : '' }}>
                                        <span class="switch-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Pricing Card -->

                    <!-- Organize Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Organizar</h5>
                        </div>
                        <div class="card-body">
                            <!-- Vendor -->
                            <div class="mb-3">
                                <label class="form-label" for="vendor">Empresa</label>
                                <select id="vendor" class="select2 form-select" data-placeholder="Seleccionar local"
                                    name="store_id" required>
                                    @if (auth()->user()->hasPermissionTo('access_global_products'))
                                        <option value="">Seleccionar local</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ $product->store_id == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="{{ auth()->user()->store_id }}" selected>
                                            {{ auth()->user()->store->name }}</option>
                                    @endif
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="mb-3 col ecommerce-select2-dropdown">
                              <select id="category-org" class="select2 form-select" data-placeholder="Seleccione la categoría" name="categories[]" multiple data-selected="{{ json_encode($product->categories->pluck('id')->toArray()) }}">
                                @foreach ($categories as $category)
                                  <option value="{{ $category->id }}">
                                      {{ $category->name }}
                                  </option>
                                @endforeach
                              </select>
                            </div>


                            <!-- Stock -->
                            <div class="mb-3">
                                <label class="form-label" for="stock">Stock</label>
                                <input type="number" class="form-control" id="stock" placeholder="Stock"
                                    name="stock" value="{{ $product->stock }}" aria-label="Introduzca el stock">
                            </div>

                            <!-- Safety Margin -->
                            <div class="mb-3">
                                <label class="form-label" for="safety_margin">Margen de Seguridad</label>
                                <input type="number" class="form-control" id="safety_margin"
                                    placeholder="Margen de seguridad" name="safety_margin"
                                    value="{{ $product->safety_margin }}" aria-label="Introduzca el margen de seguridad">
                            </div>

                            <!-- Barcode -->
                            <div class="mb-3">
                                <label class="form-label" for="bar_code">Código de Barras</label>
                                <input type="text" class="form-control" id="bar_code" placeholder="Código de barras"
                                    name="bar_code" value="{{ $product->bar_code }}"
                                    aria-label="Introduzca el código de barras">
                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->

                    <!-- Media Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Imagen del Producto</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 text-center" id="existingImage">
                                @if ($product->image)
                                    <img src="{{ asset($product->image) }}" alt="Imagen del producto"
                                        class="img-fluid mb-3" id="productImagePreview">
                                @endif
                            </div>
                            <div class="dropzone dz-clickable" id="dropzone">
                                <div class="dz-message needsclick">
                                    <p class="fs-4 note needsclick my-2">Arrastra la imagen aquí</p>
                                    <small class="text-muted d-block fs-6 my-2">o</small>
                                    <span class="note needsclick btn bg-label-primary d-inline" id="btnBrowse">Buscar
                                        imagen</span>
                                </div>
                            </div>
                            <input type="file" name="image" id="productImage" class="d-none">
                        </div>
                    </div>
                    <!-- /Media Card -->

                </div>
                <!-- /Second column -->

            </div>
    </form>
  </div>
@endsection
