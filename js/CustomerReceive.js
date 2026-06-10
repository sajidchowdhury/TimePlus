const form = document.getElementById('myForm');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const formData = new FormData(form);

  fetch('includes/CustomerReceive.inc.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json()) // Parse JSON response
  .then(data => {
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: data.message,
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true
        }).then(() => {
          // Redirect only after the message disappears
          window.location.href = "customer_receive.php";
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: data.message,
          showConfirmButton: false,
          timer: 2000
        });
      }
  })
  .catch(error => {
      Swal.fire({
        icon: "error",
        title: "Something went wrong!",
        text: error.message,
        showConfirmButton: false,
        timer: 2000
      });
      console.error("Error:", error);
  });
});


function TransactionBYDetails(transaction_by){
    $.ajax({
        url: 'includes/find_TransactionByDetails.inc.php', // Path to PHP script
        method: 'GET',
        data: { transaction_by: transaction_by }, // Correct parameter name
        success: function(response) {
            console.log(response); // Debugging: Check response in console
            $('#load_transaction_by').html(response); // Update dropdown
               $('.select2').select2();
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });


}

