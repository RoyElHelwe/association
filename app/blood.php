<?php
include 'includes/header.php';
include 'config/db.php';

// Check for clinic role
$isClinic = isset($_SESSION['role']) && $_SESSION['role'] === 'clinic';

// Define blood types
$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// Get selected blood type filter
$selectedType = isset($_GET['type']) ? $_GET['type'] : '';

// Fetch blood with type filter and valid expiration date
try {
    $sql = "SELECT * FROM blood WHERE quantity > 0 AND expiration_date > CURDATE()";
    if (!empty($selectedType)) {
        $sql .= " AND blood_type = '" . mysqli_real_escape_string($conn, $selectedType) . "'";
    }
    $sql .= " ORDER BY expiration_date ASC";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error loading blood inventory: " . $e->getMessage() . "</div>";
}
?>

<!-- Hero Section -->
<section class="hero-blood bg-danger text-white py-5 text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Blood Bank</h1>
        <p class="lead mb-0">Access our blood bank inventory with various blood types available</p>
    </div>
</section>

<!-- Blood Inventory Section -->
<section id="blood" class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="mb-0">Available Blood Units</h2>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end align-items-center">
                    <select class="form-select w-auto" onchange="filterByType(this.value)">
                        <option value="">All Blood Types</option>
                        <?php foreach ($bloodTypes as $type): ?>
                            <option value="<?= $type ?>" <?= $selectedType === $type ? 'selected' : '' ?>>
                                <?= $type ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if ($isClinic): ?>
            <!-- Order Summary for clinics -->
            <div id="orderSummary" class="card mb-4 d-none">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div id="selectedItems"></div>
                    <button class="btn btn-success mt-3 w-100" onclick="submitOrder()">
                        <i class="fas fa-check-circle me-2"></i>Submit Order
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4" id="bloodGrid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <!-- Blood Type Header -->
                            <div class="card-header text-center bg-danger text-white py-3">
                                <h3 class="h2 mb-0"><?= htmlspecialchars($row['blood_type']) ?></h3>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <!-- Quantity and Expiration -->
                                <div class="text-center mb-4">
                                    <h4 class="h5">Available Units</h4>
                                    <span class="display-6 fw-bold text-danger"><?= $row['quantity'] ?></span>
                                </div>

                                <div class="mt-auto">
                                    <!-- Expiration Date -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Expires:</span>
                                        <span class="fw-bold">
                                            <?= date('M d, Y', strtotime($row['expiration_date'])) ?>
                                        </span>
                                    </div>

                                    <?php if ($isClinic && $row['quantity'] > 0): ?>
                                        <!-- Order Controls (Only for clinic users) -->
                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select form-select-sm"
                                                onchange="updateOrder(<?= $row['id'] ?>, this.value, '<?= $row['blood_type'] ?>')">
                                                <option value="0">Select Quantity</option>
                                                <?php
                                                $maxQty = min(3, $row['quantity']);
                                                for ($i = 1; $i <= $maxQty; $i++) {
                                                    echo "<option value=\"$i\">$i units</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No blood units available for the selected type.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Head section should include SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Scripts -->
<script>
    function filterByType(type) {
        const url = new URL(window.location);
        if (type) {
            url.searchParams.set('type', type);
        } else {
            url.searchParams.delete('type');
        }
        window.location.href = url.toString();
    }

    <?php if ($isClinic): ?>
        let orderItems = {};

        function updateOrder(id, quantity, bloodType) {
            quantity = parseInt(quantity);

            if (quantity > 0) {
                orderItems[id] = {
                    id: id,
                    quantity: quantity,
                    title: `Blood Type ${bloodType}`,
                    type: 'blood'
                };
            } else {
                delete orderItems[id];
            }

            updateOrderSummary();
        }

        function updateOrderSummary() {
            const summary = document.getElementById('orderSummary');
            const items = document.getElementById('selectedItems');

            if (Object.keys(orderItems).length > 0) {
                summary.classList.remove('d-none');

                items.innerHTML = Object.values(orderItems).map(item => `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                    <span>${item.title}</span>
                    <span class="badge bg-danger">${item.quantity} units</span>
                </div>
            `).join('');
            } else {
                summary.classList.add('d-none');
            }
        }

        function resetSelections() {
            document.querySelectorAll('select[onchange^="updateOrder"]').forEach(select => {
                select.value = "0";
            });

            orderItems = {};
            updateOrderSummary();
        }

        function submitOrder() {
            if (Object.keys(orderItems).length === 0) {
                Swal.fire('Error', 'Please select at least one blood unit', 'error');
                return;
            }

            Swal.fire({
                title: 'Processing Order',
                html: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('process/submit_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ items: Object.values(orderItems) })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Blood units ordered successfully!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            resetSelections();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to submit order', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to submit order', 'error');
                });
        }
    <?php endif; ?>
</script>

<!-- Custom Styles -->
<style>
    .hover-shadow {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .card {
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .hero-blood {
        background: linear-gradient(45deg, #dc3545, #b02a37);
    }

    .form-select {
        border-radius: 0.5rem;
        padding: 0.5rem 2.25rem 0.5rem 0.75rem;
        border-color: #dee2e6;
        cursor: pointer;
    }

    .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, .25);
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
    }

    .display-6 {
        font-size: 2.5rem;
    }
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>