<?php 
// Include the header
include 'includes/header.php'; 

// Include the database connection file
include 'config/db.php';

// Check if the connection is open
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch medications data from the database
$sql = "SELECT * FROM medications";
$result = mysqli_query($conn, $sql);

// Check if the query executed successfully
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!-- Hero Section -->
<section class="hero bg-primary text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Welcome to Our Medical Association</h1>
        <p class="lead mb-5">We provide essential healthcare supplies to clinics and hospitals, ensuring the availability of medications, blood, and medical machines when needed most.</p>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="mission-vision py-5 bg-light">
    <div class="container text-center">
        <h2 class="h2 mb-4">Mission & Vision</h2>
        <div class="row justify-content-center">
            <div class="col-md-5 mb-4">
                <h3 class="h4 mb-3">Our Mission</h3>
                <p class="lead">To provide accessible and reliable medical supplies to healthcare providers, enhancing patient care and treatment outcomes worldwide.</p>
            </div>
            <div class="col-md-5 mb-4">
                <h3 class="h4 mb-3">Our Vision</h3>
                <p class="lead">To be the leading medical supplies provider, known for our integrity, innovation, and impact on improving healthcare systems globally.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section (3 Cards) -->
<section id="services" class="services py-5">
    <div class="container text-center">
        <h2 class="h2 mb-4">Our Services</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Medication Card -->
            <div class="col">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Medications</h3>
                        <p class="card-text mb-4">Explore a wide range of medications available for all your healthcare needs, with detailed descriptions and quantities.</p>
                        <a href="medications.php" class="btn btn-primary">View Medications</a>
                    </div>
                </div>
            </div>
            <!-- Blood Card -->
            <div class="col">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Blood</h3>
                        <p class="card-text mb-4">We ensure a steady supply of safe and compatible blood for all types of medical treatments and emergencies.</p>
                        <a href="blood.php" class="btn btn-primary">View Blood Supply</a>
                    </div>
                </div>
            </div>
            <!-- Machines Card -->
            <div class="col">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h3 class="card-title h5 mb-3">Machines</h3>
                        <p class="card-text mb-4">Access a variety of medical machines, from diagnostic tools to life-saving equipment, all available for your clinic's needs.</p>
                        <a href="machines.php" class="btn btn-primary">View Machines</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
// Close the database connection after usage
mysqli_close($conn);

// Include the footer
include 'includes/footer.php'; 
?>
