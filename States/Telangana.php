<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Telangana'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Telangana'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Telangana  '";
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
    <title>Telangana Tourism - Discover the Land of Pearls and Heritage</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/telanganacover.jpg');">
        <div class="container">
            <h1>Welcome to Telangana</h1>
            <p>Telangana, the land of pearls and nawabi charm, is a state brimming with history, culture, and modernity. From the iconic Charminar to the majestic Warangal Fort, Telangana offers experiences that touch the soul and ignite historical curiosity.</p>
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
        <h1>Explore Hyderabad</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="hyderabad">
            <div class="container">
                <h2 class="section-title">Hyderabad: The City of Pearls & Nawabi Charm</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Hyderabad, the capital of Telangana, is a vibrant blend of history, culture, and modernity. Known as the City of Pearls, it is famous for its royal heritage, mouth-watering biryani, and booming IT industry. Whether you are a history buff, foodie, or adventure seeker, Hyderabad has something for everyone.</p1>
                            
                            <h3>Top Attractions in Hyderabad</h3>
                            
                            <h4>1. Charminar</h4>
                            <ul>
                                <li>The iconic symbol of Hyderabad, built in 1591 by Sultan Muhammad Quli Qutb Shah</li>
                                <li>Surrounded by bustling markets like Laad Bazaar, famous for bangles and pearls</li>
                                <li>Timings: 9:30 AM - 5:30 PM</li>
                            </ul>
                            
                            <h4>2. Golconda Fort</h4>
                            <ul>
                                <li>A magnificent 16th-century fort, known for its acoustic architecture and grand history</li>
                                <li>Don't miss the light & sound show in the evening</li>
                                <li>Timings: 9:00 AM - 5:30 PM</li>
                            </ul>
                            
                            <h4>3. Snow World</h4>
                            <ul>
                                <li>India's first and largest snow-themed park, featuring skiing, ice skating, and snow rides</li>
                                <li>Timings: 11:00 AM - 9:00 PM</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Hyderabad's Famous Food</h3>
                            <ul>
                                <li><strong>Hyderabadi Biryani:</strong> World-famous aromatic rice dish</li>
                                <li><strong>Haleem:</strong> A Ramzan special delicacy</li>
                                <li><strong>Irani Chai & Osmania Biscuits:</strong> A must-try at old cafes</li>
                                <li><strong>Double Ka Meetha & Qubani Ka Meetha:</strong> Delicious desserts</li>
                            </ul>
                            
                            <h3>Best Time to Visit Hyderabad</h3>
                            <ul>
                                <li><strong>October - February:</strong> Pleasant weather for sightseeing</li>
                                <li><strong>Avoid summer (March - June):</strong> Hot and dry conditions</li>
                            </ul>
                            
                            <h3>How to Reach Hyderabad</h3>
                            <ul>
                                <li><strong>By Air:</strong> Rajiv Gandhi International Airport (HYD) has global connectivity</li>
                                <li><strong>By Train:</strong> Major railway stations: Hyderabad, Secunderabad, and Kacheguda</li>
                                <li><strong>By Road:</strong> Well-connected via NH 44, NH 65, and NH 163</li>
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
                                        <h3><i class="fas fa-bed"></i> Taj Krishna</h3>
                                        <p>Luxury hotel with heritage charm, located in Banjara Hills.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 40 6666 2323</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Novotel Hyderabad</h3>
                                        <p>Modern hotel with excellent amenities near Hitech City.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 40 6682 4422</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Golkonda Hotel</h3>
                                        <p>Comfortable stay with great views of the city.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 40 6611 0101</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,200/night</p>
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
                                        <h3><i class="fas fa-car"></i> Hyderabad Cabs</h3>
                                        <p>Reliable local taxi service with fixed rates for city tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Meru Cabs</h3>
                                        <p>AC and non-AC cabs available for local sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Ola Outstation</h3>
                                        <p>For intercity travel and airport transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43222</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </section>
    </div>
 
    <div class="collap" id="cp2">
        <h1>Explore Warangal</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="warangal">
            <div class="container">
                <h2 class="section-title">Warangal: The Land of Kakatiya Heritage</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Warangal, located in Telangana, is a historical city known for its grand Kakatiya-era temples, forts, and lakes. Once the capital of the Kakatiya Dynasty (12th-14th century), Warangal is famous for its rich heritage, intricate architecture, and natural beauty.</p1>
                            
                            <h3>Top Attractions in Warangal</h3>
                            
                            <h4>1. Warangal Fort</h4>
                            <ul>
                                <li>Built by the Kakatiya kings, this 13th-century fort features massive stone gateways (Kirti Toranas) and intricate carvings</li>
                                <li>It is a UNESCO World Heritage Site nominee</li>
                                <li>Timings: 10:00 AM - 7:00 PM</li>
                            </ul>
                            
                            <h4>2. Thousand Pillar Temple</h4>
                            <ul>
                                <li>A stunning 12th-century temple dedicated to Shiva, Vishnu, and Surya</li>
                                <li>Famous for its intricate stone carvings, huge Nandi statue, and magnificent architecture</li>
                                <li>Timings: 6:00 AM - 8:00 PM</li>
                            </ul>
                            
                            <h4>3. Ramappa Temple (UNESCO Site)</h4>
                            <ul>
                                <li>Also known as the Ramalingeswara Temple, this Kakatiya-era temple is famous for its floating bricks and intricate sculptures</li>
                                <li>Declared a UNESCO World Heritage Site in 2021</li>
                                <li>Timings: 6:00 AM - 6:00 PM</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Best Time to Visit Warangal</h3>
                            <ul>
                                <li><strong>October - March:</strong> Pleasant weather for sightseeing</li>
                                <li><strong>Avoid peak summer (April - June):</strong> High temperatures</li>
                            </ul>
                            
                            <h3>How to Reach Warangal</h3>
                            <ul>
                                <li><strong>By Air:</strong> Rajiv Gandhi International Airport (HYD) (160 km from Warangal)</li>
                                <li><strong>By Train:</strong> Warangal and Kazipet Railway Stations connect to major cities</li>
                                <li><strong>By Road:</strong> Well-connected by NH 163, frequent buses from Hyderabad</li>
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
                                        <h3><i class="fas fa-bed"></i> Hotel Ashoka</h3>
                                        <p>Comfortable stay in the heart of Warangal city.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 870 257 8999</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Ratna Hotel</h3>
                                        <p>Budget-friendly option near major attractions.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 870 244 5678</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Haritha Kakatiya</h3>
                                        <p>Government-run hotel with good facilities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 870 244 1234</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,200/night</p>
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
                                        <h3><i class="fas fa-car"></i> Warangal City Cabs</h3>
                                        <p>Local taxi service for city tours and temple visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹150/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Kakatiya Travels</h3>
                                        <p>For sightseeing packages and outstation trips.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Ola/Uber</h3>
                                        <p>Available for local travel within Warangal.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43232</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹100/km</p>
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
        <h1>Explore Nagarjuna Sagar</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="nagarjuna-sagar">
            <div class="container">
                <h2 class="section-title">Nagarjuna Sagar: The Majestic Reservoir of South India</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Nagarjuna Sagar, located on the Krishna River, is one of the largest dams in India and a popular tourist destination in Telangana and Andhra Pradesh. Known for its scenic beauty, ancient Buddhist heritage, and engineering marvel, it is an ideal getaway for nature lovers, history enthusiasts, and adventure seekers.</p1>
                            
                            <h3>Top Attractions in Nagarjuna Sagar</h3>
                            
                            <h4>1. Nagarjuna Sagar Dam</h4>
                            <ul>
                                <li>Built in 1967, this is one of the largest masonry dams in the world</li>
                                <li>26 flood gates release water, creating a spectacular view, especially during monsoon</li>
                                <li>Best Time to Visit: July - October (when the dam gates are open)</li>
                            </ul>
                            
                            <h4>2. Nagarjunakonda Island & Museum</h4>
                            <ul>
                                <li>A Buddhist archaeological site, once a major learning center during the 3rd century BCE</li>
                                <li>Houses ancient Buddhist stupas, sculptures, and inscriptions</li>
                                <li>Reachable via a boat ride from Vijayapuri South</li>
                                <li>Timings: 9:30 AM - 4:00 PM (Closed on Fridays)</li>
                            </ul>
                            
                            <h4>3. Ethipothala Waterfall</h4>
                            <ul>
                                <li>A breathtaking 70-foot waterfall, located 11 km from the dam</li>
                                <li>A perfect spot for picnics, photography, and sunset views</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Best Time to Visit Nagarjuna Sagar</h3>
                            <ul>
                                <li><strong>Monsoon (July - October):</strong> The dam gates open, creating a spectacular sight</li>
                                <li><strong>Winter (November - February):</strong> Pleasant weather for sightseeing</li>
                            </ul>
                            
                            <h3>How to Reach Nagarjuna Sagar</h3>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport: Rajiv Gandhi International Airport, Hyderabad (150 km)</li>
                                <li><strong>By Train:</strong> Nearest railway station: Macherla (24 km)</li>
                                <li><strong>By Road:</strong> Well-connected by NH 565, with buses from Hyderabad, Vijayawada, and Guntur</li>
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
                                        <h3><i class="fas fa-bed"></i> Vijay Vihar Resort</h3>
                                        <p>Lakeside resort with beautiful views of the dam.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 8645 278899</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Haritha Dam View</h3>
                                        <p>Government-run hotel with excellent dam views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 8645 277788</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Nagarjuna Hotel</h3>
                                        <p>Budget accommodation near the dam site.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 8645 276655</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/night</p>
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
                                        <h3><i class="fas fa-car"></i> Nagarjuna Travels</h3>
                                        <p>Local taxi service for dam and waterfall visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Krishna Tours</h3>
                                        <p>For sightseeing packages and island tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> AP Tourism Cabs</h3>
                                        <p>Government-approved cabs for tourists.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43242</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
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
            <h2 class="section-title">Best Time to Visit</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Hyderabad</h3>
                        <ul>
                            <li>October - February: Pleasant weather for sightseeing</li>
                            <li>Avoid summer (March - June): Hot and dry conditions</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Warangal</h3>
                        <ul>
                            <li>October - March: Pleasant weather for sightseeing</li>
                            <li>Avoid peak summer (April - June): High temperatures</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Nagarjuna Sagar</h3>
                        <ul>
                            <li>Monsoon (July - October): The dam gates open, creating a spectacular sight</li>
                            <li>Winter (November - February): Pleasant weather for sightseeing</li>
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
                    <p>Rajiv Gandhi International Airport (HYD) serves Hyderabad with domestic and international flights.</p>
                    <p>Distance to Hyderabad city: 22 km</p>
                    <p>Distance to Warangal: 160 km</p>
                    <p>Distance to Nagarjuna Sagar: 150 km</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Hyderabad, Secunderabad, and Kacheguda stations are well-connected to major cities across India.</p>
                    <p>Warangal has its own railway station connecting to major cities</p>
                    <p>Macherla is the nearest station for Nagarjuna Sagar (24 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Telangana is well-connected via national highways and state roads.</p>
                    <p>Regular bus services from Hyderabad to all major destinations</p>
                    <p>Taxis and private vehicles can easily reach all attractions</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="route-wrapper">
        <h2 class="section-title" >🗺 Hyderabad to Nagarjuna Sagar Adventure Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Hyderabad
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🕌</div>
            Charminar
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Golconda Fort
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">❄</div>
            Snow World
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏙</div>
            Warangal
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Warangal Fort
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏛</div>
            Thousand Pillar Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🛕</div>
            Ramappa Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏞</div>
            Nagarjuna Sagar
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">💧</div>
            Nagarjuna Sagar Dam
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏝</div>
            Nagarjunakonda Island & Museum
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">💦</div>
            Ethipothala Waterfall
          </div>
        </div>
      
        <div class="path-label">
          Hyderabad → Charminar → Golconda Fort → Snow World → Warangal → Warangal Fort → Thousand Pillar Temple → Ramappa Temple → Nagarjuna Sagar → Nagarjuna Sagar Dam → Nagarjunakonda Island & Museum → Ethipothala Waterfall
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
            <div class="footer-content" style="text-align: center;">
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#cp1">Hyderabad</a></li>
                        <li><a href="#cp2">Warangal</a></li>
                        <li><a href="#cp3">Nagarjuna Sagar</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@telanganatourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Hyderabad, Telangana, India</li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                    </ul>
                </div>
            </div>
            
            <hr style="border: 1px solid #ccc; margin: 20px 0;">
            <div class="copyright" style="text-align: center;">
                <p>&copy; 2025 Telangana Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>