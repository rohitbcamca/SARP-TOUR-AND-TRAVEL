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
    <title>Delhi Tourism - Discover the Capital of India</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/delhicover.jpg');">
        <div class="container">
            <h1 style="color:white">Welcome to Delhi</h1>
            <p style>Delhi, the capital of India, is a city where ancient history and modern life coexist. From the iconic India Gate to the majestic Red Fort, Delhi offers a rich tapestry of historical landmarks, cultural experiences, and vibrant street life.</p>
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
        <h1>Explore India Gate</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
 <div id="demo" class="collapse">

    <section class="attractions" id="india-gate">
        <div class="container">
            <h2 class="section-title">India Gate: The Iconic War Memorial of India</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/indiagate.jpg" alt="India Gate">
                    </div>
                    <div class="card-content">
                        <h3>The Symbol of Sacrifice and Patriotism</h3>
                        <p1>Located in the heart of New Delhi, India Gate is one of the most famous landmarks in India. Built in 1931, this 42-meter-high arch-shaped monument was designed by Sir Edwin Lutyens as a tribute to the 90,000 Indian soldiers who lost their lives in World War I and the Third Anglo-Afghan War.</p1>
                        
                        <h4>History and Significance</h4>
                        <ul>
                            <li>Built as a war memorial to honor fallen Indian soldiers</li>
                            <li>Designed by renowned architect Sir Edwin Lutyens</li>
                            <li>Features the names of 13,300 servicemen inscribed on its walls</li>
                            <li>Hosts the Amar Jawan Jyoti (Flame of the Immortal Soldier) since 1971</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/indiagateattract.webp" alt="India Gate Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Visitor Information</h3>
                        
                        <h4>Best Time to Visit</h4>
                        <ul>
                            <li>Evening & Night (7:00 PM – 10:00 PM): Enjoy the illuminated monument and street food stalls</li>
                            <li>Winter Season (October to March): Ideal for sightseeing in pleasant weather</li>
                        </ul>
                        
                        <h4>Timings & Entry Details</h4>
                        <ul>
                            <li>Opening Hours: Open 24 hours (no entry restrictions)</li>
                            <li>Entry Fee: Free for all visitors</li>
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
                                <h3><i class="fas fa-bed"></i> The Imperial New Delhi</h3>
                                <p>Luxury heritage hotel near India Gate with colonial architecture.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 11 2344 1234</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹15,000/night</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-bed"></i> Taj Mahal Hotel</h3>
                                <p>Five-star hotel offering views of India Gate and Lutyens' Delhi.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 11 2302 6162</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹12,000/night</p>
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
                                <h3><i class="fas fa-car"></i> Delhi Cab Service</h3>
                                <p>Reliable local taxi service with fixed rates for tourist spots.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-car"></i> Metro Cabs</h3>
                                <p>AC and non-AC cabs available for local sightseeing.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </section>
</div>
 
    <div class="collap" id="cp2">
        <h1>Qutub Minar</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">

    <section class="attractions" id="qutub-minar">
        <div class="container">
            <h2 class="section-title">Qutub Minar: The Tallest Brick Minaret in the World</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/qutab-minarattract.jpg" alt="Qutub Minar">
                    </div>
                    <div class="card-content">
                        <h3>A UNESCO World Heritage Site</h3>
                        <p1>Qutub Minar is one of the most iconic landmarks of Delhi. Standing at a height of 72.5 meters (237.8 feet), it is the tallest brick minaret in the world and an outstanding example of Indo-Islamic architecture. Built in 1193 by Qutb-ud-din Aibak, the first ruler of the Delhi Sultanate, this majestic tower is surrounded by ancient ruins, tombs, and mosques.</p1>
                        
                        <h4>Historical Significance</h4>
                        <ul>
                            <li>Built to celebrate Muslim dominance in Delhi after the defeat of Delhi's last Hindu kingdom</li>
                            <li>Construction began by Qutb-ud-din Aibak and completed by his successor Iltutmish</li>
                            <li>The complex includes the Iron Pillar which has resisted rust for over 1600 years</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/qutubminar.jpg" alt="Qutub Minar Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Visitor Information</h3>
                        
                        <h4>Timings & Entry Details</h4>
                        <ul>
                            <li>Opening Hours: 7:00 AM - 10:00 PM (Open all days)</li>
                            <li>Entry Fee: ₹30 (Indian citizens), ₹500 (foreign tourists)</li>
                            <li>Free entry for children below 15 years</li>
                        </ul>
                        
                        <h4>Main Attractions in the Complex</h4>
                        <ul>
                            <li>The Qutub Minar tower with its intricate carvings</li>
                            <li>Quwwat-ul-Islam Mosque, the first mosque built in Delhi</li>
                            <li>The Iron Pillar of Chandragupta II</li>
                            <li>Alai Darwaza, the magnificent gateway</li>
                            <li>Tomb of Iltutmish</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- <div class="hot">
                <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo4">Local Services</button>
            </div> -->
            <!-- <div id="demo4" class="collapse">
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
                                    <h3><i class="fas fa-bed"></i> The Lodhi</h3>
                                    <p>Luxury hotel near Qutub Minar with modern amenities.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 11 4363 3333</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹20,000/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> The Qutub Hotel</h3>
                                    <p>Boutique hotel with views of Qutub Minar complex.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 11 2664 3888</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/night</p>
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
                                    <h3><i class="fas fa-car"></i> South Delhi Cabs</h3>
                                    <p>Specialized service for Qutub Minar area visitors.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Heritage Taxis</h3>
                                    <p>Drivers knowledgeable about Delhi's historical sites.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </section>
