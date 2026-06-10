const form = document.getElementById('myForm');

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const formData = new FormData(form);

  fetch('includes/AddItem.inc.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())  // Convert to text first
  .then(data => {
      console.log("Raw Response:", data); // Debugging purpose
      return JSON.parse(data);  // Convert to JSON
  })
  .then(data => {
      Swal.fire({
          icon: data.status,
          title: data.message,
          showConfirmButton: false,
          timer: 2000
      });

      // Redirect after alert
      setTimeout(() => {
          window.location.href = 'add_item.php';
      }, 2000); // Match timer duration in Swal
  })
  .catch(error => {
      console.error("JSON Parse Error:", error);
      Swal.fire({
        icon: "error",
        title: "Something went wrong!",
        text: error.message,
        showConfirmButton: false,
        timer: 2000
      });
  });
});
