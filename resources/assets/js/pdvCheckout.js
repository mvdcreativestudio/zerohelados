$(document).ready(function () {
  let cart = [];
  const baseUrl = window.baseUrl || '';
  const frontRoute = window.frontRoute || '';
  let client = [];
  const cashRegisterId = window.cashRegisterId;
  let cashRegisterLogId = null;
  let sessionStoreId = null;

  function mostrarError(mensaje) {
    $('#errorContainer').text(mensaje).removeClass('d-none'); // Mostrar mensaje de error
  }

  function ocultarError() {
    $('#errorContainer').addClass('d-none'); // Ocultar el contenedor de errores
  }

  function obtenerCashRegisterLogId() {
    if (cashRegisterId) {
      $.ajax({
        url: `log/${cashRegisterId}`,
        type: 'GET',
        success: function (response) {
          cashRegisterLogId = response.cash_register_log_id;
        },
        error: function (xhr) {
          mostrarError('Error al obtener el ID de cash register log: ' + xhr.responseText);
        }
      });
    } else {
      console.error('ID de caja registradora no definido');
    }
  }

  function loadCartFromSession() {
    $.ajax({
      url: `cart`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (Array.isArray(response.cart)) {
          cart = response.cart;
        } else {
          cart = [];
        }
        updateCheckoutCart();
      },
      error: function (xhr) {
        mostrarError('Error al cargar el carrito desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function loadClientFromSession() {
    $.ajax({
      url: `client-session`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        client = response.client;

        if (client && client.id) {
          showClientInfo(client);
          $('#client-selection-container').hide();
        }
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function loadStoreIdFromSession() {
    $.ajax({
      url: `storeid-session`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        sessionStoreId = response.id;
        console.log('Hola + '+sessionStoreId)
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function showClientInfo(client) {
    $('#client-id').text(client.id);
    $('#client-name').text(client.name);
    $('#client-ci').text(client.ci);
    $('#client-rut').text(client.rut);
    $('#client-info').show();
    $('#client-selection-container').hide();
  }

  function saveCartToSession() {
    return $.ajax({
      url: 'cart',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        cart: cart
      }
    }).done(function (response) {
      console.log('Carrito guardado en la sesión:', response);
    }).fail(function (xhr) {
      mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText);
    });
  }

  function updateCheckoutCart() {
    let cartHtml = '';
    let subtotal = 0;

    if (!Array.isArray(cart)) {
        mostrarError('El carrito no es un array.');
        return;
    }

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        // Formatear el precio del producto y el total del ítem con separador de miles
        const formattedItemPrice = item.price.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        const formattedItemTotal = itemTotal.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

        cartHtml += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="${baseUrl}${item.image}" alt="${item.name}" class="img-thumbnail me-2" style="width: 50px;">
                <div>
                    <h6 class="mb-0">${item.name}</h6>
                    <small class="text-muted">Cantidad: ${item.quantity} x $${formattedItemPrice}</small>
                </div>
            </div>
            <span>$${formattedItemTotal}</span>
        </li>
        `;
    });

    const total = subtotal;

    // Formatear los valores de subtotal y total con separadores de miles
    const formattedSubtotal = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    const formattedTotal = total.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    $('.list-group-flush').html(cartHtml);

    $('.subtotal').text(`$${formattedSubtotal}`);
    $('.total').text(`$${formattedTotal}`);
  }



  function loadClients() {
    $.ajax({
      url: 'clients/json',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        const clients = response.clients;
        const clientCount = response.count;
        if (clientCount > 0) {
          $('#search-client-container').show();
        } else {
          $('#search-client-container').hide();
        }
        displayClients(clients);
      },
      error: function (xhr) {
        mostrarError('Error al obtener los clientes: ' + xhr.responseText);
      }
    });
  }

  function displayClients(clients) {
    const clientList = $('#client-list');
    clientList.empty();
    clients.forEach(client => {
      const clientItem = `
        <li class="list-group-item d-flex justify-content-between align-items-center client-item"
          data-name="${String(client.name).toLowerCase()}"
          data-ci="${String(client.ci).toLowerCase()}"
          data-rut="${String(client.rut).toLowerCase()}">
          data-email="${String(client.email).toLowerCase()}">
          data-phone="${String(client.phone).toLowerCase()}">
          data-address="${String(client.address).toLowerCase()}">
          <div>
            ${client.name}, CI: ${client.ci}, RUT: ${client.rut}
          </div>
          <button class="btn btn-primary btn-sm add-client" data-client='${JSON.stringify(client)}'>+</button>
        </li>
      `;
      clientList.append(clientItem);
    });

    $('.add-client').on('click', function () {
      const client = $(this).data('client');
      showClientInfo(client);

      saveClientToSession(client).done(function () {
        loadClientFromSession();
      }).fail(function (xhr) {
        mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
      });
    });
  }

  $('#search-client').on('input', function () {
    const searchText = $(this).val().toLowerCase();
    $('#client-list li').each(function () {
      const name = $(this).data('name').toString().toLowerCase();
      const ci = $(this).data('ci').toString().toLowerCase();
      const rut = $(this).data('rut').toString().toLowerCase();

      if (name.includes(searchText) || ci.includes(searchText) || rut.includes(searchText)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  function saveClientToSession(client) {
    return $.ajax({
      url: 'client-session',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        client: client
      }
    }).done(function (response) {
      console.log('Cliente guardado en la sesión:', response);
    }).fail(function (xhr) {
      mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
    });
  }

  $('#offcanvasEnd').on('show.bs.offcanvas', function () {
    loadClients();
  });

  document.getElementById('guardarCliente').addEventListener('click', function () {
    let nombre = document.getElementById('nombreCliente').value;
    let apellido = document.getElementById('apellidoCliente').value;
    let tipo = document.getElementById('tipoCliente').value;
    let email = document.getElementById('emailCliente').value;
    let ci = document.getElementById('ciCliente').value;
    let rut = document.getElementById('rutCliente').value;

    let data = {
      name: nombre,
      lastname: apellido,
      type: tipo,
      email: email
    };

    if (tipo === 'individual') {
      data.ci = ci;
    } else if (tipo === 'company') {
      data.rut = rut;
    }

    fetch('client', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(data => {
        let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
        offcanvas.hide();
        document.getElementById('formCrearCliente').reset();
      })
      .catch((error) => {
        mostrarError('Error al guardar el cliente: ' + error);
      });
  });

  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    if (tipo === 'individual') {
      document.getElementById('ciField').style.display = 'block';
      document.getElementById('rutField').style.display = 'none';
    } else if (tipo === 'company') {
      document.getElementById('ciField').style.display = 'none';
      document.getElementById('rutField').style.display = 'block';
    }
  });

  $('#deselect-client').on('click', function () {
    deselectClient();
  });

  function deselectClient() {
    client = [];
    saveClientToSession(client);

    $('#client-id').text('');
    $('#client-name').text('');
    $('#client-ci').text('');
    $('#client-rut').text('');
    $('#client-info').hide();
    $('#client-selection-container').show();
  }

  loadCartFromSession();
  loadClientFromSession();
  obtenerCashRegisterLogId();
  loadStoreIdFromSession();

  function postOrder() {
    ocultarError(); // Ocultar errores previos

    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    let cashSales = 0;
    let posSales = 0;

    // Convertir los valores de texto formateados a números enteros
    const total = parseInt($('.total').text().replace(/[^\d]/g, ''), 10) || 0; // Remover todo excepto dígitos y convertir a entero
    const subtotal = parseInt($('.subtotal').text().replace(/[^\d]/g, ''), 10) || 0; // Remover todo excepto dígitos y convertir a entero

    if (paymentMethod === 'cash') {
        cashSales = parseInt($('.total').text().replace('$', ''));
    } else {
        posSales = parseInt($('.total').text().replace('$', ''));
    }

    const orderData = {
        date: new Date().toISOString().split('T')[0],
        hour: new Date().toLocaleTimeString('it-IT'),
        cash_register_log_id: cashRegisterLogId,
        cash_sales: cashSales,
        pos_sales: posSales,
        discount: 0,
        client_id: client && client.id ? client.id : null,
        client_type: client && client.type ? client.type : 'individual',
        products: JSON.stringify(cart),
        subtotal: parseInt($('.subtotal').text().replace('$', '')),
        total: parseInt($('.total').text().replace('$', '')),
        notes: $('textarea').val() || ''
    };

    // Primero, hacer el POST a pos-orders
    $.ajax({
        url: `${baseUrl}admin/pos-orders`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ...orderData
        },
        success: function (response) {
            // Si la orden se guarda con éxito en pos-orders, proceder a guardar en orders
            const ordersData = {
                date: orderData.date,
                time: orderData.hour,
                origin: 'physical',
                client_id: orderData.client_id,
                store_id: sessionStoreId,
                products: orderData.products,
                subtotal: orderData.subtotal,
                tax: 0,
                shipping: 0,
                coupon_id: null,
                coupon_amount: 0,
                discount: orderData.discount,
                total: orderData.total,
                estimate_id: null,
                shipping_id: null,
                payment_status: 'paid',
                shipping_status: 'delivered',
                payment_method: paymentMethod,
                shipping_method: 'standard',
                preference_id: null,
                shipping_tracking: null,
                is_billed: 0,
                doc_type: null,
                document: null,
                name: client.name,
                lastname: client.lastname,
                address: client.address,
                phone: client.phone,
                email: client.email
            };
            $.ajax({
                url: `${baseUrl}admin/orders`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ...ordersData
                },
                success: function (response) {
                  console.log('Prueba');
                    saveCartToSession().done(function () {
                        updateCheckoutCart();
                        saveClientToSession(client).done(function () {
                            window.location.href = frontRoute;
                        }).fail(function (xhr) {
                            mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
                        });
                    }).fail(function (xhr) {
                        mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText);
                    });
                },
                error: function (xhr) {
                    mostrarError('Error al guardar la orden en orders: ' + xhr.responseText);
                }
            });
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errores = xhr.responseJSON.errors;
                let mensajes = '';
                for (const campo in errores) {
                    mensajes += `${errores[campo].join(', ')}<br>`;
                }
                mostrarError(mensajes);
            } else {
                mostrarError('Error al guardar la orden en pos-orders: ' + xhr.responseText);
            }
        }
    });
}

  $('.btn-success').on('click', function () {
    postOrder();
  });

  $('#descartarVentaBtn').on('click', function () {
    client = [];
    saveClientToSession(client);
    cart = [];
    saveCartToSession();
    updateCheckoutCart();
  });

  $('#valorRecibido').on('input', function () {
    // Obtener el valor recibido eliminando cualquier carácter que no sea un dígito o coma, y luego reemplazando la coma por un punto
    var valorRecibido = parseFloat($(this).val().replace(/[^\d,]/g, '').replace(',', '.')) || 0;

    // Obtener el total eliminando cualquier carácter que no sea un dígito o punto decimal
    var total = parseFloat($('.total').text().replace(/[^\d.-]/g, '').replace('.', '').replace(',', '.')) || 0;

    // Calcular el vuelto
    var vuelto = valorRecibido - total;

    // Verificar si el valor recibido es menor que el total
    if (valorRecibido < total) {
        $('#mensajeError').removeClass('d-none');
    } else {
        $('#mensajeError').addClass('d-none');
    }

    // Formatear el vuelto con separadores de miles, mínimo de 0 decimales y máximo de 2
    var formattedVuelto = vuelto.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Mostrar el vuelto formateado
    $('#vuelto').text(`${formattedVuelto}`);
});


});
