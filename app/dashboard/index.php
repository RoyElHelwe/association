<?php
include 'includes/header.php';
include 'secure/auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-card {
            transition: transform 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>

    <div class="container-fluid mt-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
            <div class="date-filter">
                <select class="form-select" id="timeRange">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-center text-primary">
                            <i class="fas fa-pills card-icon"></i>
                            <h5>Medications</h5>
                            <h2 id="medications-count">0</h2>
                            <p class="mb-0"><span id="medications-trend"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-center text-success">
                            <i class="fas fa-shopping-cart card-icon"></i>
                            <h5>Orders</h5>
                            <h2 id="orders-count">0</h2>
                            <p class="mb-0"><span id="orders-trend"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-center text-info">
                            <i class="fas fa-cogs card-icon"></i>
                            <h5>Machines</h5>
                            <h2 id="machines-count">0</h2>
                            <p class="mb-0"><span id="machines-trend"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-center text-danger">
                            <i class="fas fa-tint card-icon"></i>
                            <h5>Blood Units</h5>
                            <h2 id="blood-count">0</h2>
                            <p class="mb-0"><span id="blood-trend"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <!-- Order Status Distribution -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Order Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blood Type Inventory -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Blood Type Inventory</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="bloodInventoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Orders Trend -->
            <div class="col-xl-12 mb-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold">Monthly Orders Trend</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="ordersTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Chart.js default configurations
        Chart.defaults.font.family = "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#666';

        // Store chart instances globally
        let charts = {};

        // Main function to fetch and display dashboard data
        async function fetchDashboardData() {
            try {
                // Show loading state
                showLoadingState(true);

                // Fetch and debug each API response
                const apis = [
                    { name: 'blood', url: 'api/blood_orders.php' },
                    { name: 'medications', url: 'api/medications_order.php' },
                    { name: 'machines', url: 'api/machines_order.php' },
                    { name: 'orders', url: 'api/orders.php' }
                ];

                const responses = {};

                for (const api of apis) {
                    try {
                        const response = await fetch(api.url);
                        const text = await response.text(); // Get response as text first

                        try {
                            responses[api.name] = JSON.parse(text);
                        } catch (parseError) {
                            console.error(`Error parsing ${api.name} response:`, text);
                            throw new Error(`Invalid JSON from ${api.name} API`);
                        }
                    } catch (error) {
                        console.error(`Error fetching ${api.name}:`, error);
                        throw error;
                    }
                }

                // Update dashboard with the data
                updateDashboardContent(
                    responses.blood,
                    responses.medications,
                    responses.machines,
                    responses.orders
                );

            } catch (error) {
                console.error('Dashboard data fetch error:', error);
                if (typeof Swal === 'undefined') {
                    alert('Error loading dashboard data: ' + error.message);
                } else {
                    showError('Failed to load dashboard data: ' + error.message);
                }
            } finally {
                showLoadingState(false);
            }
        }
        // Update all dashboard content
        function updateDashboardContent(bloodData, medicationsData, machinesData, ordersData) {
            // Update summary cards
            updateSummaryCards(bloodData, medicationsData, machinesData, ordersData);

            // Update charts
            updateOrderStatusChart(ordersData);
            updateBloodInventoryChart(bloodData.data);
            updateOrdersTrendChart(ordersData);
        }

        // Loading state management
        function showLoadingState(isLoading) {
            const loadingElements = document.querySelectorAll('.loading-placeholder');
            loadingElements.forEach(element => {
                element.style.display = isLoading ? 'block' : 'none';
            });
        }

        // Error display
        function showError(message) {
            if (typeof Swal === 'undefined') {
                alert(message);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
        // Update summary cards
        function updateSummaryCards(bloodData, medicationsData, machinesData, ordersData) {
            // Update counts
            document.getElementById('medications-count').textContent = medicationsData?.data?.length || 0;
            document.getElementById('orders-count').textContent = ordersData?.length || 0;
            document.getElementById('machines-count').textContent = machinesData?.data?.length || 0;
            document.getElementById('blood-count').textContent = bloodData?.data?.length || 0;
        }

        // Order Status Chart
        function updateOrderStatusChart(ordersData) {
            if (!ordersData || !Array.isArray(ordersData)) return;

            const statusCounts = {
                pending: 0,
                accepted: 0,
                declined: 0,
                modified: 0
            };

            ordersData.forEach(order => {
                if (order.status in statusCounts) {
                    statusCounts[order.status]++;
                }
            });

            const ctx = document.getElementById('orderStatusChart');
            if (!ctx) return;

            if (charts.orderStatus) {
                charts.orderStatus.destroy();
            }

            charts.orderStatus = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(statusCounts).map(status =>
                        status.charAt(0).toUpperCase() + status.slice(1)
                    ),
                    datasets: [{
                        data: Object.values(statusCounts),
                        backgroundColor: [
                            '#ffc107', // Pending
                            '#28a745', // Accepted
                            '#dc3545', // Declined
                            '#17a2b8'  // Modified
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Order Status Distribution'
                        }
                    }
                }
            });
        }

        // Blood Inventory Chart
        function updateBloodInventoryChart(bloodData) {
            if (!bloodData || !Array.isArray(bloodData)) return;

            const bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            const quantities = bloodTypes.map(type => {
                return bloodData
                    .filter(item => item.blood_type === type)
                    .reduce((sum, item) => sum + parseInt(item.blood_stock_quantity || 0), 0);
            });

            const ctx = document.getElementById('bloodInventoryChart');
            if (!ctx) return;

            if (charts.bloodInventory) {
                charts.bloodInventory.destroy();
            }

            charts.bloodInventory = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: bloodTypes,
                    datasets: [{
                        label: 'Units Available',
                        data: quantities,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Blood Type Distribution'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Units'
                            }
                        }
                    }
                }
            });
        }

        // Orders Trend Chart
        function updateOrdersTrendChart(ordersData) {
            if (!ordersData || !Array.isArray(ordersData)) return;

            const months = getLastSixMonths();
            const monthlyOrders = months.map(month => {
                return ordersData.filter(order => {
                    const orderDate = new Date(order.created_at);
                    return orderDate.getMonth() === month.getMonth() &&
                        orderDate.getFullYear() === month.getFullYear();
                }).length;
            });

            const ctx = document.getElementById('ordersTrendChart');
            if (!ctx) return;

            if (charts.ordersTrend) {
                charts.ordersTrend.destroy();
            }

            charts.ordersTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months.map(date => date.toLocaleString('default', { month: 'short' })),
                    datasets: [{
                        label: 'Orders',
                        data: monthlyOrders,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Monthly Orders Trend'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Orders'
                            }
                        }
                    }
                }
            });
        }

        // Utility function to get last six months
        function getLastSixMonths() {
            const months = [];
            const today = new Date();
            for (let i = 5; i >= 0; i--) {
                const date = new Date();
                date.setMonth(today.getMonth() - i);
                months.push(date);
            }
            return months;
        }

        // Handle time range changes
        document.getElementById('timeRange')?.addEventListener('change', function (e) {
            fetchDashboardData();
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function () {
            fetchDashboardData();
            // Refresh data every 5 minutes
            setInterval(fetchDashboardData, 5 * 60 * 1000);
        });
    </script>

</body>

</html>