<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Gujarat'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Gujarat'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Gujarat'";
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
    <title>Gujarat Tourism - Discover the Land of Temples and Heritage</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/gujratcover.jpeg');">
        <div class="container">
            <h1>Welcome to Gujarat</h1>
            <p>Gujarat, the land of temples and vibrant culture, is a state brimming with spirituality, history, and natural beauty. From the sacred Somnath Temple to the heritage city of Ahmedabad, Gujarat offers experiences that touch the soul and ignite historical curiosity.</p>
            <p>Explore our guide to the must-visit destinations in this vibrant state.</p>
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
        <h1>Explore Somnath</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="somnath">
            <div class="container">
                <h2 class="section-title">Somnath Temple – The Eternal Shrine of Lord Shiva</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Located on the western coast of Gujarat in the town of <strong>Prabhas Patan</strong> near Veraval, <strong>Somnath Temple</strong> is one of the most sacred and revered <strong>Jyotirlingas</strong> of Lord Shiva. Steeped in mythology, history, and spiritual significance, the temple stands as a symbol of <strong>resilience and devotion</strong>, having been rebuilt multiple times after invasions and natural calamities.</p1>
                            
                            <h3>Highlights</h3>
                            <ul>
                                <li><strong>Historical Significance:</strong> Mentioned in ancient texts like the <em>Rigveda</em> and <em>Skanda Purana</em>, Somnath has been a major pilgrimage site for centuries.</li>
                                <li><strong>Architectural Marvel:</strong> The temple is built in the <strong>Chalukya style of architecture</strong>, showcasing intricate carvings, majestic pillars, and a shikhara (spire) that rises impressively.</li>
                                <li><strong>Mythological Belief:</strong> Legend says the moon god (Chandra) built the original Somnath Temple to regain his lost shine after being cursed. Hence, the temple is also called the "<strong>Shrine Eternal</strong>."</li>
                            </ul>
                            
                            <h3>What to See</h3>
                            <ul>
                                <li><strong>Main Shiva Lingam:</strong> The central sanctum houses the sacred <strong>Jyotirlinga</strong>, drawing millions of devotees every year.</li>
                                <li><strong>Sea-facing Location:</strong> The temple's stunning location offers a serene view of the <strong>Arabian Sea</strong>, creating a spiritually uplifting atmosphere.</li>
                                <li><strong>Sound and Light Show:</strong> Held every evening in the temple complex, this multilingual show narrates the glorious history of Somnath in an engaging way.</li>
                                <li><strong>Triveni Sangam Ghat:</strong> Close to the temple lies the confluence of three rivers — Hiran, Kapila, and the mythical Saraswati.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Opening and Closing Timings</h3>
                            <ul>
                                <li><strong>Temple Timings:</strong> 6:00 AM to 9:30 PM (Open daily)</li>
                                <li><strong>Darshan Timings:</strong>
                                    <ul>
                                        <li>Morning: 6:00 AM – 12:00 PM</li>
                                        <li>Afternoon: 12:30 PM – 6:30 PM</li>
                                        <li>Evening: 7:00 PM – 9:30 PM</li>
                                    </ul>
                                </li>
                                <li><strong>Aarti Timings:</strong>
                                    <ul>
                                        <li>Morning Aarti: 7:00 AM</li>
                                        <li>Midday Aarti: 12:00 PM</li>
                                        <li>Evening Aarti: 7:00 PM</li>
                                    </ul>
                                </li>
                                <li><strong>Sound & Light Show (Jay Somnath Show):</strong> 8:00 PM to 9:00 PM (in Hindi and other languages) | Ticket Fee: Approx. ₹25–₹50</li>
                            </ul>
                            
                            <h3>Important Tips</h3>
                            <ul>
                                <li><strong>Dress Modestly:</strong> Traditional attire is appreciated as it's a sacred temple.</li>
                                <li><strong>Photography is not allowed</strong> inside the temple premises for security reasons.</li>
                                <li><strong>Best Time to Visit:</strong> October to March (pleasant weather); Maha Shivratri is a grand occasion here.</li>
                            </ul>
                            
                            <h3>How to Reach</h3>
                            <ul>
                                <li><strong>By Air:</strong> The nearest airport is <strong>Diu Airport (85 km)</strong>.</li>
                                <li><strong>By Train:</strong> <strong>Veraval Railway Station (7 km)</strong> is the nearest railhead.</li>
                                <li><strong>By Road:</strong> Well-connected via roads and buses from cities like Ahmedabad, Rajkot, and Dwarka.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
 
    <div class="collap" id="cp2">
        <h1>Explore Ahmedabad</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="ahmedabad">
            <div class="container">
                <h2 class="section-title">Ahmedabad – The Heritage Heart of Gujarat</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Ahmedabad, the largest city in Gujarat, is a vibrant blend of history, culture, modernity, and innovation. Known for its rich architectural heritage, bustling bazaars, delicious street food, and warm hospitality, the city earned the honor of being India's <strong>first UNESCO World Heritage City</strong> in 2017.</p1>
                            
                            <h3>Top Attractions in Ahmedabad</h3>
                            
                            <h4>1. Sabarmati Ashram</h4>
                            <ul>
                                <li>Once the residence of <strong>Mahatma Gandhi</strong>, this peaceful ashram on the banks of the Sabarmati River is a center of historical importance and simplicity.</li>
                                <li>Timings: <strong>8:00 AM – 6:30 PM</strong> | Entry: Free</li>
                            </ul>
                            
                            <h4>2. Kankaria Lake</h4>
                            <ul>
                                <li>A large, man-made lake with a zoo, toy train, water rides, balloon rides, and more — perfect for families and kids.</li>
                                <li>Timings: <strong>9:00 AM – 10:00 PM (Monday closed)</strong> | ₹10–₹25</li>
                            </ul>
                            
                            <h4>3. Jama Masjid</h4>
                            <ul>
                                <li>Built in 1424 by Sultan Ahmed Shah, this mosque is known for its symmetry and grandeur, using yellow sandstone with beautiful calligraphy.</li>
                                <li>Timings: <strong>6:00 AM – 8:00 PM</strong></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Cultural & Historical Gems</h3>
                            <ul>
                                <li><strong>Heritage Walk of Ahmedabad:</strong> Explore the old city lanes, hawels, temples, and pols (traditional housing clusters).</li>
                                <li><strong>Auto World Vintage Car Museum:</strong> Showcasing a rich collection of vintage cars and royal vehicles.</li>
                            </ul>
                            
                            <h3>Must-Try Street Foods</h3>
                            <ul>
                                <li><strong>Dabeli, Khaman, Fafda-Jalebi, Sev Puri, and Handvo</strong></li>
                                <li><strong>Places to try:</strong> Manek Chowk, Law Garden, CG Road, and the local Gujarati thali restaurants like <strong>Gordhan Thal</strong> or <strong>Agashiye</strong>.</li>
                            </ul>
                            
                            <h3>Best Time to Visit</h3>
                            <ul>
                                <li><strong>October to March:</strong> Pleasant winter months ideal for sightseeing.</li>
                                <li><strong>Special Festival:</strong> Uttarayan (January 14) – the Kite Festival, where the skies are filled with colorful kites.</li>
                            </ul>
                            
                            <h3>How to Reach</h3>
                            <ul>
                                <li><strong>By Air:</strong> Sardar Vallabhbhai Patel International Airport connects to major Indian and global cities.</li>
                                <li><strong>By Train:</strong> Ahmedabad Junction (ADI) is a major railway hub.</li>
                                <li><strong>By Road:</strong> Well-connected via NH 8 and state highways.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <div class="collap" id="cp3">
        <h1>Explore Dwarka</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="dwarka">
            <div class="container">
                <h2 class="section-title">Dwarka – The Sacred Kingdom of Lord Krishna</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Dwarka, located on the western tip of Gujarat by the <strong>Arabian Sea</strong>, is one of the <strong>Char Dham pilgrimage sites</strong> and one of the <strong>Sapta Puri</strong>—seven sacred cities in Hinduism. Known as the "<strong>Kingdom of Lord Krishna</strong>", Dwarka is steeped in mythology, spiritual importance, and coastal charm.</p1>
                            
                            <h3>Top Attractions in Dwarka</h3>
                            
                            <h4>1. Dwarkadhish Temple (Jagat Mandir)</h4>
                            <ul>
                                <li>The heart of Dwarka, this 5-story temple dedicated to <strong>Lord Krishna</strong> stands on 72 intricately carved pillars and dates back over 2,500 years.</li>
                                <li>Timings:
                                    <ul>
                                        <li>Morning: 6:30 AM - 1:00 PM</li>
                                        <li>Evening: 5:00 PM - 9:30 PM</li>
                                    </ul>
                                </li>
                                <li>Entry Fee: Free</li>
                                <li>Photography: Not allowed inside the temple.</li>
                            </ul>
                            
                            <h4>2. Gomti Ghat</h4>
                            <ul>
                                <li>A sacred spot where the <strong>Gomti River meets the Arabian Sea</strong>. Devotees take a holy dip here before visiting the Dwarkadhish Temple.</li>
                                <li>Best Time: Early morning for sunrise or during evening aarti.</li>
                            </ul>
                            
                            <h4>3. Rukmini Devi Temple</h4>
                            <ul>
                                <li>Located 2 km from the main temple, this shrine is dedicated to <strong>Rukmini</strong>, Krishna's consort. It features beautiful carvings and a peaceful ambiance.</li>
                                <li>Timings: 6:00 AM - 12:00 PM | 4:00 PM - 9:00 PM</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Best Time to Visit</h3>
                            <ul>
                                <li><strong>October to March:</strong> Pleasant weather for sightseeing and temple visits.</li>
                                <li><strong>Janmashtami (August–September):</strong> Celebrated with grandeur as it marks Lord Krishna's birth.</li>
                            </ul>
                            
                            <h3>Local Delicacies</h3>
                            <ul>
                                <li>Enjoy traditional Gujarati thalis, snacks like Thepla, Khaman Dhokla, and sweets like Ghooghra.</li>
                            </ul>
                            
                            <h3>How to Reach</h3>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Jamnagar (approx. 130 km).</li>
                                <li><strong>By Train:</strong> Dwarka Railway Station connects to major cities like Ahmedabad, Rajkot, and Mumbai.</li>
                                <li><strong>By Road:</strong> Well-connected by state highways and buses from all parts of Gujarat.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <section class="best-time" id="best-time">
        <div class="container">
            <h2 class="section-title">Best Time to Visit</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Somnath</h3>
                        <ul>
                            <li>October to March: Pleasant weather for temple visits</li>
                            <li>Maha Shivratri: Special celebrations at the temple</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Ahmedabad</h3>
                        <ul>
                            <li>October to March: Pleasant winter months ideal for sightseeing</li>
                            <li>Uttarayan (January 14): Kite Festival with colorful skies</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Dwarka</h3>
                        <ul>
                            <li>October to March: Pleasant weather for temple visits</li>
                            <li>Janmashtami (August-September): Grand celebrations of Lord Krishna's birth</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p>Sardar Vallabhbhai Patel International Airport (AMD) serves Ahmedabad with domestic and international flights.</p>
                    <p>Other airports: Diu Airport (for Somnath), Jamnagar Airport (for Dwarka)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Ahmedabad Junction (ADI) is a major railway hub connecting to cities across India.</p>
                    <p>Other stations: Veraval (for Somnath), Dwarka Station</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Gujarat is well-connected via national highways and state roads.</p>
                    <p>Regular bus services connect all major destinations</p>
                    <p>Suggested Route: Ahmedabad → Dwarka → Somnath (or reverse)</p>
                </div>
            </div>
        </div>
    </section>
    
    
<div class="route-wrapper">
    <h2 class="section-title">🗺 Gujarat Heritage Route</h2>
    
    <div class="pathway">
      <div class="step">
        <div class="emoji">🏙</div>
        Ahmedabad
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🕌</div>
        Dwarka
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">⛪</div>
        Somnath
      </div>
    </div>
  
    <div class="path-label">
      Ahmedabad → Dwarka → Somnath
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
                        <li><a href="#cp1">Somnath</a></li>
                        <li><a href="#cp2">Ahmedabad</a></li>
                        <li><a href="#cp3">Dwarka</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@gujarattourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Ahmedabad, Gujarat, India</li>
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
                <p>&copy; 2025 Gujarat Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>