<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Karnataka'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Karnataka'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Karnataka  '";
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
    <title>Karnataka Tourism - Discover Bengaluru, The Garden City & Silicon Valley of India</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/KarnCover.jpg')">
        <div class="container">
            <h1>Welcome to Karnataka</h1>
            <p>Bengaluru (Bangalore), the capital of Karnataka, is a vibrant metropolis known for its pleasant weather, IT hubs, lush gardens, and rich heritage. It seamlessly blends modern technology, cultural heritage, and a thriving food and nightlife scene, making it a perfect destination for tourists.</p>
            <p>Explore our guide to the must-visit destinations in this dynamic city.</p>
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
        <h1>Lalbagh Botanical Garden</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
 <div id="demo" class="collapse">

    <section class="attractions" id="lalbagh-garden">
        <div class="container">
            <h2 class="section-title">Lalbagh Botanical Garden</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/lalbhagh.jpg" alt="Lalbagh Botanical Garden">
                    </div>
                    <div class="card-content">
                        <h3>A Green Paradise in the Heart of Bengaluru</h3>
                        <p1>Lalbagh Botanical Garden is a 240-acre garden with over 1,800 species of plants, century-old trees, and a stunning glasshouse inspired by London's Crystal Palace. It's one of Bengaluru's most iconic landmarks and a favorite among nature lovers and photographers.</p1>
                        
                        <h4>Key Features</h4>
                        <ul>
                            <li>Home to rare species of tropical plants and trees</li>
                            <li>Features a beautiful lake and several themed gardens</li>
                            <li>The famous Flower Show during Independence Day and Republic Day attracts thousands</li>
                        </ul>
                        
                        <h4>Visiting Information</h4>
                        <ul>
                            <li>Timings: 6:00 AM – 7:00 PM daily</li>
                            <li>Entry Fee: ₹25 for adults, ₹10 for children</li>
                            <li>Best time to visit: Early morning or late afternoon</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- <div class="hot">
                 <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo3">Local Services</button>
             </div>
           
        <div id="demo3" class="collapse">
            <div id="serviceSection">
                <div class="local-services-columns"> -->
                    <!-- Hotels Column -->
                    <!-- <div class="service-column">
                        <div class="service-category">
                            <i class="fas fa-hotel"></i>
                            <h3>Recommended Hotels</h3>
                        </div>
                        <div class="services-grid">
                            <div class="service-item">
                                <h3><i class="fas fa-bed"></i> Lalbagh Residency</h3>
                                <p>Comfortable accommodations near the garden.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-bed"></i> Green View Hotel</h3>
                                <p>Budget-friendly option with garden views.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,000/night</p>
                            </div>
                        </div>
                    </div> -->
            
                    <!-- Cabs Column -->
                    <!-- <div class="service-column">
                        <div class="service-category">
                            <i class="fas fa-taxi"></i>
                            <h3>Cab Services</h3>
                        </div>
                        <div class="services-grid">
                            <div class="service-item">
                                <h3><i class="fas fa-car"></i> Bengaluru City Cabs</h3>
                                <p>Reliable local taxi service with fixed rates.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-car"></i> Green Earth Tours</h3>
                                <p>Specialized service for garden and park visits.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </section>
</div>
 
    <div class="collap" id="cp2">
        <h1>Cubbon Park</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">

    <section class="attractions" id="cubbon-park">
        <div class="container">
            <h2 class="section-title">Cubbon Park</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/cubbonpark.jpg" alt="Cubbon Park">
                    </div>
                    <div class="card-content">
                        <h3>The Green Lungs of Bengaluru</h3>
                        <p1>Cubbon Park is a beautiful green oasis in the heart of Bengaluru, spread over 300 acres. It's a perfect place for morning walks, cycling, and relaxation amidst nature, away from the city's hustle and bustle.</p1>
                        
                        <h4>Key Features</h4>
                        <ul>
                            <li>Home to Bangalore High Court, State Library, and several statues of historical figures</li>
                            <li>Features well-maintained walking paths, flowering trees, and open spaces</li>
                            <li>Popular spot for joggers, yoga practitioners, and nature lovers</li>
                        </ul>
                        
                        <h4>Visiting Information</h4>
                        <ul>
                            <li>Timings: Open 24 hours (best visited in the morning or evening)</li>
                            <li>Entry Fee: Free</li>
                            <li>Cycling allowed only on designated paths</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- <div class="hot">
                <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo4">Local Services</button>
            </div>
            <div id="demo4" class="collapse">
                <div id="serviceSection">
                    <div class="local-services-columns"> -->
                        <!-- Hotels Column -->
                        <!-- <div class="service-column">
                            <div class="service-category">
                                <i class="fas fa-hotel"></i>
                                <h3>Recommended Hotels</h3>
                            </div>
                            <div class="services-grid">
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Park View Suites</h3>
                                    <p>Luxury accommodations with views of Cubbon Park.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,000/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> City Central Hotel</h3>
                                    <p>Mid-range option near the park's main entrance.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                </div>
                            </div>
                        </div> -->
                
                        <!-- Cabs Column -->
                        <!-- <div class="service-column">
                            <div class="service-category">
                                <i class="fas fa-taxi"></i>
                                <h3>Cab Services</h3>
                            </div>
                            <div class="services-grid">
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Bengaluru Metro Cabs</h3>
                                    <p>Reliable taxi service with AC and non-AC options.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Green Wheels</h3>
                                    <p>Eco-friendly electric cabs for city tours.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </section>
