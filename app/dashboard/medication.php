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
    <title>Medication Management</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .preview-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }

        .table-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Medication Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Medications</li>
                </ol>
            </nav>
        </div>

        <!-- Add/Edit Medication Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="medicationForm">
                    <h4 class="card-title mb-4" id="formTitle">Add New Medication</h4>
                    <input type="hidden" id="medicationId" name="id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="medicationTitle" class="form-label">Medication Title</label>
                                <input type="text" class="form-control" id="medicationTitle" name="title" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="medicationCategory" class="form-label">Category</label>
                                <select class="form-control" id="medicationCategory" name="category" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="medicationQuantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="medicationQuantity" name="quantity"
                                    min="0" value="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fileToUpload" class="form-label">Medication Image</label>
                                <input type="file" class="form-control" id="fileToUpload" name="fileToUpload"
                                    accept="image/*">
                                <div id="imagePreview" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="medicationDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="medicationDescription" name="description" rows="3"
                                    required></textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitButton">Add Medication</button>
                                <button type="button" class="btn btn-secondary" id="cancelEdit"
                                    style="display:none">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Medications List -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Existing Medications</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="medicationsList">
                            <!-- Medications will be populated here -->
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

        function loadMedications() {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
            }

            $.ajax({
                url: 'process/medication_crud.php',
                method: 'GET',
                success: function (response) {
                    let medications = response;
                    try {
                        if (typeof response === 'string') {
                            medications = JSON.parse(response);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        return;
                    }

                    let rows = '';
                    if (!Array.isArray(medications) || medications.length === 0) {
                        rows = '<tr><td colspan="8" class="text-center">No medications found</td></tr>';
                    } else {
                        medications.forEach(function (medication) {
                            const imageUrl = medication.image ?
                                `process/uploads/${medication.image}` :
                                'assets/images/no-image.png';
                            const description = medication.description ?
                                medication.description.substring(0, 50) +
                                (medication.description.length > 50 ? '...' : '') : '';

                            const medicationData = encodeURIComponent(JSON.stringify(medication));

                            rows += `
                        <tr>
                            <td>${medication.id}</td>
                            <td>
                                <img src="${imageUrl}" alt="${medication.title}" 
                                     class="table-image"
                                     onerror="this.src='assets/images/no-image.png'">
                            </td>
                            <td>${medication.title}</td>
                            <td>${description}</td>
                            <td>${medication.category}</td>
                            <td>${medication.quantity}</td>
                            <td>${new Date(medication.created_at).toLocaleString()}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" 
                                        onclick="editMedication('${medicationData}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" 
                                        onclick="deleteMedication(${medication.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                        });
                    }
                    $('#medicationsList').html(rows);
                },
                error: function (xhr, status, error) {
                    console.error('Error loading medications:', error);
                    showAlert('error', 'Failed to load medications');
                }
            });
        }

        function loadCategories() {
            $.ajax({
                url: 'api/category_api.php',
                method: 'GET',
                success: function (response) {
                    let categories = response;
                    try {
                        if (typeof response === 'string') {
                            categories = JSON.parse(response);
                        }
                    } catch (e) {
                        console.error('Error parsing categories:', e);
                        return;
                    }

                    let options = '<option value="">Select Category</option>';
                    if (Array.isArray(categories)) {
                        categories.forEach(function (category) {
                            options += `<option value="${category.name}">${category.name}</option>`;
                        });
                    }
                    $('#medicationCategory').html(options);
                },
                error: function (xhr, status, error) {
                    console.error('Error loading categories:', error);
                }
            });
        }

        function editMedication(medicationData) {
            try {
                const medication = JSON.parse(decodeURIComponent(medicationData));

                isEditing = true;

                $('#medicationId').val(medication.id);
                $('#medicationTitle').val(medication.title);
                $('#medicationDescription').val(medication.description);
                $('#medicationCategory').val(medication.category);
                $('#medicationQuantity').val(medication.quantity);

                if (medication.image) {
                    $('#imagePreview').html(`
                <img src="process/uploads/${medication.image}" 
                     alt="Current Image" 
                     class="preview-image">
                <p class="text-muted mt-2">Current image shown. Upload new image to change.</p>
            `);
                } else {
                    $('#imagePreview').empty();
                }

                $('#formTitle').text('Edit Medication');
                $('#submitButton').text('Update Medication');
                $('#cancelEdit').show();

                $('#medicationForm')[0].scrollIntoView({ behavior: 'smooth' });
            } catch (e) {
                console.error('Error parsing medication data:', e);
                showAlert('error', 'Failed to load medication details');
            }
        }

        function resetForm() {
            isEditing = false;
            $('#medicationForm')[0].reset();
            $('#medicationId').val('');
            $('#imagePreview').empty();
            $('#formTitle').text('Add New Medication');
            $('#submitButton').text('Add Medication');
            $('#cancelEdit').hide();
        }

        function deleteMedication(id) {
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
                        url: `process/medication_crud.php?action=delete&id=${id}`,
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

                            showAlert(res.status || 'success', res.message || 'Medication deleted successfully');
                            if (res.status === 'success') {
                                currentTimeout = setTimeout(loadMedications, 100);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error deleting medication:', error);
                            showAlert('error', 'Failed to delete medication');
                        }
                    });
                }
            });
        }

        // Event Handlers
        function handleFormSubmit(e) {
            e.preventDefault();

            let formData = new FormData(e.target);
            let url = `process/medication_crud.php?action=${isEditing ? 'edit' : 'add'}`;

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
                        currentTimeout = setTimeout(loadMedications, 100);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error submitting form:', error);
                    showAlert('error', 'An error occurred while processing your request');
                }
            });
        }

        function handleImagePreview(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#imagePreview').html(`
                <img src="${e.target.result}" 
                     alt="Preview" 
                     class="preview-image">
            `);
                };
                reader.readAsDataURL(file);
            }
        }

        // Initialize
        $(document).ready(function () {
            // Remove any existing event handlers
            $('#medicationForm').off('submit').on('submit', handleFormSubmit);
            $('#cancelEdit').off('click').on('click', resetForm);
            $('#fileToUpload').off('change').on('change', handleImagePreview);

            // Load initial data
            loadMedications();
            loadCategories();
        });
    </script>

</body>

</html>