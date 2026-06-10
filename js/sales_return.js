function SalesReturn() {
    const rows = document.querySelectorAll('.return-now');
    const items = [];
    let error = false;

    // Collect additional form data
    const sales_id = $('#sales_id').val();
    const customer_id = $('#customer_id').val();
    const invoice_date = $('#invoice_date').val();

    rows.forEach((input, index) => {
        const return_now = parseFloat(input.value) || 0;
        const product_id = input.dataset.productId;
        const remaining_qty = parseFloat(input.dataset.remaining_qty);
        const previous = parseFloat(input.dataset.previous);
        const price = parseFloat(input.dataset.price);
        const note = document.querySelectorAll('.note')[index].value;
        const expiry_date = input.dataset.expiry_date;
        const product_name = input.dataset.product_name;

        if (return_now > remaining_qty) {
            Swal.fire({
                icon: 'warning',
                title: `Return quantity cannot exceed ${remaining_qty}`,
                text: `Product: ${product_name}`,
                timer: 3000,
                showConfirmButton: false
            });
            error = true;
            return;
        }

        if (return_now > 0) {
            items.push({
                product_id,
                return_now,
                note,
                price,
                expiry_date
            });
        }
    });

    if (error) return;

    if (items.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No items to return',
            text: 'Please enter at least one return quantity.',
            timer: 2500,
            showConfirmButton: false
        });
        return;
    }

    // Send all data
    fetch('includes/SalesReturn.inc.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            sales_id,
            customer_id,
            invoice_date,
            items
        })
    })
    .then(res => res.json())
    .then(data => {
        Swal.fire({
            icon: data.status,
            title: data.message,
            showConfirmButton: false,
            timer: 2000
        });

        if (data.status === 'success') {
            // Update remaining_qty for each returned item
            items.forEach(item => {
                const selector = `.return-now[data-product-id="${item.product_id}"][data-expiry_date="${item.expiry_date}"]`;
                const input = document.querySelector(selector);

                if (input) {
                    const oldRemaining = parseFloat(input.dataset.remaining_qty) || 0;
                    const newRemaining = oldRemaining - parseFloat(item.return_now);
                    const newPreReturn =parseFloat(input.dataset.previous) + parseFloat(item.return_now);

                    // Update data attribute
                    input.dataset.remaining_qty = newRemaining;
                    input.dataset.previous = newPreReturn;

                    input.value = 0;
                    // Reset input value
                    input.value = 0;
                    
                    // Disable input if nothing left
                    if (newRemaining <= 0) {
                        input.disabled = true;
                    }

                    // Update remaining quantity in UI
                    const remainingDisplay = input.closest('tr').querySelector('.remaining-display');
                    if (remainingDisplay) {
                        remainingDisplay.textContent = newRemaining;
                    }

                   const PreviousDisplay = input.closest('tr').querySelector('.previous-display');
                    if (PreviousDisplay) {
                        PreviousDisplay.textContent = newPreReturn;
                    }


                }
            });

            // Optional: clear notes if needed
            document.querySelectorAll('.note').forEach(note => note.value = 'N/A');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'An error occurred!',
            text: 'Please try again.',
            showConfirmButton: true
        });
    });
}


function DeleteInvoice(item_id) {
    var delete_type = $('#delete_type').val();

    $.ajax({
        url: 'includes/SalesReturnData.inc.php',
        type: 'POST',
        data: {
            action: 'delete_from_cart',
            item_id: item_id,
            delete_type: delete_type
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

                                document.getElementById('searchReport').click();

            }
        }
    });
}


