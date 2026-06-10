$(document).ready(function() {
    $("#productSearch").select2({
        placeholder: "Search for a product...",
        ajax: {
            url: "includes/product_search.inc.php", // Path to your PHP file
            dataType: "json",
            delay: 250, // Delay before sending the request
            data: function(params) {
                return {
                    q: params.term // Pass search term to PHP file
                };
            },
            processResults: function(data) {
                return {
                    results: data.items // Format the response
                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup; // Allow HTML formatting in results
        }
    });
});

$('#submit').click(function(e) {
    e.preventDefault();

    var product_id = $('#productSearch').val();
    var qty = $('#qty').val();
    var rate = $('#rate').val();
    var related_id = $('#related_id').val();
    var invoice = $('#invoice').val();
    var supplier_id = $('#supplier_id').val();
    var prev_invoice_date = $('#prev_invoice_date').val();
    var invoice_date = $('#invoice_date').val();
    var expiry_date = $('#expiry_date').val();


    if(product_id == '' || qty == ''|| rate == '' ){
        
        Swal.fire({
            icon: 'error', // Show correct icon
            title: 'Fill up all data',
            showConfirmButton: false,
            timer: 2000
          });
          return false;
    }
    $.ajax({
        url: 'includes/PurchaseSessionData.inc.php',
        type: 'POST',
        data: {
            action: 'add_to_cart',
            product_id: product_id,
            qty: qty,
            rate: rate,
            related_id: related_id,
            invoice: invoice,
            supplier_id: supplier_id,
            prev_invoice_date: prev_invoice_date,
            invoice_date: invoice_date,
            expiry_date: expiry_date

        },
        success: function(response) {
            var result = JSON.parse(response);
            Swal.fire({
                icon: 'success', // Show correct icon
                title: result.message,
                showConfirmButton: false,
                timer: 2000
              });

                loadCart(related_id); // Reload the cart items (this function is for rendering the cart items dynamically)
          
        }
    });
});

$(document).on('click', '.delete-item', function() {
   var item_id = $(this).data('item_id'); // Get the product ID from the data attribute
    var invoice_id = $('#related_id').val();


    $.ajax({
        url: 'includes/PurchaseSessionData.inc.php',
        type: 'POST',
        data: {
            action: 'delete_from_cart',
            item_id: item_id,
            invoice_id: invoice_id
        },
        success: function(response) {
            var result = JSON.parse(response);
                        console.log(result);

            Swal.fire({
                icon: 'success', // Show correct icon
                title: result.message,
                showConfirmButton: false,
                timer: 2000
              });
            if (result.status === 'success') {
                loadCart(invoice_id); // Reload the cart after deletion
            }
        }
    });
});

$('#finalSubmit').click(function(e) { 
    e.preventDefault();

    var invoice = $('#invoice').val();
    var supplier_name = $('#sup_name').val();
    var countItem = $('#countItem').val();
    var related_id = $('#related_id').val();
    var prev_invoice_date = $('#prev_invoice_date').val();
    var invoice_date = $('#invoice_date').val();


    if (countItem < 1) {
        Swal.fire({
            icon: 'error',
            title: '2No item added',
            showConfirmButton: false,
            timer: 2000
        });
        return false;
    }

    if (invoice == '' || supplier_name == '') {
        Swal.fire({
            icon: 'error',
            title: 'Fill up all data',
            showConfirmButton: false,
            timer: 2000
        });
        return false;
    }

    // Fetch cart data from PHP and convert it into JSON format
    $.ajax({
        url: 'includes/PurchaseSessionData.inc.php',
        type: 'POST',
        data: {
            action: 'submit_cart',
            invoice: invoice,
            sup_name: supplier_name,
            related_id: related_id,
            prev_invoice_date: prev_invoice_date,
            invoice_date: invoice_date,
            cart_data: JSON.stringify(sessionStorage.getItem('cartData')) // Send cart as JSON
        },
        success: function(response) {
            console.log(response);
            var result = JSON.parse(response);
            Swal.fire({
                icon: result.status == 'success' ? 'success' : 'error',
                title: result.message,
                showConfirmButton: true,
                timer: 2000
            });

                sessionStorage.removeItem('cartData'); // Clear sessionStorage after success
                loadCart(related_id);
                $('#productSearch').val(null).trigger('change'); // Reset select2 or dropdown
                $('#rate').val(''); $('#qty').val('');  // Clear input field
           
        }
    });
});


function loadCart(related_id) {
    $.ajax({
        url: 'purchase_cart.php?id=' + related_id, // URL for your cart page
        type: 'GET',
        success: function(response) {
            $('#load_cart').html(response); // Update the cart container with new content
        }
    });
}

$(".js-example-tags").select2({
    tags: true
  });
