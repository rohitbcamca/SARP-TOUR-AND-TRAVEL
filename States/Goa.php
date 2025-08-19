<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Goa'";
if (!empty($selected_city)) {
    $hotels_query .= " AND city = ?";
}
$hotels_query .= " ORDER BY rating DESC";

$hotels = [];
if ($stmt = $conn->prepare($hotels_query)) {
    if (!empty($selected_city)) {
        $stmt->bind_param("s", $selected_city);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
    $stmt->close();
}



// Fetch cab drivers data
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Goa'";
if (!empty($selected_city)) {
    $cab_query .= " AND city = ?";
}
$cab_query .= " ORDER BY name";

$cab_drivers = [];
if ($stmt = $conn->prepare($cab_query)) {
    if (!empty($selected_city)) {
        $stmt->bind_param("s", $selected_city);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cab_drivers[] = $row;
    }
    $stmt->close();
}

// Get unique cities for filter
$cities = [];
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Goa'";
if ($result = $conn->query($cities_query)) {
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goa Tourism - Discover the Beach Paradise</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style= "background-image: url('IMAGE/goacover.webp')">
        <div class="container">
            <h1>Welcome to Goa</h1>
            <p>Goa, India's beach paradise, is a state brimming with golden sands, swaying palms, and vibrant culture. From the serene Palolem Beach to the bustling markets of North Goa, it offers experiences that relax the body and energize the soul.</p>
            <p>Explore our guide to the must-visit destinations in this coastal paradise.</p>
        </div>
    </header>

    
    <?php if(isset($_SESSION["user_id"])): ?>
        <div class="welcome-banner">
            <h1>Welcome, <?php echo $user_name; ?>!</h1>
            <p>Your trusted partner in creating unforgettable travel experiences</p>
            <div class="user-info">
                <?php echo $user_email; ?>
            </div>
        </div>
        <?php endif; ?>

<!-- City Filter -->
    <div class="container" style="margin-top: 20px;">
        <div class="row">
            <div class="col-md-12">
                <div class="filter-section">
                    <h3>Filter by City</h3>
                    <form method="GET" action="">
                        <select name="city" class="form-control" onchange="this.form.submit()">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($selected_city == $city) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Local Services Section -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section-title">Local Services</h2>
                <?php if (empty($hotels) && empty($trekking_services)): ?>
                    <div class="no-services">
                        <p>No local services found for the selected city.</p>
                    </div>
                <?php else: ?>
                    <div class="services-grid">
                        <?php
                        $current_type = '';
                        foreach ($hotels as $hotel):
                            if ($current_type != $hotel['room_types']):
                                if ($current_type != ''):
                                    echo '</div>'; // Close previous service type group
                                endif;
                                $current_type = $hotel['room_types'];
                                echo '<div class="service-type-group">';
                                echo '<h3 class="service-type">' . htmlspecialchars($hotel['room_types']) . '</h3>';
                            endif;
                        ?>
                            <div class="service-item">
                                <h4><?php echo htmlspecialchars($hotel['hotel_name']); ?></h4>
                                <div class="service-details">
                                    <?php if (!empty($hotel['description'])): ?>
                                        <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($hotel['phone'])): ?>
                                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hotel['phone']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($hotel['price'])): ?>
                                        <p><i class="fas fa-rupee-sign"></i> <?php echo htmlspecialchars($hotel['price']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($hotel['location'])): ?>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($hotel['rating'])): ?>
                                        <p class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $hotel['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div> <!-- Close last service type group -->
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="collap" id="cp1">
        <h1>Explore Palolem Beach</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="palolem">
            <div class="container">
                <h2 class="section-title">Palolem Beach – Goa's Peaceful Paradise</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Nestled in the Canacona region of <strong>South Goa, Palolem Beach</strong> is a serene, crescent-shaped shoreline known for its <strong>calm waters, white sand, and relaxed vibes</strong>. Less commercialized than North Goa beaches, Palolem is perfect for travelers seeking peace, natural beauty, and a more soulful coastal experience.</p1>
                            
                            <h3>Why Visit Palolem Beach?</h3>
                            <ul>
                                <li><strong>Breathtaking Natural Beauty:</strong> Fringed with swaying palm trees and backed by green hills, the beach looks like a postcard come to life.</li>
                                <li><strong>Calm and Clean Waters:</strong> Ideal for swimming, kayaking, and relaxing without the hustle of noisy crowds.</li>
                                <li><strong>Dolphin Spotting:</strong> You can hop on a boat in the early morning for a chance to spot playful dolphins in the Arabian Sea.</li>
                                <li><strong>Laid-back Beach Shacks:</strong> Enjoy fresh seafood, Goan curries, cocktails, and live music in cozy beach huts.</li>
                                <li><strong>Yoga & Wellness:</strong> Palolem is a hub for yoga retreats, Ayurvedic massages, and meditation.</li>
                            </ul>
                            
                            <h3>Opening & Closing Timings</h3>
                            <ul>
                                <li>🟢 <strong>Beach Access:</strong> Open 24 hours (public beach)</li>
                                <li>🟣 <strong>Activities & Rentals:</strong> Usually active from <strong>7:00 AM – 6:00 PM</strong></li>
                                <li>🟤 <strong>Boat Rides & Kayaking:</strong> Available from <strong>7:00 AM – 5:30 PM</strong></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Top Things to Do</h3>
                            <ul>
                                <li><strong>Silent Noise Party</strong>  
                                    <ul>
                                        <li>A unique headphone party held on weekends where music plays in your headset only!</li>
                                        <li>Location: Neptune Point (South end of the beach)</li>
                                    </ul>
                                </li>
                                <li><strong>Sunset at Butterfly Beach</strong>
                                    <ul>
                                        <li>Take a short boat ride or a forest trek to reach this hidden beach with spectacular sunsets.</li>
                                    </ul>
                                </li>
                                <li><strong>Visit Monkey Island (Canacona Island)</strong>
                                    <ul>
                                        <li>Walk to the island during low tide or take a boat during high tide. Great for exploration and solitude.</li>
                                    </ul>
                                </li>
                            </ul>
                            
                            <h3>Best Time to Visit</h3>
                            <ul>
                                <li><strong>November to February:</strong> Cool, dry, and perfect beach weather</li>
                                <li>Avoid <strong>monsoon season (June to September)</strong> due to high tides and limited activities</li>
                            </ul>
                            
                            <h3>How to Reach Palolem Beach</h3>
                            <ul>
                                <li><strong>By Air:</strong> Dabolim Airport (GOI) – 60 km (approx. 1.5 hrs by taxi)</li>
                                <li><strong>By Train:</strong> Canacona Station – Just 3 km away</li>
                                <li><strong>By Road:</strong> Easily accessible by taxi or scooter from other parts of Goa</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo3">Local Services</button>
                </div>
                
                <div id="demo3" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Beach Resorts</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Cape Goa</h3>
                                        <p>Luxury beachfront resort with private beach access and pool.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Palolem Beach Resort</h3>
                                        <p>Beach huts with sea views and authentic Goan hospitality.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Silent Noise Cottages</h3>
                                        <p>Budget-friendly cottages near the famous Silent Noise party spot.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Cabs Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-taxi"></i>
                                    <h3>Transport Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Palolem Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for beach transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-motorcycle"></i> Scooter Rentals</h3>
                                        <p>Self-drive scooters for exploring South Goa at your own pace.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-ship"></i> Boat Tours</h3>
                                        <p>Dolphin spotting and island hopping tours from Palolem.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹500/person</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <section class="best-time" id="best-time">
        <div class="container">
            <h2 class="section-title">Best Time to Visit Goa</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Palolem Beach</h3>
                        <ul>
                            <li>November to February: Perfect beach weather</li>
                            <li>Avoid June to September: Monsoon season with high tides</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Goa</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p>Dabolim Airport (GOI) serves Goa with domestic and international flights.</p>
                    <p>Distance to Palolem Beach: 60 km (approx. 1.5 hrs by taxi)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Canacona Station is just 3 km from Palolem Beach.</p>
                    <p>Other major stations: Madgaon, Thivim</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Goa is well-connected via national highways and state roads.</p>
                    <p>Regular bus services connect all major destinations</p>
                </div>
            </div>
        </div>
    </section>
    
    
<div class="route-container">
    <h2 class="section-title">🗺 South Goa Exploration Route</h2>
    <div class="pathway">
      <div class="step">✈ Dabolim Airport</div>
      <div class="arrow">➝</div>
      <div class="step">🏖 Palolem Beach</div>
      <div class="arrow">➝</div>
      <div class="step">🎧 Silent Noise Party</div>
      <div class="arrow">➝</div>
      <div class="step">🦋 Butterfly Beach</div>
      <div class="arrow">➝</div>
      <div class="step">🏝 Monkey Island</div>
      <div class="arrow">➝</div>
      <div class="step">🧘‍♂ Yoga Retreats</div>
      <div class="arrow">➝</div>
      <div class="step">🌿 Explore Canacona</div>
      <div class="arrow">➝</div>
      <div class="step">🚉 Canacona Station / Taxi</div>
    </div>
  </div>

  
          <!-- Cab Drivers Section -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="section-title">Cab Drivers</h2>
                <?php if (empty($cab_drivers)): ?>
                    <div class="no-services">
                        <p>No cab drivers found for the selected city.</p>
                    </div>
                <?php else: ?>
                    <div class="local-services-columns">
                        <!-- Cab Drivers Column -->
                        <div class="service-column">
                            <div class="service-category">
                                <i class="fas fa-taxi"></i>
                                <h3>Cab Drivers</h3>
                            </div>
                            <div class="services-grid">
                                <?php foreach ($cab_drivers as $driver): ?>
                                    <div class="service-item">
                                        <h3><i class="fas fa-user"></i> <?php echo htmlspecialchars($driver['name']); ?></h3>
                                        <?php if (!empty($driver['vehicle_type'])): ?>
                                            <p><i class="fas fa-car"></i> <?php echo htmlspecialchars($driver['vehicle_type']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($driver['vehicle_number'])): ?>
                                            <p><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($driver['vehicle_number']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($driver['phone'])): ?>
                                            <p class="contact"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($driver['phone']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($driver['license_number'])): ?>
                                            <p><i class="fas fa-id-card"></i> License: <?php echo htmlspecialchars($driver['license_number']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($driver['address'])): ?>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($driver['address']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#cp1">Palolem Beach</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@goatourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Panaji, Goa, India</li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <ul>
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                    </ul>
                </div>
            </div>
            
            <hr style="border: 1px solid #ccc; margin: 20px 0;">
            <div class="copyright" style="text-align: center;">
                <p>&copy; 2025 Goa Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>