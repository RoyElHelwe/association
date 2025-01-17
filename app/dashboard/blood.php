<?php
// Include the header and authentication
include 'includes/header.php';
include 'secure/auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Management</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Blood Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Blood Records</li>
                </ol>
            </nav>
        </div>

        <!-- Add/Edit Blood Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="bloodForm">
                    <h4 class="card-title mb-4" id="formTitle">Add New Blood Record</h4>
                    <input type="hidden" id="bloodId" name="id">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bloodType" class="form-label">Blood Type</label>
                                <select class="form-control" id="bloodType" name="blood_type" required>
                                    <option value="">Select Blood Type</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity (Units)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0"
                                    value="0" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expirationDate" class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" id="expirationDate" name="expiration_date"
                                    required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitButton">Add Blood
                                    Record</button>
                                <button type="button" class="btn btn-secondary" id="cancelEdit"
                                    style="display:none">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Blood Records List -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Existing Blood Records</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Blood Type</th>
                                <th>Quantity</th>
                                <th>Expiration Date</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bloodList">
                            <!-- Blood records will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        let isEditing = false;
        let currentTimeout = null;

        function showAlert(type, message) {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Success!' : 'Error!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        function loadBlood() {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
            }

            $.ajax({
                url: 'process/blood_crud.php',
                method: 'GET',
                success: function (response) {
                    let blood_records = response;
                    try {
                        if (typeof response === 'string') {
                            blood_records = JSON.parse(response);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        return;
                    }

                    let rows = '';
                    if (!Array.isArray(blood_records) || blood_records.length === 0) {
                        rows = '<tr><td colspan="6" class="text-center">No blood records found</td></tr>';
                    } else {
                        blood_records.forEach(function (record) {
                            const recordData = encodeURIComponent(JSON.stringify(record));
                            rows += `
                        <tr>
                            <td>${record.id}</td>
                            <td>${record.blood_type}</td>
                            <td>${record.quantity}</td>
                            <td>${record.expiration_date}</td>
                            <td>${new Date(record.created_at).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" 
                                        onclick="editBlood('${recordData}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" 
                                        onclick="deleteBlood(${record.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                        });
                    }
                    $('#bloodList').html(rows);
                },
                error: function (xhr, status, error) {
                    console.error('Error loading blood records:', error);
                    showAlert('error', 'Failed to load blood records');
                }
            });
        }

        function editBlood(recordData) {
            try {
                const record = JSON.parse(decodeURIComponent(recordData));

                isEditing = true;

                $('#bloodId').val(record.id);
                $('#bloodType').val(record.blood_type);
                $('#quantity').val(record.quantity);
                $('#expirationDate').val(record.expiration_date);

                $('#formTitle').text('Edit Blood Record');
                $('#submitButton').text('Update Blood Record');
                $('#cancelEdit').show();

                $('#bloodForm')[0].scrollIntoView({ behavior: 'smooth' });
            } catch (e) {
                console.error('Error parsing blood record data:', e);
                showAlert('error', 'Failed to load blood record details');
            }
        }

        function resetForm() {
            isEditing = false;
            $('#bloodForm')[0].reset();
            $('#bloodId').val('');
            $('#formTitle').text('Add New Blood Record');
            $('#submitButton').text('Add Blood Record');
            $('#cancelEdit').hide();
        }

        function deleteBlood(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `process/blood_crud.php?action=delete&id=${id}`,
                        method: 'GET',
                        success: function (response) {
                            let res = response;
                            try {
                                if (typeof response === 'string') {
                                    res = JSON.parse(response);
                                }
                            } catch (e) {
                                console.error('Error parsing delete response:', e);
                                return;
                            }

                            showAlert(res.status || 'success', res.message || 'Blood record deleted successfully');
                            if (res.status === 'success') {
                                currentTimeout = setTimeout(loadBlood, 100);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error deleting blood record:', error);
                            showAlert('error', 'Failed to delete blood record');
                        }
                    });
                }
            });
        }

        // Event Handlers
        function handleFormSubmit(e) {
            e.preventDefault();

            let formData = new FormData(e.target);
            let url = `process/blood_crud.php?action=${isEditing ? 'edit' : 'add'}`;

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    let res = response;
                    try {
                        if (typeof response === 'string') {
                            res = JSON.parse(response);
                        }
                    } catch (e) {
                        console.error('Error parsing form submission response:', e);
                        return;
                    }

                    showAlert(res.status, res.message);
                    if (res.status === 'success') {
                        resetForm();
                        currentTimeout = setTimeout(loadBlood, 100);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error submitting form:', error);
                    showAlert('error', 'An error occurred while processing your request');
                }
            });
        }

        // Initialize
        $(document).ready(function () {
            // Remove any existing event handlers
            $('#bloodForm').off('submit').on('submit', handleFormSubmit);
            $('#cancelEdit').off('click').on('click', resetForm);

            // Set min date for expiration date to today
            const today = new Date().toISOString().split('T')[0];
            $('#expirationDate').attr('min', today);

            // Load initial data
            loadBlood();
        });
    </script>

</body>

</html>