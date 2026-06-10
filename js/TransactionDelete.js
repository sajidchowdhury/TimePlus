$(document).ready(function () {
    $(document).on("click", ".delete-btn", function () {
        let related_id = $(this).data("item_id"); // Get invoice ID
        let row = $("#row-" + related_id); // Get the row by ID
        let PageName = $("#PageName").val(); // Get page name

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to recover this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            console.log("SweetAlert result object:", result); // Debugging

            if (result.value === true) {
                $.ajax({
                    url: "includes/TransactionDelete.inc.php",
                    type: "POST",
                    data: {
                        action: "delete_invoice",
                        related_id: related_id,
                        PageName: PageName
                    },
                    dataType: "json",
                    success: function (response) {
                        console.log("AJAX Success Response:", response); // Debugging

                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                            });

                            row.fadeOut(500, function () {
                                $(this).remove();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: response.message,
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", xhr, status, error); // Debugging

                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: `Something went wrong.\nStatus: ${status}\nError: ${error}\nResponse: ${xhr.responseText}`,
                        });
                    },
                });
            } else {
                console.log("User canceled deletion.");
            }
        });
    });
});
