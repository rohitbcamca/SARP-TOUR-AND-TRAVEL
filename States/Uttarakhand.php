<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Uttarakhand'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Uttarakhand'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Uttarakhand'";
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
    <title>Uttarakhand Tourism - Discover the Land of Gods & Natural Wonders</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style= "background-image: url('IMAGE/utrakhandcover.jpg')">
        <div class="container">
            <h1>Welcome to Uttarakhand</h1>
            <p>Uttarakhand, the "Land of Gods," is a paradise of pristine Himalayan landscapes, sacred rivers, and spiritual destinations. From the serene lakes of Nainital to the yoga capital Rishikesh and the holy city of Haridwar, Uttarakhand offers a perfect blend of spirituality, adventure, and natural beauty.</p>
            <p>Explore our guide to the must-visit destinations in this divine state.</p>
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
        <h1>Nainital: The City of Lakes</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="nainital">
            <div class="container">
                <h2 class="section-title">Nainital</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Nainital.jpg" alt="Naini Lake">
                        </div>
                        <div class="card-content">
                            <h3>The Lake District of India</h3>
                            <p1>Nestled in the Kumaon Hills of Uttarakhand, Nainital is a charming hill station known for its pristine lakes, lush greenery, and breathtaking mountain views. Often called the "Lake District of India," Nainital is a popular getaway for nature lovers, honeymooners, and adventure seekers.</p1>
                            
                            <h4>Top Attractions</h4>
                            <ul>
                                <li><strong>Naini Lake:</strong> The heart of Nainital offering boating, yachting, and peaceful walks</li>
                                <li><strong>Naina Devi Temple:</strong> A sacred Hindu temple dedicated to Goddess Naina Devi</li>
                                <li><strong>Snow View Point:</strong> Panoramic views of Himalayan peaks</li>
                                <li><strong>Tiffin Top:</strong> Scenic viewpoint perfect for picnics</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/nainitalattract.jpg" alt="Nainital View">
                        </div>
                        <div class="card-content">
                            <h3>Visiting Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Summer (March to June):</strong> Pleasant weather for sightseeing and boating</li>
                                <li><strong>Monsoon (July to September):</strong> Lush green beauty but occasional landslides</li>
                                <li><strong>Winter (October to February):</strong> Snowfall and magical winter experience</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Pantnagar Airport (65 km)</li>
                                <li><strong>By Train:</strong> Kathgodam (34 km)</li>
                                <li><strong>By Road:</strong> Well-connected to Delhi, Dehradun, and Kathgodam</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo3">Local Services</button>
                </div> -->
               
                <!-- <div id="demo3" class="collapse">
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
                                        <h3><i class="fas fa-bed"></i> The Naini Retreat</h3>
                                        <p>Luxury heritage hotel with lake views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Lake View</h3>
                                        <p>Mid-range option with panoramic lake views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
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
                                        <h3><i class="fas fa-car"></i> Kumaon Travels</h3>
                                        <p>Reliable local taxi service for sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Himalayan Cabs</h3>
                                        <p>Specialized service for hill station tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹3,000/day</p>
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
        <h1>Rishikesh: The Yoga Capital</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="rishikesh">
            <div class="container">
                <h2 class="section-title">Rishikesh</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Rishikesh.jpg" alt="Rishikesh">
                        </div>
                        <div class="card-content">
                            <h3>The Yoga Capital of the World</h3>
                            <p1>Nestled in the foothills of the Himalayas along the sacred Ganges River, Rishikesh is a spiritual haven and adventure hub. Known as the "Yoga Capital of the World," this serene town attracts pilgrims, yogis, and thrill-seekers alike. Whether you're looking for peaceful meditation, thrilling river rafting, or breathtaking mountain views, Rishikesh offers an unforgettable experience.</p1>
                            
                            <h4>Top Attractions</h4>
                            <ul>
                                <li><strong>Laxman Jhula & Ram Jhula:</strong> Iconic suspension bridges over the Ganges</li>
                                <li><strong>Triveni Ghat:</strong> Sacred bathing spot with mesmerizing Ganga Aarti</li>
                                <li><strong>Neelkanth Mahadev Temple:</strong> Revered Shiva temple at 1,330 meters altitude</li>
                                <li><strong>Beatles Ashram:</strong> Historic meditation site of The Beatles</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/RishikeshAttract.jpg" alt="Ganga Aarti">
                        </div>
                        <div class="card-content">
                            <h3>Visiting Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Summer (March to June):</strong> Pleasant weather, great for adventure sports</li>
                                <li><strong>Monsoon (July to September):</strong> Lush greenery but rafting is closed</li>
                                <li><strong>Winter (October to February):</strong> Ideal for yoga and spiritual retreats</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Jolly Grant Airport, Dehradun (21 km)</li>
                                <li><strong>By Train:</strong> Haridwar (25 km)</li>
                                <li><strong>By Road:</strong> Well-connected to Delhi, Haridwar, and Dehradun</li>
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
                                        <h3><i class="fas fa-bed"></i> Ganga View Resort</h3>
                                        <p>Riverside accommodations with yoga facilities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Yoga Ashram Stay</h3>
                                        <p>Authentic spiritual experience with meals.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
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
                                        <h3><i class="fas fa-car"></i> Rishikesh Taxi Service</h3>
                                        <p>Reliable local taxi service with fixed rates.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Adventure Travels</h3>
                                        <p>Specialized service for rafting and trekking trips.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
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
    
    <div class="collap" id="cp3">
        <h1>Haridwar: Gateway to the Gods</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="haridwar">
            <div class="container">
                <h2 class="section-title">Haridwar</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/HaridwarTemple.png" alt="Haridwar">
                        </div>
                        <div class="card-content">
                            <h3>The Gateway to the Gods</h3>
                            <p1>Haridwar, meaning "Gateway to the Gods," is one of the holiest cities in India, situated on the banks of the sacred Ganges River in Uttarakhand. It is a spiritual and cultural hub, attracting millions of pilgrims who come to take a holy dip in the Ganges, seek blessings, and witness the divine Ganga Aarti at Har Ki Pauri.</p1>
                            
                            <h4>Top Attractions</h4>
                            <ul>
                                <li><strong>Har Ki Pauri:</strong> The most famous ghat with mesmerizing Ganga Aarti</li>
                                <li><strong>Chandi Devi Temple:</strong> Sacred temple on Neel Parvat Hill</li>
                                <li><strong>Mansa Devi Temple:</strong> Wish-fulfilling temple accessible by ropeway</li>
                                <li><strong>Shanti Kunj:</strong> Renowned center for Ayurveda and spirituality</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/HaridwarAttract.jpg" alt="Ganga Aarti Haridwar">
                        </div>
                        <div class="card-content">
                            <h3>Visiting Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Summer (March to June):</strong> Warm weather, ideal for temple visits</li>
                                <li><strong>Monsoon (July to September):</strong> Lush green surroundings but heavy rains</li>
                                <li><strong>Winter (October to February):</strong> Pleasant and perfect for exploring</li>
                                <li><strong>During Kumbh Mela:</strong> Held every 12 years (next in 2025)</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Jolly Grant Airport, Dehradun (35 km)</li>
                                <li><strong>By Train:</strong> Haridwar Railway Station (well-connected)</li>
                                <li><strong>By Road:</strong> Well-connected to Delhi, Rishikesh, and Dehradun</li>
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
                                        <h3><i class="fas fa-bed"></i> Ganga Lahari</h3>
                                        <p>Riverside hotel with views of Har Ki Pauri.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Haveli Hari Ganga</h3>
                                        <p>Heritage property with traditional architecture.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,000/night</p>
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
                                        <h3><i class="fas fa-car"></i> Haridwar Taxi Service</h3>
                                        <p>Reliable local taxi service for temple visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Pilgrim Cabs</h3>
                                        <p>Specialized service for religious tourism.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹2,500/day</p>
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
            <h2 class="section-title">Best Time to Visit Uttarakhand</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Summer (March to June)</h3>
                        <ul>
                            <li>Ideal for hill stations like Nainital and Mussoorie</li>
                            <li>Perfect for adventure activities in Rishikesh</li>
                            <li>Temperature ranges from 15°C to 30°C</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Monsoon (July to September)</h3>
                        <ul>
                            <li>Lush green landscapes throughout the state</li>
                            <li>Some areas may experience landslides</li>
                            <li>River rafting closed during peak monsoon</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Winter (October to February)</h3>
                        <ul>
                            <li>Snowfall in higher altitudes (Auli, Chopta)</li>
                            <li>Best for spiritual tourism in Haridwar and Rishikesh</li>
                            <li>Temperature can drop below 5°C in hills</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Uttarakhand</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Jolly Grant Airport (DED):</strong> Serves Dehradun, Rishikesh, Haridwar</p>
                    <p><strong>Pantnagar Airport (PGH):</strong> For Nainital and Kumaon region</p>
                    <p>Major airports nearby: Delhi (250 km from Haridwar)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Haridwar Junction:</strong> Major railhead for Char Dham pilgrims</p>
                    <p><strong>Kathgodam Station:</strong> Gateway to Nainital and Kumaon hills</p>
                    <p><strong>Dehradun Station:</strong> For Mussoorie and Garhwal region</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Excellent road network from Delhi (6-8 hours)</p>
                    <p>Regular bus services from major North Indian cities</p>
                    <p>Scenic mountain roads to hill stations</p>
                </div>
            </div>
        </div>
    </section>
    
    
<div class="route-wrapper">
    <h2 class="section-title">🗺 Nainital to Haridwar Adventure Route</h2>
    
    <div class="pathway">
      <div class="step">
        <div class="emoji">🏞</div>
        Nainital
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🌊</div>
        Naini Lake
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Naina Devi Temple
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">⛰</div>
        Rishikesh
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🌉</div>
        Laxman Jhula & Ram Jhula
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🌅</div>
        Triveni Ghat
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Neelkanth Mahadev Temple
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🌊</div>
        Haridwar
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Har Ki Pauri
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Chandi Devi Temple
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Mansa Devi Temple
      </div>
    </div>
  
    <div class="path-label">
      Nainital → Naini Lake → Naina Devi Temple → Rishikesh → Laxman Jhula & Ram Jhula → Triveni Ghat → Neelkanth Mahadev Temple → Haridwar → Har Ki Pauri → Chandi Devi Temple → Mansa Devi Temple
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
                        <li><a href="#cp1">Nainital</a></li>
                        <li><a href="#cp2">Rishikesh</a></li>
                        <li><a href="#cp3">Haridwar</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@uttarakhandtourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Dehradun, Uttarakhand, India</li>
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
                <p>&copy; 2025 Uttarakhand Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>