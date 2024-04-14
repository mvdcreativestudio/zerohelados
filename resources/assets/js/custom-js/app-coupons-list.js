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

  var dt_coupon_table = $('.datatables-coupons');

  if (dt_coupon_table.length) {
    var dt_coupons = dt_coupon_table.DataTable({
      ajax: 'coupons/datatable',
      columns: [
        { data: 'id' },
        { data: 'code' },
        { data: 'type' },
        { data: 'amount' },
        { data: 'product_categories' },
        { data: 'products'},
        { data: 'due_date'},
        { data: '' }
      ],
      columnDefs: [

      ],
      order: [1, 'asc'],
      dom:
        '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
        lengthMenu: [10, 25, 50, 100],
        language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoFiltered: "filtrados de _MAX_ cupones",
        paginate: {
          first: '<<',
          last: '>>',
          next: '>',
          previous: '<'
        },


      }
    });


    // Estilos buscador y paginación
    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');

  }
});
