const form = document.getElementById('myForm');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const formData = new FormData(form);

  fetch('includes/voucher.inc.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json()) // Parse JSON response
  .then(data => {
      Swal.fire({
        icon: data.status, // Show correct icon (success/error)
        title: data.message,
        showConfirmButton: false,
        timer: 2000
      });

      // Optionally clear the form after successful submission
      if (data.status === 'success') {
        form.reset(); // Reset form fields
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




function LedgerWiseAc(ledger_id) {
    $.ajax({
        url: 'includes/find_LedgerWiseAc.inc.php', // Path to PHP script
        method: 'GET',
        data: { ledger_id: ledger_id }, // Correct parameter name
        success: function(response) {
            console.log(response); // Debugging: Check response in console
            $('#load_account_head').html(response); // Update dropdown
               $('.select2').select2();
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
}


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
