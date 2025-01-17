<?php 
session_start();


// Redirect to dashboard if user is already logged in
if (isset($_SESSION['user_id'])) {
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

<!-- Hero Section for Register -->
<section class="hero-register bg-info text-white py-5 text-center">
    <h1 class="display-4 mb-4">Register</h1>
    <p class="lead mb-6">Create an account to access all the features and benefits we offer.</p>
</section>

<!-- Register Form Section -->
<section id="register-form" class="register-form py-5 bg-light">
    <div class="container">
        <h2 class="h2 mb-4 text-center">Create Your Account</h2>
         <!-- Display Success or Error Messages -->
         <!-- Display Success or Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

        <!-- Register Form -->
        <form action="process/register.php" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <!-- Full Name Field -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>
                
                <!-- Clinic Name Field -->
                <div class="col-md-6 mb-3">
                    <label for="clinic_name" class="form-label">Clinic Name</label>
                    <input type="text" class="form-control" id="clinic_name" name="clinic_name" required>
                    <div class="invalid-feedback">Please enter the clinic name.</div>
                </div>
            </div>

            <div class="row">
                <!-- Email Field -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>

                <!-- Phone Number Field -->
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
            </div>

            <div class="row">
                <!-- Password Field -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Please enter your password.</div>
                </div>

                <!-- Confirm Password Field -->
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Please confirm your password.</div>
                </div>
            </div>

            <div class="row">
                <!-- Clinic ID Field -->
                <div class="col-md-6 mb-3">
                    <label for="clinic_id" class="form-label">Clinic ID</label>
                    <input type="text" class="form-control" id="clinic_id" name="clinic_id" required>
                    <div class="invalid-feedback">Please enter the clinic ID.</div>
                </div>
                <!-- Address Field -->
                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
    </div>
</section>

<?php 
// Include the footer
include 'includes/footer.php'; 
?>
