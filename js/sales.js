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



     $("#productSearch").on('select2:select', function(e) {
        var product_id = e.params.data.id; // Get the selected product's ID
        var price = e.params.data.price; 
        document.getElementById('sales_rate').value = price; 
        // Call the FindStock function with the selected product_id
        FindStock(product_id);

        
        document.getElementById('qty').focus();
    });

   // Initialize calculation after cart is loaded
    setTimeout(function() {
        Calculate();
    }, 800);

    // Bind input listeners for real-time calculation
    $(document).on('input', '#discount_percentage, #adjustment, #receive_now', function() {
        Calculate();
    });

    // Also trigger when discount amount is manually edited (if you want bidirectional)
    $(document).on('input', '#actual_discount', function() {
        let subtotal = parseFloat($('#subtotal').val()) || 0;
        let discountAmount = parseFloat($(this).val()) || 0;
        
        if (subtotal > 0) {
            let percent = (discountAmount / subtotal) * 100;
            $('#discount_percentage').val(percent.toFixed(2));
        }
        Calculate();
    });

});





function FindStock(product_id) {





    $.ajax({

        url: 'includes/find_stock.inc.php',

        method: 'GET',

        data: { product_id: product_id },

     success: function(response) {

    try {

        const data = JSON.parse(response);

        console.log(data);



        const select = document.getElementById('product_stock_select');

        select.innerHTML = '<option value="">Select stock</option>';



        data.forEach(item => {

            const option = document.createElement('option');

            option.value = item.expiry_date;

            option.textContent = `Expiry: ${item.expiry_date} - Stock: ${item.stock}`;

            option.setAttribute('data-stock', item.stock);

            select.appendChild(option);

        });

    } catch (e) {

        console.error("JSON error:", e, "\nRaw response:", response);

    }

},

        error: function(xhr, status, error) {

            console.error('Error:', error);

        }

    });

}





$('#submit').click(function(e) {

    e.preventDefault();



    var product_id = $('#productSearch').val();

    var qty = $('#qty').val();

    var rate = $('#sales_rate').val();

    var product_stock = $('#product_stock').val();

    var related_id = $('#related_id').val();

    var invoice = $('#invoice').val();

    var customer_id = $('#customer_id').val();

    var user_id = $('#user_id').val();

    var prev_invoice_date = $('#prev_invoice_date').val();

    var invoice_date = $('#invoice_date').val();

    var discount = $('#discount_percentage').val();

    var sales_person = $('#sales_person').val();

    var cart_data = $('#cart_dataArray').val();

    var transaction_by_id = $('#transaction_by_id').val();

    var receive_now = $('#receive_now').val();



    var product_stock_select = $('#product_stock_select').val();

    var selected_stock = parseInt($('#product_stock_select option:selected').data('stock') || 0);



    $.ajax({

        url: 'includes/SalesSessionData.inc.php',

        type: 'POST',

        data: {

            action: 'add_to_cart',

            product_id: product_id,

            qty: qty,

            product_stock:product_stock,

            rate: rate,

            related_id: related_id,

            invoice: invoice,

            customer_id: customer_id,

            user_id: user_id,

            discount: discount,

            sales_person: sales_person,

            prev_invoice_date: prev_invoice_date,

            invoice_date: invoice_date,

            product_stock_select: product_stock_select,

            selected_stock: selected_stock,

            transaction_by_id: transaction_by_id,

            receive_now: receive_now





        },

        success: function(response) {

            var result = JSON.parse(response);

            console.log(response);



            Swal.fire({

                icon: result.status, // Show correct icon

                title: result.message,

                showConfirmButton: false,

                timer: 2000

              });



            if (result.status == 'success') {

                loadCart(related_id); // Reload the cart items (this function is for rendering the cart items dynamically)

            }

        }

    });

});



