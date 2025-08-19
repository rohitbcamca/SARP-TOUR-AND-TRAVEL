<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Tamil Nadu'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Tamil Nadu'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Tamil Nadu'";
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
    <title>Tamil Nadu Tourism - Discover the Cultural Heart of South India</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet"  href="State.css">
</head>
<body>
     
    <header style= "background-image: url('IMAGE/tamilcover.webp')">
        <div class="container">
            <h1 style="color:chartreuse">Welcome to Tamil Nadu</h1>
            <p>Tamil Nadu, the cultural capital of South India, is a land of ancient temples, stunning beaches, and picturesque hill stations. From the vibrant streets of Chennai to the spiritual aura of Madurai and the serene beauty of Ooty, Tamil Nadu offers diverse experiences for every traveler.</p>
            <p>Explore our guide to the must-visit destinations in this magnificent state.</p>
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
        <h1>Explore Chennai</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#chennai">Know More</button>
    </div>
   
    <div id="chennai" class="collapse">
        <section class="attractions" id="chennai-attractions">
            <div class="container">
                <h2 class="section-title">Chennai: The Cultural Capital of South India</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/TamilNAdu.jpg" alt="Chennai">
                        </div>
                        <div class="card-content">
                            <h3>The Gateway to South India</h3>
                            <p1>Chennai, the capital of Tamil Nadu, is a vibrant city known for its rich history, classical music, Dravidian architecture, and stunning beaches. As one of India's oldest metropolitan cities, Chennai seamlessly blends tradition with modernity, making it a must-visit destination for history lovers, food enthusiasts, and beachgoers.</p1>
                            
                            <h4>Key Attractions in Chennai</h4>
                            <ul>
                                <li><strong>Marina Beach</strong>: India's longest beach, ideal for morning walks and sunset views</li>
                                <li><strong>Kapaleeshwarar Temple</strong>: A stunning Dravidian-style temple dedicated to Lord Shiva</li>
                                <li><strong>Fort St. George</strong>: India's first British fortress, now housing a museum</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/TamilNAduAttract.jpg" alt="Chennai Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Main Attractions in Chennai</h3>
                            
                            <h4>1. Marina Beach</h4>
                            <ul>
                                <li>India's longest beach and one of the most beautiful coastlines in the country</li>
                                <li>Ideal for morning walks, sunset views, and street food</li>
                                <li>Timings: Open 24 hours, best visited early morning or evening</li>
                            </ul>
                            
                            <h4>2. Kapaleeshwarar Temple</h4>
                            <ul>
                                <li>A stunning Dravidian-style temple dedicated to Lord Shiva</li>
                                <li>Famous for its intricate carvings and gopuram (tower)</li>
                                <li>Timings: 5:30 AM – 12:00 PM & 4:00 PM – 9:00 PM</li>
                            </ul>
                            
                            <h4>3. Fort St. George</h4>
                            <ul>
                                <li>India's first British fortress (built in 1644), now housing a museum</li>
                                <li>Displays colonial artifacts, ancient coins, and military relics</li>
                                <li>Timings: 9:00 AM – 5:00 PM (Closed on Fridays)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#chennai-services">Local Services</button>
                </div>
               
                <div id="chennai-services" class="collapse">
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
                                        <h3><i class="fas fa-bed"></i> Marina Beach View Hotel</h3>
                                        <p>This hotel offers stunning views of Marina Beach and comfortable accommodations.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Heritage Chennai</h3>
                                        <p>A boutique hotel located in the heart of Chennai, close to major attractions.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Temple Plaza</h3>
                                        <p>Modern hotel with rooftop restaurant offering views of the city.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43212</p>
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
                                        <h3><i class="fas fa-car"></i> Chennai Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Chennai area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Tamil Nadu Tourist Cabs</h3>
                                        <p>AC and non-AC cabs available for local sightseeing and airport transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Temple Taxis</h3>
                                        <p>Specialized service for temple visitors with knowledgeable drivers.</p>
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
        <h1>Explore Madurai</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#madurai">Know More</button>
    </div>
    
    <div id="madurai" class="collapse">
        <section class="attractions" id="madurai-attractions">
            <div class="container">
                <h2 class="section-title">Madurai: The Temple City of India</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Madurai.jpg" alt="Madurai">
                        </div>
                        <div class="card-content">
                            <h3>The Athens of the East</h3>
                            <p1>Madurai, one of the oldest continuously inhabited cities in the world, is often called the "Athens of the East." Located in Tamil Nadu, this city is famous for its rich history, stunning Dravidian architecture, vibrant culture, and mouthwatering street food. At the heart of Madurai lies the magnificent Meenakshi Amman Temple, a marvel of South Indian temple architecture.</p1>
                            
                            <h4>Key Attractions in Madurai</h4>
                            <ul>
                                <li><strong>Meenakshi Amman Temple</strong>: A stunning Dravidian-style temple dedicated to Goddess Meenakshi</li>
                                <li><strong>Thirumalai Nayakkar Mahal</strong>: A 17th-century palace blending Dravidian and Islamic styles</li>
                                <li><strong>Gandhi Memorial Museum</strong>: Showcasing Mahatma Gandhi's life and India's freedom struggle</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/MaduraiAttract.jpg" alt="Madurai Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Main Attractions in Madurai</h3>
                            
                            <h4>1. Meenakshi Amman Temple</h4>
                            <ul>
                                <li>A stunning Dravidian-style temple dedicated to Goddess Meenakshi (Parvati) and Lord Sundareswaran (Shiva)</li>
                                <li>Known for its colorful gopurams (towers), intricate sculptures, and a thousand-pillar hall</li>
                                <li>Timings: 5:00 AM – 12:30 PM & 4:00 PM – 9:30 PM</li>
                            </ul>
                            
                            <h4>2. Thirumalai Nayakkar Mahal</h4>
                            <ul>
                                <li>A 17th-century palace built by King Thirumalai Nayak</li>
                                <li>Features a blend of Dravidian and Islamic architectural styles</li>
                                <li>Timings: 9:00 AM – 5:00 PM</li>
                                <li>Light & Sound Show: 6:45 PM (Tamil), 7:45 PM (English)</li>
                            </ul>
                            
                            <h4>3. Gandhi Memorial Museum</h4>
                            <ul>
                                <li>A historical museum showcasing Mahatma Gandhi's life and India's freedom struggle</li>
                                <li>Displays Gandhi's bloodstained dhoti worn during his assassination</li>
                                <li>Timings: 10:00 AM – 1:00 PM & 2:00 PM – 5:30 PM (Closed on Fridays)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#madurai-services">Local Services</button>
                </div>
                <div id="madurai-services" class="collapse">
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
                                        <h3><i class="fas fa-bed"></i> Temple View Hotel</h3>
                                        <p>This hotel offers stunning views of Meenakshi Temple and comfortable accommodations.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Heritage Madurai</h3>
                                        <p>A boutique hotel located in the heart of Madurai, just minutes from Meenakshi Temple.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Nayak Palace</h3>
                                        <p>Modern hotel with rooftop restaurant offering views of the city.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43232</p>
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
                                        <h3><i class="fas fa-car"></i> Madurai Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Madurai area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Tamil Nadu Tourist Cabs</h3>
                                        <p>AC and non-AC cabs available for local sightseeing and airport transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Temple Taxis</h3>
                                        <p>Specialized service for temple visitors with knowledgeable drivers.</p>
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
    
    <div class="collap" id="cp3">
        <h1>Explore Ooty</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ooty">Know More</button>
    </div>
    
    <div id="ooty" class="collapse">
        <section class="attractions" id="ooty-attractions">
            <div class="container">
                <h2 class="section-title">Ooty: The Queen of Hill Stations</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Ooty.jpg" alt="Ooty">
                        </div>
                        <div class="card-content">
                            <h3>The Nilgiri Paradise</h3>
                            <p1>Ooty (Udhagamandalam), nestled in the Nilgiri Hills of Tamil Nadu, is a picturesque hill station known for its lush green landscapes, tea gardens, serene lakes, and colonial charm. Popular among honeymooners, nature lovers, and adventure seekers, Ooty offers a perfect escape from city life.</p1>
                            
                            <h4>Key Attractions in Ooty</h4>
                            <ul>
                                <li><strong>Ooty Lake & Boat House</strong>: A serene artificial lake surrounded by eucalyptus trees</li>
                                <li><strong>Nilgiri Mountain Railway</strong>: A UNESCO-listed heritage train offering stunning views</li>
                                <li><strong>Botanical Garden</strong>: Spread across 55 acres with exotic plants and orchids</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/OtyAttract.jpg" alt="Ooty Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Main Attractions in Ooty</h3>
                            
                            <h4>1. Ooty Lake & Boat House</h4>
                            <ul>
                                <li>A serene artificial lake surrounded by eucalyptus trees and green hills</li>
                                <li>Enjoy boating, cycling, and picnicking by the lake</li>
                                <li>Timings: 9:00 AM – 6:00 PM</li>
                            </ul>
                            
                            <h4>2. Nilgiri Mountain Railway (Toy Train)</h4>
                            <ul>
                                <li>A UNESCO-listed heritage train offering stunning views of tea plantations, tunnels, and waterfalls</li>
                                <li>Runs from Mettupalayam to Ooty (46 km journey, 5 hours)</li>
                                <li>Timings: 7:10 AM departure from Mettupalayam</li>
                            </ul>
                            
                            <h4>3. Botanical Garden</h4>
                            <ul>
                                <li>Spread across 55 acres, home to exotic plants, orchids, and a 20-million-year-old fossil tree</li>
                                <li>A paradise for nature lovers and photographers</li>
                                <li>Timings: 7:00 AM – 6:30 PM</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ooty-services">Local Services</button>
                </div>
                <div id="ooty-services" class="collapse">
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
                                        <h3><i class="fas fa-bed"></i> Lake View Hotel</h3>
                                        <p>This hotel offers stunning views of Ooty Lake and comfortable accommodations.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Heritage Ooty</h3>
                                        <p>A boutique hotel located in the heart of Ooty, with colonial charm.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Nilgiri Resort</h3>
                                        <p>Modern resort with spa facilities and mountain views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,200/night</p>
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
                                        <h3><i class="fas fa-car"></i> Ooty Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Ooty area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Nilgiri Travels</h3>
                                        <p>AC and non-AC cabs available for local sightseeing and airport transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Hill Taxis</h3>
                                        <p>Specialized service for hill station visitors with knowledgeable drivers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹350/km</p>
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
                        <h3>Chennai</h3>
                        <ul>
                            <li>Winter (November to February): The best time with pleasant weather</li>
                            <li>Summer (March to June): Hot and humid, but ideal for indoor attractions</li>
                            <li>Monsoon (July to October): Occasional rains, but greenery and beach views are stunning</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Madurai</h3>
                        <ul>
                            <li>Winter (October – March): Best time for sightseeing with pleasant weather</li>
                            <li>Summer (April – June): Very hot, but temple visits are still possible</li>
                            <li>Monsoon (July – September): Rain enhances the beauty of surrounding nature</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Ooty</h3>
                        <ul>
                            <li>Summer (March – June): Best time to escape the heat</li>
                            <li>Monsoon (July – September): Lush greenery, but occasional landslides</li>
                            <li>Winter (October – February): Cool weather, perfect for honeymooners</li>
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
                    <p><strong>Chennai:</strong> Chennai International Airport (MAA) connects to domestic and international cities</p>
                    <p><strong>Madurai:</strong> Madurai International Airport (IXM) (12 km from the city)</p>
                    <p><strong>Ooty:</strong> Coimbatore International Airport (88 km) is the nearest airport</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Chennai:</strong> Major railway stations: Chennai Central & Chennai Egmore</p>
                    <p><strong>Madurai:</strong> Madurai Junction is well-connected to major Indian cities</p>
                    <p><strong>Ooty:</strong> Nearest railway station: Mettupalayam (40 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p><strong>Chennai:</strong> Well-connected by national highways and buses</p>
                    <p><strong>Madurai:</strong> Regular buses from Chennai, Bangalore, and Coimbatore</p>
                    <p><strong>Ooty:</strong> Well-connected by buses and taxis from Bangalore, Coimbatore, and Mysore</p>
                </div>
            </div>
        </div>
    </section>
    <div class="route-wrapper">
        <h2 class="section-title">🗺 Chennai to Ooty Adventure Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Chennai
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏖</div>
            Marina Beach
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">⛪</div>
            Kapaleeshwarar Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Fort St. George
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏙</div>
            Madurai
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🛕</div>
            Meenakshi Amman Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Thirumalai Nayakkar Mahal
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏛</div>
            Gandhi Memorial Museum
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏞</div>
            Ooty
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🚤</div>
            Ooty Lake & Boat House
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🚂</div>
            Nilgiri Mountain Railway
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🌿</div>
            Botanical Garden
          </div>
        </div>
      
        <div class="path-label">
          Chennai → Marina Beach → Kapaleeshwarar Temple → Fort St. George → Madurai → Meenakshi Amman Temple → Thirumalai Nayakkar Mahal → Gandhi Memorial Museum → Ooty → Ooty Lake & Boat House → Nilgiri Mountain Railway → Botanical Garden
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
                        <li><a href="#cp1">Chennai</a></li>
                        <li><a href="#cp2">Madurai</a></li>
                        <li><a href="#cp3">Ooty</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@tamilnadutourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Chennai, Tamil Nadu, India</li>
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
                <p>&copy; 2025 Tamil Nadu Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>