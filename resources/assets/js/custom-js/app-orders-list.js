/**
 * app-ecommerce-order-list Script
 */

'use strict';

// Datatable (jquery)

$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  // Variable declaration for table

  var dt_order_table = $('.datatables-order');

  // E-commerce Products datatable

  if (dt_order_table.length) {
    var dt_products = dt_order_table.DataTable({
      ajax: 'orders/datatable',
      columns: [
        { data: 'id' },
        { data: 'date' },
        { data: 'client_name' },
        { data: 'store_name' },
        { data: 'total' },
        { data: '' }
      ],
      columnDefs: [
        {
          // ID del pedido
          targets: 0,
          render: function (data, type, full, meta) {
            return '#' + data;
          }
        },
        {
          // Fecha del pedido
          targets: 1,
          render: function (data, type, full, meta) {
            return moment(data).locale('es').format('DD/MM/YY');
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Acciones',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-flex justify-content-sm-center align-items-sm-center">' +
              '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href=" ' +
              baseUrl +
              'app/ecommerce/order/details" class="dropdown-item">Ver pedido</a>' +
              '<a href="javascript:0;" class="dropdown-item delete-record">' +
              'Eliminar' +
              '</a>' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          // Información del cliente
          targets: 2, // Ajusta este valor al índice correcto de tu columna "Cliente"
          render: function (data, type, full, meta) {
            var $name = full['client_name'],
                $email = full['client_email'],
                $output;

          // Extraer la primera letra del nombre y la primera letra del apellido
          var names = $name.split(' '),
              $initials = '';
          if (names.length > 1) {
            $initials = names[0].charAt(0) + names[1].charAt(0); // Primera letra del nombre y apellido
          } else {
            $initials = names[0].charAt(0); // Solo hay un nombre, toma la primera letra
          }
          $initials = $initials.toUpperCase();

          // Ajusta el color del avatar de manera aleatoria
          var stateNum = Math.floor(Math.random() * 6);
          var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
          var $state = states[stateNum];

          // Genera el avatar con las iniciales
          $output = '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';

          // Crea el output completo para la fila
          var $row_output =
            '<div class="d-flex justify-content-start align-items-center">' +
            '<div class="avatar-wrapper">' +
            '<div class="avatar me-2">' + $output + '</div>' +
            '</div>' +
            '<div class="d-flex flex-column">' +
            '<h6 class="m-0"><a href="javascript:void(0);" class="text-body">' + $name + '</a></h6>' +
            '<small class="text-muted">' + $email + '</small>' +
            '</div>' +
            '</div>';
          return $row_output;
          }
        },
        {
          // Total
          targets: 4,
          render: function (data, type, full, meta) {
            return '$' + data;
          }
        }
      ],
      order: [3, 'asc'], //set any columns order asc/desc
      dom:
        '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [10, 40, 60, 80, 100], //for length of menu
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ tiendas',
        paginate: {
          first: 'Primero',
          last: 'Último',
          next: '<span class="mx-2">Siguiente</span>',
          previous: '<span class="mx-2">Anterior</span>'
        },
      } 

    });
    $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
    $('.dt-action-buttons').addClass('pt-0');
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');
  }

  // Delete Record
  $('.datatables-order tbody').on('click', '.delete-record', function () {
    dt_products.row($(this).parents('tr')).remove().draw();
  });

});
