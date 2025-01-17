<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Association Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .navbar {
            padding: 1rem;
            background-color: white !important;
        }

        .navbar-brand {
            font-weight: bold;
            color: #0d6efd !important;
        }

        .nav-link {
            color: #333 !important;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #0d6efd !important;
        }

        .nav-link.active {
            color: #0d6efd !important;
            font-weight: 500;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: 50%;
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                margin-top: 1rem;
            }

            .nav-item {
                padding: 0.5rem 0;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Association</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Default Navigation -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-2"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-2"></i>Contact
                        </a>
                    </li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'clinic'): ?>
                        <!-- Clinic Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="medications.php">
                                <i class="fas fa-pills me-2"></i>Medications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="blood.php">
                                <i class="fas fa-tint me-2"></i>Blood
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="machines.php">
                                <i class="fas fa-cogs me-2"></i>Machines
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <?php if (isset($_SESSION['role'])): ?>
                    <!-- User is logged in -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications Dropdown -->
                        <div class="dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    id="notificationCount" style="display: none;">
                                    0
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown"
                                style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div id="notificationList">
                                    <!-- Notifications will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle fa-lg"></i>
                                <span class="d-none d-lg-inline"><?= $_SESSION['name'] ?? 'User' ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="dashboard/index.php">
                                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="process/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set active nav item
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>

    <script>
        let lastNotificationCount = 0;

        function loadNotifications() {
            $.ajax({
                url: 'process/get_notifications.php',
                method: 'GET',
                success: function (response) {
                    console.log('Notification response:', response); // Debug line

                    if (response.notifications && response.status === 'success') {
                        const unreadCount = response.notifications.filter(n => n.status === 'unread').length;

                        // Update notification badge
                        const countElement = $('#notificationCount');
                        countElement.text(unreadCount);
                        countElement.toggle(unreadCount > 0);

                        // Update notification list
                        const listElement = $('#notificationList');
                        if (response.notifications.length > 0) {
                            const notificationList = response.notifications.map(notification => `
                        <div class="dropdown-item ${notification.status === 'unread' ? 'bg-light' : ''}" 
                             onclick="markNotificationRead(${notification.id})">
                            <small class="text-muted d-block">
                                ${new Date(notification.created_at).toLocaleString()}
                            </small>
                            <div class="mt-1">${notification.message}</div>
                        </div>
                    `).join('<div class="dropdown-divider"></div>');
                            listElement.html(notificationList);
                        } else {
                            listElement.html('<div class="dropdown-item text-muted">No notifications</div>');
                        }

                        // Show toast for new notifications
                        if (unreadCount > lastNotificationCount) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                icon: 'info',
                                title: 'New notification received'
                            });
                        }

                        lastNotificationCount = unreadCount;
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading notifications:', error);
                }
            });
        }

        function markNotificationRead(notificationId) {
            $.ajax({
                url: 'process/mark_notification_read.php',
                method: 'POST',
                data: { notification_id: notificationId },
                success: function (response) {
                    if (response.status === 'success') {
                        loadNotifications();
                    }
                }
            });
        }

        // Only initialize notifications if user is logged in
        if (document.getElementById('notificationsDropdown')) {
            loadNotifications();
            setInterval(loadNotifications, 30000);
        }
    </script>

</body>

</html>