<?php
include 'includes/header.php';
include 'config/db.php';

// Check for clinic role
$isClinic = isset($_SESSION['role']) && $_SESSION['role'] === 'clinic';

// Fetch categories for filter
$categoriesQuery = "SELECT DISTINCT category FROM medications";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row['category'];
}

// Get selected category filter
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch medications with category filter
try {
    $sql = "SELECT * FROM medications WHERE quantity > 0";
    if (!empty($selectedCategory)) {
        $sql .= " AND category = '" . mysqli_real_escape_string($conn, $selectedCategory) . "'";
    }
    $sql .= " ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error loading medications: " . $e->getMessage() . "</div>";
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - Association Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Your other styles remain the same -->
</head>
<!-- Hero Section -->
<section class="hero-medications bg-primary text-white py-5 text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Explore Our Medications</h1>
        <p class="lead mb-0">Browse our extensive collection of quality medications</p>
    </div>
</section>

<!-- Medications Grid -->
<section id="medications" class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="mb-0">Available Medications</h2>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end align-items-center">
                    <select class="form-select w-auto" onchange="filterByCategory(this.value)">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if ($isClinic): ?>
            <!-- Order Summary for clinics -->
            <div id="orderSummary" class="card mb-4 d-none">
                <div class="card-header bg-primary text-white">
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

        <div class="row g-4" id="medicationsGrid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <!-- Image -->
                            <img src="<?= !empty($row['image']) ? 'dashboard/process/uploads/' . $row['image'] : 'assets/images/no-image.png' ?>"
                                class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>"
                                style="height: 200px; object-fit: cover;">

                            <div class="card-body d-flex flex-column">
                                <div>
                                    <!-- Title -->
                                    <h3 class="h5 card-title"><?= htmlspecialchars($row['title']) ?></h3>

                                    <!-- Category -->
                                    <span class="badge bg-primary mb-2"><?= htmlspecialchars($row['category']) ?></span>

                                    <!-- Description -->
                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...
                                    </p>
                                </div>

                                <div class="mt-auto">
                                    <!-- Stock Status -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Available:</span>
                                        <span class="fw-bold <?= $row['quantity'] < 5 ? 'text-warning' : 'text-success' ?>">
                                            <?= $row['quantity'] ?> units
                                        </span>
                                    </div>

                                    <?php if ($isClinic && $row['quantity'] > 0): ?>
                                        <!-- Order Controls (Only for clinic users) -->
                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select form-select-sm"
                                                onchange="updateOrder(<?= $row['id'] ?>, this.value, '<?= htmlspecialchars($row['title']) ?>')">
                                                <option value="0">Select Quantity</option>
                                                <?php
                                                $maxQty = min(3, $row['quantity']);
                                                for ($i = 1; $i <= $maxQty; $i++) {
                                                    echo "<option value=\"$i\">$i</option>";
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
                        No medications found in this category.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Scripts -->
<script>
    function filterByCategory(category) {
        const url = new URL(window.location);
        if (category) {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }
        window.location.href = url.toString();
    }

    <?php if ($isClinic): ?>
        let orderItems = {};

        function updateOrder(id, quantity, title) {
            quantity = parseInt(quantity);

            if (quantity > 0) {
                orderItems[id] = {
                    id: id,
                    quantity: quantity,
                    title: title,
                    type: 'medication'
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
                                <span class="badge bg-primary">${item.quantity} units</span>
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
                Swal.fire('Error', 'Please select at least one item', 'error');
                return;
            }
            console.log("hi")
            // Show loading state
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
                            text: 'Order submitted successfully!',
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

    .hero-medications {
        background: linear-gradient(45deg, #007bff, #0056b3);
    }

    .form-select {
        border-radius: 0.5rem;
        padding: 0.5rem 2.25rem 0.5rem 0.75rem;
        border-color: #dee2e6;
        cursor: pointer;
    }

    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, .25);
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.75em;
    }
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>