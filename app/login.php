<?php
// Start the session
session_start();
// check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard if user is already logged in
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard/index.php");
    } elseif ($_SESSION['role'] == 'clinic') {
        header("Location: index.php");
    }
    exit;
}
// Include the header
include 'includes/header.php'; 


?>

<!-- Hero Section for Login -->
<section class="hero-login bg-info text-white py-5 text-center">
    <h1 class="display-4 mb-4">Login</h1>
    <p class="lead mb-6">Login to access your account and features.</p>
</section>

<!-- Login Form Section -->
<section id="login-form" class="login-form py-5 bg-light">
    <div class="container">
        <h2 class="h2 mb-4 text-center">Login to Your Account</h2>
        <!-- Display Success or Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="process/login.php" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <!-- Email Field -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>

                <!-- Password Field -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <p class="mt-3 text-center">Don't have an account? <a href="register.php" class="text-primary">Register</a></p>
    </div>
</section>

<?php 
// Include the footer
include 'includes/footer.php'; 
?>