$(document).on('click', '.delete-item', function() {

    var item_id = $(this).data('item_id'); // Get the product ID from the data attribute

    var invoice_id = $('#related_id').val();





    $.ajax({

        url: 'includes/SalesSessionData.inc.php',

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

    var invoice           = $('#invoice').val();
    var customer_id       = $('#customer_id').val();
    var sales_person      = $('#sales_person').val();
    var countItem         = $('#countItem').val();
    var related_id        = $('#related_id').val();
    var prev_invoice_date = $('#prev_invoice_date').val();
    var invoice_date      = $('#invoice_date').val();
    var discount          = $('#discount_percentage').val();
    var adjustment        = $('#adjustment').val() || 0;           // ← NEW
    var transaction_by_id = $('#transaction_by_id').val();
    var receive_now       = $('#receive_now').val();

    // Validation
    if (parseInt(countItem) < 1) {
        Swal.fire({
            icon: 'error',
            title: 'No item added',
            text: 'Please add at least one item to the cart',
            showConfirmButton: false,
            timer: 2000
        });
        return false;
    }

    if (invoice === '' || customer_id === '' || sales_person === '') {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill Invoice, Customer and Sales Person',
            showConfirmButton: false,
            timer: 2000
        });
        return false;
    }

    $.ajax({
        url: 'includes/SalesSessionData.inc.php',
        type: 'POST',
        data: {
            action: 'submit_cart',
            invoice: invoice,
            customer_id: customer_id,
            sales_person: sales_person, 
            related_id: related_id,
            invoice_date: invoice_date,
            prev_invoice_date: prev_invoice_date,
            transaction_by_id: transaction_by_id,
            discount: discount,
            adjustment: adjustment,           // ← NEW - Very Important
            receive_now: receive_now
            // Removed unnecessary cart_data (we use session on backend)
        }
    })

    .done(function(response) {
        console.log('Server Response:', response);

        let result;
        try {
            result = JSON.parse(response);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Server Response',
                text: response,
                showConfirmButton: true
            });
            return;
        }

        Swal.fire({
            icon: result.status === 'success' ? 'success' : 'error',
            title: result.message,
            showConfirmButton: true,
            timer: result.status === 'success' ? 1500 : 3000
        });

        if (result.status === 'success') {
            // Clear cart
            if (related_id === 'New') {
                if (typeof $_SESSION !== 'undefined') { // safety
                    // Session will be cleared on backend
                }
            }

            loadCart(related_id);
            
            // Reset form fields
            $('#productSearch').val(null).trigger('change');
            $('#qty').val('');
            $('#sales_rate').val('');
            $('#adjustment').val(0);   // Reset adjustment after submit

            // Open invoice in new tab
            if (result.invoice_id || result.invoice_no) {
                let invoiceUrl = "invoice.php?invoice=" + encodeURIComponent(result.invoice_id || result.invoice_no);
                window.open(invoiceUrl, "_blank");
            }
        }
    })

    .fail(function(jqXHR, textStatus, errorThrown) {
        Swal.fire({
            icon: 'error',
            title: 'Request Failed',
            text: 'Please try again. Error: ' + textStatus,
            showConfirmButton: true
        });
        console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
    });
});


// ==================== CUSTOMER CHANGE - DEFAULT 18% ====================
$("#customer_id").on('change', function() {
    const selectedCustomer = $(this).val();
    
    if (selectedCustomer) {
        const currentDiscount = parseFloat($('#discount_percentage').val()) || 0;
        
        // Apply default 18% only if no discount is currently set or it's 0
        if (currentDiscount === 0) {
            $('#discount_percentage').val(18);
        }
        Calculate();
    }
});


// ==================== MAKE DUE ZERO ====================
function makeDueZero() {
    const invoicePrice = parseFloat(document.getElementById('invoicePrice').value) || 0;
    const receiveNow = parseFloat(document.getElementById('receive_now').value) || 0;
    const currentDue = invoicePrice - receiveNow;

    if (currentDue > 0) {
        document.getElementById('adjustment').value = currentDue.toFixed(2);
        Calculate();

        Swal.fire({
            icon: 'success',
            title: 'Adjustment Applied',
            text: 'Due has been adjusted to 0',
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'info',
            title: 'No adjustment needed',
            text: 'Due is already 0 or negative',
            timer: 1500
        });
    }
}


// ==================== CALCULATION ENGINE ====================
function Calculate() {
    const subtotalField = document.getElementById('subtotal');
    const discountPercentField = document.getElementById('discount_percentage');
    const actualDiscountField = document.getElementById('actual_discount');
    const adjustmentField = document.getElementById('adjustment');
    const invoicePriceField = document.getElementById('invoicePrice');
    const receiveNowField = document.getElementById('receive_now');
    const invoiceDueField = document.getElementById('invoice_Due');

    if (!subtotalField) return;

    let subtotal = parseFloat(subtotalField.value) || 0;
    let discountPercent = parseFloat(discountPercentField.value) || 0;
    let adjustment = parseFloat(adjustmentField.value) || 0;
    let receiveNow = parseFloat(receiveNowField.value) || 0;

    // Calculate Discount Amount from Percentage
    let discountAmount = (subtotal * discountPercent) / 100;

    // Final Invoice Price after discount and adjustment
    let invoicePrice = subtotal - discountAmount - adjustment;

    // Due Amount (never negative)
    let due = Math.max(0, invoicePrice - receiveNow);

    // Update all fields
    actualDiscountField.value = discountAmount.toFixed(2);
    invoicePriceField.value = invoicePrice.toFixed(2);
    invoiceDueField.value = due.toFixed(2);

    // Optional: Round negative adjustment to 0
    if (adjustment < 0) {
        adjustmentField.value = "0.00";
    }
}




function loadCart(related_id) {

    $.ajax({

        url: 'sales_cart.php?id=' + related_id, // URL for your cart page

        type: 'GET',

        success: function(response) {

            $('#load_cart').html(response); // Update the cart container with new content

        }

    });

}



$(".js-example-tags").select2({

    tags: true

  });