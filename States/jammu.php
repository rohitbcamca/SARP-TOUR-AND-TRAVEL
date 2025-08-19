<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Jammu and Kashmir'";
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

// Fetch trekking services data
$trekking_query = "SELECT * FROM trekking_services WHERE state = 'Jammu and Kashmir'";
if (!empty($selected_city)) {
    $trekking_query .= " AND city = ?";
}
$trekking_query .= " ORDER BY service_type, name";

$trekking_services = [];
if ($stmt = $conn->prepare($trekking_query)) {
    if (!empty($selected_city)) {
        $stmt->bind_param("s", $selected_city);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trekking_services[] = $row;
    }
    $stmt->close();
}

// Fetch cab drivers data
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Jammu and Kashmir'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Jammu and Kashmir' UNION SELECT DISTINCT city FROM trekking_services WHERE state = 'Jammu and Kashmir'";
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
    <title>Jammu & Kashmir Tourism - Paradise on Earth</title>
    
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-section select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .service-item {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .service-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .service-type {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .service-details {
            margin-top: 10px;
        }
        .service-details p {
            margin-bottom: 5px;
        }
        .no-services {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
     
    <header style="background-image: url('IMAGE/jammucover.webp')">
        <div class="container">
            <h1>Welcome to Jammu & Kashmir</h1>
            <p>Jammu & Kashmir, often called "Paradise on Earth," is a land of breathtaking landscapes, sacred shrines, and adventure destinations. From the holy Vaishno Devi shrine to the snow-capped peaks of Gulmarg and the serene beauty of Pahalgam, this region offers unforgettable experiences.</p>
            <p>Explore our guide to the must-visit destinations in this heavenly region.</p>
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
        <h1>Explore Vaishno Devi</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="vaishno-devi">
            <div class="container">
                <h2 class="section-title">Vaishno Devi - A Sacred Journey to the Divine</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/VaishnoDevi.jpg" alt="Vaishno Devi">
                        </div>
                        <div class="card-content">
                            <h3>The Spiritual Powerhouse</h3>
                            <p1>Nestled in the majestic Trikuta Mountains of Jammu and Kashmir, Vaishno Devi is one of the most revered pilgrimage destinations in India. Dedicated to Mata Vaishno Devi, this sacred shrine attracts millions of devotees from across the world every year.</p1>
                            
                            <h4>Spiritual Significance</h4>
                            <ul>
                                <li>Vaishno Devi Temple is believed to be the holy abode of Maa Vaishno Devi, an incarnation of Goddess Durga.</li>
                                <li>Devotees embark on a 13-kilometer trek from Katra, chanting "Jai Mata Di" with unwavering faith.</li>
                            </ul>
                            
                            <h4>Trek Highlights</h4>
                            <ul>
                                <li>The trek starts from Banganga and passes through key points like Charan Paduka, Ardhkuwari, and Sanjichhat before reaching the Bhavan (main temple).</li>
                                <li>Average trek duration: 4-6 hours (one way).</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/VaishnoDeviAttract.jpg" alt="Vaishno Devi Trek">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li>Open year-round, but best from March to October.</li>
                                <li>Navratri festival sees grand celebrations.</li>
                                <li>Avoid monsoon season (July-August) due to slippery paths.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Jammu Airport (Satwari Airport), about 50 km from Katra.</li>
                                <li><strong>By Train:</strong> The Shri Mata Vaishno Devi Katra Railway Station connects Katra to major Indian cities.</li>
                                <li><strong>By Road:</strong> Katra is well-connected by road to Jammu, Delhi, and other major cities via buses and taxis.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo3">Trekking Services</button>
                </div>
               
                <div id="demo3" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Atrium on the Greens</h3>
                                        <p>Luxury hotel with mountain views near the base camp.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Asia Vaishno Devi</h3>
                                        <p>Comfortable stay with easy access to the trek starting point.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Trekking Services Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hiking"></i>
                                    <h3>Trekking Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-horse"></i> Pony Services</h3>
                                        <p>Pony rides available for the trek from Banganga to Bhawan.</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/ride</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-chair"></i> Palanquin Services</h3>
                                        <p>Palanquin services for those who cannot walk the trek.</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,000/ride</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-helicopter"></i> Helicopter Services</h3>
                                        <p>Helicopter service operates from Katra to Sanjichhat.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43212</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,800/person</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
 
    <div class="collap" id="cp2">
        <h1>Explore Leh-Ladakh</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="leh-ladakh">
            <div class="container">
                <h2 class="section-title">Leh-Ladakh - The Land of High Passes</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Leh.jpg" alt="Leh-Ladakh">
                        </div>
                        <div class="card-content">
                            <h3>The Adventure Paradise</h3>
                            <p1>Nestled in the Himalayas, Leh-Ladakh is a paradise for travelers seeking breathtaking landscapes, adventure, and spirituality. Known for its snow-capped peaks, pristine lakes, ancient monasteries, and rugged terrain.</p1>
                            
                            <h4>Top Attractions</h4>
                            <ul>
                                <li><strong>Pangong Lake:</strong> A mesmerizing high-altitude lake (4,350m) that changes colors from blue to green.</li>
                                <li><strong>Nubra Valley:</strong> Known as the Valley of Flowers, featuring scenic landscapes and sand dunes.</li>
                                <li><strong>Khardung La Pass:</strong> One of the highest motorable roads in the world at 5,359m.</li>
                                <li><strong>Magnetic Hill:</strong> A mysterious spot where vehicles appear to move uphill on their own.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/LehLadakh.jpg" alt="Ladakh Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li>Best from May to September when roads are open.</li>
                                <li>Winter months are extremely cold with many closures.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Leh Airport connects to major Indian cities (flights operate May-Oct).</li>
                                <li><strong>By Road:</strong> Two main routes - Manali-Leh Highway (open June-Sept) and Srinagar-Leh Highway (open May-Nov).</li>
                                <li>Foreign tourists require Inner Line Permit (ILP) to visit certain areas.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo4">Adventures Tour</button>
                </div>
                <div id="demo4" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Grand Dragon Ladakh</h3>
                                        <p>Luxury hotel with traditional Ladakhi architecture.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Pangong Resort</h3>
                                        <p>Lakeside accommodation near Pangong Lake.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,000/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Adventure Tours Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hiking"></i>
                                    <h3>Adventure Tours</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-motorcycle"></i> Ladakh Bike Tours</h3>
                                        <p>Motorcycle tours covering Khardung La and other high passes.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43222</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-campground"></i> Pangong Camping</h3>
                                        <p>Overnight camping experience at Pangong Lake.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43223</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-camel"></i> Nubra Valley Camel Safari</h3>
                                        <p>Camel rides in the sand dunes of Nubra Valley.</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹500/ride</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <div class="collap" id="cp3">
        <h1>Explore Gulmarg</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="gulmarg">
            <div class="container">
                <h2 class="section-title">Gulmarg - The Winter Wonderland</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/gulmarg.jpg" alt="Gulmarg">
                        </div>
                        <div class="card-content">
                            <h3>The Skiing Capital of India</h3>
                            <p1>Located in the Baranuilla district of Jammu and Kashmir, Gulmarg is a breathtaking hill station known for its snow-covered peaks, lush green meadows, and world-class skiing facilities. Often called the "Meadow of Flowers," Gulmarg is a year-round destination.</p1>
                            
                            <h4>Top Attractions</h4>
                            <ul>
                                <li><strong>Gulmarg Gondola:</strong> Asia's highest cable car reaching 3,980 meters with stunning views.</li>
                                <li><strong>Apharwat Peak:</strong> A snow-covered paradise for skiing and snowboarding.</li>
                                <li><strong>Gulmarg Golf Course:</strong> One of the highest golf courses in the world at 2,650 meters.</li>
                                <li><strong>Alpather Lake:</strong> Frozen lake surrounded by snow-capped peaks.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/GUlmargAttract.jpg" alt="Gulmarg Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Winter (December-February):</strong> For snowfall and winter sports.</li>
                                <li><strong>Summer (April-June):</strong> For pleasant weather and lush greenery.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Srinagar International Airport (56 km away).</li>
                                <li><strong>By Road:</strong> Well-connected by road from Srinagar (2 hours drive).</li>
                                <li><strong>By Train:</strong> Nearest railway station is Jammu Tawi (290 km away).</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo5">Adventures Activity List</button>
                </div>
                <div id="demo5" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Khyber Himalayan Resort & Spa</h3>
                                        <p>Luxury resort with ski-in/ski-out access.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹12,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Highlands Park</h3>
                                        <p>Heritage property with views of the golf course.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹7,500/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Adventure Activities Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-skiing"></i>
                                    <h3>Winter Sports</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-skiing"></i> Skiing Lessons</h3>
                                        <p>Beginner to advanced skiing lessons at Apharwat Peak.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43242</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-snowboarding"></i> Snowboarding</h3>
                                        <p>Snowboarding equipment rental and guides.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43243</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,000/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-golf-ball"></i> Golf Packages</h3>
                                        <p>Green fees and equipment rental at Gulmarg Golf Course.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43244</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,000/day</p>
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
            <h2 class="section-title">Best Time to Visit</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Vaishno Devi</h3>
                        <ul>
                            <li>Open year-round, but best from March to October.</li>
                            <li>Navratri festival sees grand celebrations.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Leh-Ladakh</h3>
                        <ul>
                            <li>Best from May to September when roads are open.</li>
                            <li>Winter months are extremely cold with many closures.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Gulmarg</h3>
                        <ul>
                            <li>Winter (December-February) for snowfall and winter sports.</li>
                            <li>Summer (April-June) for pleasant weather and lush greenery.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Jammu & Kashmir</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Jammu Airport:</strong> For Vaishno Devi pilgrims</p>
                    <p><strong>Srinagar Airport:</strong> For Gulmarg/Pahalgam</p>
                    <p><strong>Leh Airport:</strong> For Ladakh region</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Jammu Tawi:</strong> Main railway station for Jammu region</p>
                    <p><strong>Katra Station:</strong> For Vaishno Devi pilgrims</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected by national highways from Delhi, Chandigarh</p>
                    <p>NH44 connects Jammu to Srinagar</p>
                    <p>Manali-Leh and Srinagar-Leh highways (seasonal)</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="route-wrapper">
        <h2 class="section-title">Jammu & Kashmir Adventure Route</h2>
        
        <div class="pathway">
            <div class="step">
                <div class="emoji">🏔</div>
                Jammu
            </div>
            <div class="arrow"></div>
            <div class="step">
                <div class="emoji">🛐</div>
                Vaishno Devi
            </div>
            <div class="arrow"></div>
            <div class="step">
                <div class="emoji">🚡</div>
                Gulmarg
            </div>
             
            <div class="arrow"></div>
            <div class="step">
                <div class="emoji">⛰</div>
                Leh-Ladakh
            </div>
        </div>
    
        <div class="path-label">
            Jammu → Vaishno Devi → Gulmarg → Pahalgam → Leh-Ladakh
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
                        <li><a href="#cp1">Vaishno Devi</a></li>
                        <li><a href="#cp2">Leh-Ladakh</a></li>
                        <li><a href="#cp3">Gulmarg</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@jktourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Srinagar, Jammu & Kashmir</li>
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
                <p>&copy; 2025 Jammu & Kashmir Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>