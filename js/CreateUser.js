const form = document.getElementById('myForm');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const formData = new FormData(form);

  fetch('includes/CreateUser.inc.php', {
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
          if (data.message != "User updated successfully.") {
            form.reset(); // Reset form only on success

          }
      if (data.status === "success") {

        $("#load_data").load("list_user.php", function(){ 
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


function PermissionCheckUncheck(userId, menuId,permission_type) {

       let isChecked = document.getElementById(permission_type + 'todoCheck' + menuId).checked ? 1 : 0;

  
  // Send AJAX request to update the permission
  fetch('includes/UpdateMenuPermission.inc.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `user_id=${userId}&menu_id=${menuId}&status=${isChecked}&permission_type=${permission_type}`
  })
  .then(response => response.json())
  .then(data => 

{
      Swal.fire({
            icon: data.status, // Show correct icon
            title: data.message,
            showConfirmButton: false,
            timer: 2000
          });
         
     
  })

  .catch(error => console.error('Error:', error));
}