</div>
    
<div class="collap" id="cp3">
    <h1>Bangalore Palace</h1>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
</div>

  <div id="demo2" class="collapse">
    <section class="attractions" id="bangalore-palace">
        <div class="container">
            <h2 class="section-title">Bangalore Palace</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/Bangalorepalace.jpg" alt="Bangalore Palace">
                    </div>
                    <div class="card-content">
                        <h3>A Royal Heritage Site</h3>
                        <p1>Bangalore Palace is a majestic palace built in Tudor-style architecture, inspired by England's Windsor Castle. It stands as a testament to the royal heritage of Karnataka and offers visitors a glimpse into the luxurious lifestyle of the Wadiyar dynasty.</p1>
                        
                        <h4>Key Features</h4>
                        <ul>
                            <li>Features royal artifacts, paintings, and stunning wooden interiors</li>
                            <li>The palace grounds host cultural events, concerts, and exhibitions</li>
                            <li>Beautiful gardens and courtyards perfect for photography</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/BangalorePalaceAttract.jpg" alt="Bangalore Palace Interior">
                    </div>
                    <div class="card-content">
                        <h3>Visiting Information</h3>
                        
                        <h4>Palace Tours</h4>
                        <ul>
                            <li>Guided tours available in multiple languages</li>
                            <li>Audio guides for self-paced exploration</li>
                            <li>Photography allowed (additional fee may apply)</li>
                        </ul>
                        
                        <h4>Practical Details</h4>
                        <ul>
                            <li>Timings: 10:00 AM – 5:30 PM (Closed on Mondays)</li>
                            <li>Entry Fee: ₹230 for Indians, ₹460 for foreigners</li>
                            <li>Parking available on premises</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- <div class="hot">
                <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo5">Local Services</button>
            </div>
            <div id="demo5" class="collapse">
                <div id="serviceSection">
                    <div class="local-services-columns"> -->
                        <!-- Hotels Column -->
                        <!-- <div class="service-column">
                            <div class="service-category">
                                <i class="fas fa-hotel"></i>
                                <h3>Recommended Hotels</h3>
                            </div>
                            <div class="services-grid">
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Royal Heritage Hotel</h3>
                                    <p>Luxury accommodations near the palace.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,000/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Palace View Inn</h3>
                                    <p>Mid-range hotel with views of the palace.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
                                </div>
                            </div>
                        </div> -->
                
                        <!-- Cabs Column -->
                        <!-- <div class="service-column">
                            <div class="service-category">
                                <i class="fas fa-taxi"></i>
                                <h3>Cab Services</h3>
                            </div>
                            <div class="services-grid">
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Palace Tours & Travels</h3>
                                    <p>Specialized service for heritage site visitors.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Heritage Cabs</h3>
                                    <p>Guided tours with transportation to multiple heritage sites.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹3,500/day</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </section>
</div>
    
    <section class="best-time" id="best-time">
        <div class="container">
            <h2 class="section-title">Best Time to Visit Bengaluru</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Winter (October – March)</h3>
                        <ul>
                            <li>Best weather for sightseeing with pleasant temperatures</li>
                            <li>Ideal for outdoor activities and garden visits</li>
                            <li>Peak tourist season with many cultural events</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Monsoon (July – September)</h3>
                        <ul>
                            <li>Lush greenery throughout the city</li>
                            <li>Occasional rains may affect outdoor plans</li>
                            <li>Lower hotel rates compared to peak season</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Summer (April – June)</h3>
                        <ul>
                            <li>Pleasant evenings but warm afternoons</li>
                            <li>Good time to visit indoor attractions</li>
                            <li>Fewer crowds at popular sites</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Bengaluru</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Kempegowda International Airport (BLR):</strong> 40 km from the city center</p>
                    <p>Connects to all major domestic and international destinations</p>
                    <p>Airport shuttle and taxi services available to city center</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Bangalore City Railway Station:</strong> Connects to major cities across India</p>
                    <p><strong>Yeshwantpur Railway Station:</strong> Another major station in the city</p>
                    <p>Frequent trains from Mumbai, Chennai, Delhi, and other metros</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected via NH 44, NH 48, and NH 75</p>
                    <p>Regular bus services from neighboring states</p>
                    <p>Excellent road infrastructure makes driving convenient</p>
                </div>
            </div>
        </div>
    </section>

    <div class="route-wrapper">
        <h2 class="section-title">🗺 Bangalore to Vizag Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Bangalore
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🛕</div>
            Lepakshi
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🙏</div>
            Tirupati
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏖</div>
            Vizag
          </div>
        </div>
        <div class="path-label">
            Bangalore → Lepakshi → Tirupati → Visakhapatnam
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
                        <li><a href="#cp1">Lalbagh Botanical Garden</a></li>
                        <li><a href="#cp2">Cubbon Park</a></li>
                        <li><a href="#cp3">Bangalore Palace</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@karnatakatourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Bengaluru, Karnataka, India</li>
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
            
            <div class="copyright" style="text-align: center; margin-top: 20px;">
                <hr style="border: 1px solid #ccc; margin-bottom: 20px;">
                <p>&copy; 2025 Karnataka Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>