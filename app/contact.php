<?php 
// Include the header
include 'includes/header.php'; 
?>

<!-- Hero Section for Contact -->
<section class="hero-contact bg-primary text-white py-5 text-center">
    <h1 class="display-4 mb-4">Contact Us</h1>
    <p class="lead mb-6">We'd love to hear from you! Reach out for any inquiries or feedback.</p>
</section>

<!-- Contact Form Section -->
<section id="contact-form" class="contact-form py-5 bg-light">
    <div class="container">
        <h2 class="h2 mb-4 text-center">Get in Touch</h2>
        
        <!-- Contact Form -->
        <form action="process_contact.php" method="POST" class="needs-validation" novalidate>
            <div class="row">
                <!-- Name Field -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <div class="invalid-feedback">Please enter your full name.</div>
                </div>
                
                <!-- Email Field -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>
            </div>

            <!-- Message Field -->
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                <div class="invalid-feedback">Please provide a message.</div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
</section>

<!-- Contact Information Section -->
<section id="contact-info" class="contact-info py-5 bg-info text-white text-center">
    <div class="container">
        <h2 class="h2 mb-4">Our Contact Information</h2>
        <p class="lead mb-4">Feel free to reach out via the contact form or through any of the following methods:</p>
        
        <ul class="list-unstyled">
            <li><strong>Email:</strong> <a href="mailto:info@yourdomain.com" class="text-white">info@yourdomain.com</a></li>
            <li><strong>Phone:</strong> +1 234 567 890</li>
            <li><strong>Address:</strong> 123 Your Street, Your City, Country</li>
        </ul>
    </div>
</section>

<!-- Map Section -->
<section id="map" class="map py-5">
    <div class="container">
        <h2 class="h2 mb-4 text-center">Find Us</h2>
        <!-- Embed Google Map -->
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2925.518514358946!2d-77.0368705!3d38.9071923!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89b7b5c18d9d4a23%3A0x7d46e9e6535e7c57!2sWhite%20House!5e0!3m2!1sen!2sus!4v1614319137269!5m2!1sen!2sus" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php 
// Include the footer
include 'includes/footer.php'; 
?>
