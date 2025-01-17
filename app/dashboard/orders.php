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
    <title>Order Management</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Order Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Orders</li>
                </ol>
            </nav>
        </div>

        <!-- Orders List -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Order Records</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Clinic</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody id="ordersList">
                            <!-- Orders will be populated here -->
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

        function loadOrders() {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
            }

            $.ajax({
                url: 'process/orders_crud.php',
                method: 'GET',
                success: function (response) {
                    let orders = response;
                    console.log('Raw response:', orders);

                    try {
                        if (typeof response === 'string') {
                            orders = JSON.parse(response);
                        }

                        let rows = '';
                        if (!Array.isArray(orders) || orders.length === 0) {
                            rows = '<tr><td colspan="5" class="text-center">No orders found</td></tr>';
                        } else {
                            orders.forEach(function (order) {
                                const statusClass = {
                                    'pending': 'bg-warning text-dark',
                                    'accepted': 'bg-success text-white',
                                    'declined': 'bg-danger text-white',
                                }[order.status] || '';

                                // Parse items if it's a string
                                let items = order.items;
                                if (typeof items === 'string') {
                                    try {
                                        items = JSON.parse(items);
                                    } catch (e) {
                                        console.error('Error parsing items JSON:', e);
                                        items = [];
                                    }
                                }

                                rows += `
                            <tr>
                                <td>${order.id}</td>
                                <td>${order.clinic_id || 'N/A'}</td>
                                <td>
                                    <select class="form-select form-select-sm ${statusClass}" 
                                            onchange="updateStatus(${order.id}, this.value)">
                                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="accepted" ${order.status === 'accepted' ? 'selected' : ''}>Accepted</option>
                                        <option value="declined" ${order.status === 'declined' ? 'selected' : ''}>Declined</option>
                                    </select>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        ${Array.isArray(items) ? items.map(item => `
                                            <li class="mb-2">
                                                <strong>${item.type}:</strong> ${item.quantity} units
                                            </li>
                                        `).join('') : 'No items'}
                                    </ul>
                                </td>
                                <td>${new Date(order.created_at).toLocaleString()}</td>
                            </tr>
                        `;
                            });
                        }
                        $('#ordersList').html(rows);
                    } catch (e) {
                        console.error('Error processing orders:', e);
                        showAlert('error', 'Error processing orders data');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading orders:', error);
                    showAlert('error', 'Failed to load orders');
                }
            });
        }
        function updateStatus(orderId, newStatus) {
            Swal.fire({
                title: 'Update Order Status',
                text: `Are you sure you want to change the order status to ${newStatus}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'process/orders_crud.php',
                        method: 'POST',
                        data: {
                            action: 'edit',
                            id: orderId,
                            status: newStatus
                        },
                        success: function (response) {
                            let res = response;
                            try {
                                if (typeof response === 'string') {
                                    res = JSON.parse(response);
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                return;
                            }

                            showAlert(res.status, res.message);
                            if (res.status === 'success') {
                                currentTimeout = setTimeout(loadOrders, 100);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error updating order status:', error);
                            showAlert('error', 'Failed to update order status');
                        }
                    });
                }
            });
        }

        // Initialize
        $(document).ready(function () {
            loadOrders();
        });
    </script>

</body>

</html>