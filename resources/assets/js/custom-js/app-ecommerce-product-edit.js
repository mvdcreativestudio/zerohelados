/**
 * App eCommerce Edit Product Script
 */
'use strict';

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize Quill editor for product description
    const commentEditorElement = document.querySelector('.comment-editor');
    const initialDescription = document.querySelector('#hiddenDescription').value; // Assuming value is passed from the server

    if (commentEditorElement) {
      const quill = new Quill(commentEditorElement, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Descripción del producto',
        theme: 'snow'
      });

      // Set initial content if available
      quill.root.innerHTML = initialDescription;

      const form = commentEditorElement.closest('form');
      if (form) {
        form.addEventListener('submit', function () {
          const hiddenInput = document.getElementById('hiddenDescription');
          if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;  // Save Quill's HTML into hidden input on submit
          }
        });
      }
    }

    // Initialize Select2 Components with existing values
    $('.select2').each(function() {
      $(this).select2().val($(this).data('selected')).trigger('change');
    });

    // Initialize Dropzone for image upload
    const dropzoneElement = document.querySelector('#dropzone-basic');
    if (dropzoneElement) {
      const myDropzone = new Dropzone(dropzoneElement, {
        url: '/upload-target',  // Set the url for your upload script
        previewTemplate: document.querySelector('#preview-template').innerHTML,
        addRemoveLinks: true,
        maxFiles: 1,
        init: function () {
          this.on("addedfile", function (file) {
            if (this.files.length > 1) {
              this.removeFile(this.files[0]);  // Only one file permitted
            }
          });
        }
      });
    }

    // Initialize flatpickr for date selection fields
    const expiryDateInput = document.querySelector('.product-date');
    if (expiryDateInput) {
      flatpickr(expiryDateInput, { dateFormat: "Y-m-d" });
    }

    // Initialize state switch
    const statusSwitch = document.getElementById('statusSwitch');
    if (statusSwitch) {
      statusSwitch.checked = statusSwitch.value === '1';
      statusSwitch.addEventListener('change', function () {
        this.value = this.checked ? '1' : '2';
      });
    }

    // Setup event listeners for dynamically showing/hiding elements
    const productTypeSelect = document.getElementById('productType');
    const flavorsContainer = document.getElementById('flavorsContainer');
    const maxFlavorsInput = document.getElementById('max_flavors');

    if (productTypeSelect && flavorsContainer && maxFlavorsInput) {
      const toggleFlavorsVisibility = () => {
        const isConfigurable = productTypeSelect.value === 'configurable';
        flavorsContainer.style.display = isConfigurable ? 'block' : 'none';
        maxFlavorsInput.style.display = isConfigurable ? 'block' : 'none';
      };

      productTypeSelect.addEventListener('change', toggleFlavorsVisibility);
      toggleFlavorsVisibility(); // Initial call to set visibility based on current product type
    }

    // Handle discard button logic with SweetAlert for confirmation
    const discardButton = document.getElementById('discardButton');
    if (discardButton) {
      discardButton.addEventListener('click', function () {
        Swal.fire({
          title: '¿Estás seguro?',
          text: "Perderás todos los datos no guardados si sales de esta página.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, descartar cambios',
          cancelButtonText: 'No, volver'
        }).then((result) => {
          if (result.isConfirmed) {
            window.history.back();
          }
        });
      });
    }
  });
})();