</div>
    
<div class="collap" id="cp3">
    <h1>Red Fort</h1>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
</div>

  <div id="demo2" class="collapse">
    <section class="attractions" id="red-fort">
        <div class="container">
            <h2 class="section-title">Red Fort: The Grand Mughal Fortress of Delhi</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/redattract.jpg" alt="Red Fort">
                    </div>
                    <div class="card-content">
                        <h3>A UNESCO World Heritage Site</h3>
                        <p1>Red Fort (Lal Qila) is one of India's most iconic landmarks. Built in 1648 by Mughal Emperor Shah Jahan, this majestic red sandstone fort served as the main residence of the Mughal emperors for nearly 200 years. Today, it stands as a symbol of India's rich history and independence, as the Prime Minister hoists the national flag here every Independence Day (August 15).</p1>
                        
                        <h4>Historical Significance</h4>
                        <ul>
                            <li>Built when Shah Jahan shifted his capital from Agra to Delhi</li>
                            <li>Served as the Mughal emperors' residence until 1857</li>
                            <li>Became a symbol of India's independence after 1947</li>
                            <li>Hosts the Prime Minister's Independence Day speech annually</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/redfort.jpg" alt="Red Fort Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Key Attractions of Red Fort</h3>
                        
                        <h4>1. Lahori Gate & Delhi Gate</h4>
                        <ul>
                            <li>Lahori Gate is the main entrance, where the Indian flag is hoisted every Independence Day</li>
                            <li>Delhi Gate, the second entrance, was used by Mughal royals</li>
                        </ul>
                        
                        <h4>2. Diwan-i-Aam & Diwan-i-Khas</h4>
                        <ul>
                            <li>Diwan-i-Aam (Hall of Public Audience): Where the emperor addressed the public</li>
                            <li>Diwan-i-Khas (Hall of Private Audience): A luxurious hall where the emperor met special guests</li>
                        </ul>
                        
                        <h4>3. Rang Mahal</h4>
                        <ul>
                            <li>The "Palace of Colors" was the residence of the emperor's wives and mistresses</li>
                            <li>Features a marble pool fed by the Nahr-i-Bihisht (Stream of Paradise)</li>
                        </ul>
                        
                        <h4>Visitor Information</h4>
                        <ul>
                            <li>Opening Hours: 9:30 AM – 4:30 PM (Closed on Mondays)</li>
                            <li>Entry Fee: ₹35 (Indian citizens), ₹500 (foreign tourists)</li>
                            <li>Free entry for children below 15 years</li>
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
                                    <h3><i class="fas fa-bed"></i> The Oberoi, Delhi</h3>
                                    <p>Luxury hotel near Red Fort with excellent service.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 11 2389 0606</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹18,000/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Hotel Broadway</h3>
                                    <p>Heritage property close to Red Fort and Chandni Chowk.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 11 4366 3600</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,000/night</p>
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
                                    <h3><i class="fas fa-car"></i> Old Delhi Taxis</h3>
                                    <p>Specialized service for Red Fort and Chandni Chowk area.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Heritage Cabs</h3>
                                    <p>Knowledgeable drivers familiar with Old Delhi's history.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
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
    
    <section class="best-time" id="best-time">
        <div class="container">
            <h2 class="section-title">Best Time to Visit</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>India Gate</h3>
                        <ul>
                            <li>Evening (7:00 PM – 10:00 PM) for illuminated views</li>
                            <li>Winter season (October to March) for pleasant weather</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Qutub Minar</h3>
                        <ul>
                            <li>Early morning or late afternoon to avoid crowds</li>
                            <li>October to March for comfortable sightseeing weather</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Red Fort</h3>
                        <ul>
                            <li>Morning hours to explore before it gets too crowded</li>
                            <li>October to March for pleasant weather conditions</li>
                            <li>August 15 (Independence Day) for special celebrations</li>
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
                    <p>Indira Gandhi International Airport serves Delhi with domestic and international flights.</p>
                    <p>Distance to India Gate: 15 km</p>
                    <p>Distance to Qutub Minar: 15 km</p>
                    <p>Distance to Red Fort: 20 km</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Delhi has several major railway stations connecting to all parts of India.</p>
                    <p>New Delhi Railway Station is closest to most attractions.</p>
                    <p>Metro connectivity available from all major stations.</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Delhi is well-connected via national highways and expressways.</p>
                    <p>Regular bus services from all neighboring states.</p>
                    <p>Taxis, auto-rickshaws, and metro available for local transport.</p>
                </div>
            </div>
        </div>
    </section>
    
    <body>

        <div class="route-wrapper">
          <h2 class="section-title" >🗺 Delhi Heritage Route</h2>
          
          <div class="pathway">
            <div class="step">
              <div class="emoji">🇮🇳</div>
              India Gate
            </div>
            <div class="arrow"></div>
            <div class="step">
              <div class="emoji">🕌</div>
              Qutub Minar
            </div>
            <div class="arrow"></div>
            <div class="step">
              <div class="emoji">🏰</div>
              Red Fort
            </div>
          </div>
        
          <div class="path-label">
            India Gate → Qutub Minar → Red Fort
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
                        <li><a href="#cp1">India Gate</a></li>
                        <li><a href="#cp2">Qutub Minar</a></li>
                        <li><a href="#cp3">Red Fort</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@delhitourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> New Delhi, India</li>
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
                <p>&copy; 2025 Delhi Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>