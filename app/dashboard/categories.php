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
    <title>Category Management</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Category Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
        </div>

        <!-- Add/Edit Category Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="categoryForm">
                    <h4 class="card-title mb-4" id="formTitle">Add New Category</h4>
                    <input type="hidden" id="categoryId" name="id">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="categoryName" name="name" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitButton">Add Category</button>
                                <button type="button" class="btn btn-secondary" id="cancelEdit"
                                    style="display:none">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories List -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Existing Categories</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesList">
                            <!-- Categories will be populated here -->
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

        function loadCategories() {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
            }

            $.ajax({
                url: 'process/category_crud.php',
                method: 'GET',
                success: function (response) {
                    let categories = response;
                    try {
                        if (typeof response === 'string') {
                            categories = JSON.parse(response);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        return;
                    }

                    let rows = '';
                    if (!Array.isArray(categories) || categories.length === 0) {
                        rows = '<tr><td colspan="4" class="text-center">No categories found</td></tr>';
                    } else {
                        categories.forEach(function (category) {
                            rows += `
        <tr>
            <td>${category.id}</td>
            <td>${category.name}</td>
            <td>${new Date(category.created_at).toLocaleString()}</td>
            <td>
                <button class="btn btn-warning btn-sm" 
                        onclick="editCategory('${category.id}', '${category.name}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" 
                        onclick="deleteCategory(${category.id})"
                        ${category.usage_count > 0 ? 'disabled' : ''}>
                    <i class="fas fa-trash"></i> Delete
                </button>
                <small class="text-muted ms-2">
                    ${category.usage_count > 0 ? `(Used in ${category.usage_count} items)` : ''}
                </small>
            </td>
        </tr>
    `;
                        });
                    }
                    $('#categoriesList').html(rows);
                },
                error: function (xhr, status, error) {
                    console.error('Error loading categories:', error);
                    showAlert('error', 'Failed to load categories');
                }
            });
        }

        function editCategory(id, name) {
            isEditing = true;

            $('#categoryId').val(id);
            $('#categoryName').val(name);

            $('#formTitle').text('Edit Category');
            $('#submitButton').text('Update Category');
            $('#cancelEdit').show();

            $('#categoryForm')[0].scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            isEditing = false;
            $('#categoryForm')[0].reset();
            $('#categoryId').val('');
            $('#formTitle').text('Add New Category');
            $('#submitButton').text('Add Category');
            $('#cancelEdit').hide();
        }

        function deleteCategory(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the category. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `process/category_crud.php?action=delete&id=${id}`,
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

                            showAlert(res.status, res.message);
                            if (res.status === 'success') {
                                currentTimeout = setTimeout(loadCategories, 100);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error deleting category:', error);
                            showAlert('error', 'Failed to delete category');
                        }
                    });
                }
            });
        }
        // Event Handlers
        function handleFormSubmit(e) {
            e.preventDefault();

            let formData = new FormData(e.target);
            let url = `process/category_crud.php?action=${isEditing ? 'edit' : 'add'}`;

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
                        currentTimeout = setTimeout(loadCategories, 100);
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
            $('#categoryForm').off('submit').on('submit', handleFormSubmit);
            $('#cancelEdit').off('click').on('click', resetForm);

            // Load initial data
            loadCategories();
        });
    </script>

</body>

</html>