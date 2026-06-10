const form = document.getElementById('myForm');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const formData = new FormData(form);

  fetch('includes/AccountSetup.inc.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json()) // Parse JSON response
  .then(data => {
      Swal.fire({
            icon: data.status, // Show correct icon
            title: data.message,
            showConfirmButton: false,
            timer: 2000
          });
          if (data.message != "updated successfully.") {
            form.reset(); // Reset form only on success

          }
      if (data.status === "success") {

        $("#load_data").load("list_account.php", function(){ 
          $("#example1").DataTable({
            "responsive": true,
            "autoWidth": false,
          });
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
  });
});

