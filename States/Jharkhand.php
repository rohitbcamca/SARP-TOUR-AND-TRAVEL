<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Jharkhand'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Jharkhand'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Jharkhand'";
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
    <title>Jharkhand Tourism - Discover the Land of Forests and Waterfalls</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/jhcover.jpg');">
        <div class="container">
            <h1 style="color:aqua">Welcome to Jharkhand</h1>
            <p>Jharkhand, the land of forests and waterfalls, is a state brimming with natural wonders and spiritual significance. From the majestic Hundru Falls to the sacred Baidyanath Temple, it offers experiences that connect you with nature and divinity.</p>
            <p>Explore our guide to the must-visit destinations in this beautiful state.</p>
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
        <h1>Explore Hundru Falls</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="hundru-falls">
            <div class="container">
                <h2 class="section-title">Hundru Falls - The Majestic Cascade of Jharkhand</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/hundru.jpg" alt="Hundru Falls">
                        </div>
                        <div class="card-content">
                            <h3>The Natural Wonder</h3>
                            <p1>Located about 45 km from Ranchi, Hundru Falls is one of the most spectacular waterfalls in Jharkhand, formed by the Subarnarekha River plunging down from a height of 98 meters (322 feet). Surrounded by lush green forests and rocky terrain, it is a perfect getaway for nature lovers, photographers, and adventure seekers.</p1>
                            
                            <h4>What to Expect</h4>
                            <ul>
                                <li>A breathtaking cascade of water over rugged rocks</li>
                                <li>Natural pool at the base – great for photography and picnicking</li>
                                <li>Trek down about 700+ steps to reach the bottom – wear comfortable shoes!</li>
                                <li>Surrounded by dense forest and rock formations, ideal for nature walks</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/hundruattract.jpg" alt="Hundru Falls Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Visiting Timings</h4>
                            <ul>
                                <li><strong>Opening Time:</strong> 8:00 AM</li>
                                <li><strong>Closing Time:</strong> 5:00 PM</li>
                                <li><strong>Open on all days</strong> of the week</li>
                                <li>Best to visit early in the day to avoid the afternoon rush and enjoy cooler weather</li>
                            </ul>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Monsoon & post-monsoon (July to February)</strong> is the best time to see the waterfall in full glory</li>
                                <li>Avoid during heavy rains due to slippery paths and high water current</li>
                            </ul>
                            
                            <h4>Location</h4>
                            <ul>
                                <li><strong>District:</strong> Ranchi, Jharkhand</li>
                                <li><strong>Distance from Ranchi city:</strong> Approx. 45 km</li>
                                <li><strong>Via Road:</strong> Accessible through Ranchi-Purulia Road or via Ormanjhi–Sikidiri route</li>
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
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Royal Retreat</h3>
                                        <p>Comfortable stay near Hundru Falls with scenic views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Nature Valley Resort</h3>
                                        <p>Eco-friendly resort close to the waterfall.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Ranchi Grand</h3>
                                        <p>City hotel with easy access to Hundru Falls.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
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
                                        <h3><i class="fas fa-car"></i> Jharkhand Cab Service</h3>
                                        <p>Reliable taxi service for Hundru Falls trips.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bus"></i> Tourist Bus Service</h3>
                                        <p>Daily tours from Ranchi to Hundru Falls.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹500/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-hiking"></i> Guided Trekking Tours</h3>
                                        <p>Expert guides for waterfall exploration.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹1,000/tour</p>
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
        <h1>Baidyanath Temple, Deoghar</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="baidyanath-temple">
            <div class="container">
                <h2 class="section-title">Baidyanath Temple - The Abode of Lord Shiva</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/babadham.jpeg" alt="Baidyanath Temple">
                        </div>
                        <div class="card-content">
                            <h3>The Spiritual Powerhouse</h3>
                            <p1>Baidyanath Dham, also known as Baba Baidyanath Temple, is one of the 12 Jyotirlingas of Lord Shiva and a major pilgrimage site in India. Located in Deoghar, Jharkhand, it is believed that prayers offered here with pure devotion are always fulfilled. The temple holds deep spiritual and mythological significance and draws millions of devotees every year, especially during the Shravani Mela (July–August).</p1>
                            
                            <h4>Spiritual Significance</h4>
                            <ul>
                                <li>According to legend, Ravana worshipped Shiva here, offering his heads one by one. Shiva, pleased with his devotion, appeared as a healer (Vaidya) and cured him — hence the name Baidyanath.</li>
                                <li>This temple is also considered one of the 51 Shakti Peethas, where Sati's heart is believed to have fallen.</li>
                            </ul>
                            
                            <h4>Temple Highlights</h4>
                            <ul>
                                <li>The main temple (Shikhara) is a towering 72-foot-high structure with a gold pot atop it.</li>
                                <li>The sanctum houses the Jyotirlinga of Baba Baidyanath, a symbol of boundless energy and healing.</li>
                                <li>The complex also includes 21 other shrines dedicated to various deities like Parvati, Ganesha, Brahma, and Vishnu.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/babadhamAttract.webp" alt="Baidyanath Temple Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Temple Timings</h4>
                            <ul>
                                <li><strong>Opening Time:</strong> 4:00 AM</li>
                                <li><strong>Closing Time:</strong> 9:00 PM</li>
                                <li><strong>Darshan (Worship) Time:</strong> 4:00 AM – 3:30 PM & 6:00 PM – 9:00 PM</li>
                                <li>Rituals like Shringar, Abhishek, and Aarti are performed at different times throughout the day.</li>
                            </ul>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Shravan month (July–August)</strong> is the peak pilgrimage season; lakhs of Kanwariyas (devotees) walk barefoot with Ganga water from Sultanganj.</li>
                                <li><strong>October to March</strong> offers pleasant weather for sightseeing and temple visits.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo4">Local Services</button>
                </div>
                <div id="demo4" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Shiva Inn</h3>
                                        <p>Comfortable stay close to the temple complex.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43270</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Baidyanath Guest House</h3>
                                        <p>Managed by temple trust, ideal for pilgrims.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43271</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Deoghar Residency</h3>
                                        <p>Modern amenities with temple views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43272</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
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
                                        <h3><i class="fas fa-car"></i> Deoghar Cab Service</h3>
                                        <p>Reliable local taxi service for temple visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43280</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹150/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bus"></i> Pilgrim Bus Service</h3>
                                        <p>Regular buses from Jasidih station to temple.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43281</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹50/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-om"></i> Puja Services</h3>
                                        <p>Complete puja arrangements with priests.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43282</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹500/puja</p>
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
        <h1>Sun Temple (Surya Mandir)</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="sun-temple">
            <div class="container">
                <h2 class="section-title">Sun Temple - The Architectural Marvel</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/sun-temple-ranchi.jpg" alt="Sun Temple">
                        </div>
                        <div class="card-content">
                            <h3>The Celestial Wonder</h3>
                            <p1>The Sun Temple (Surya Mandir) near Ranchi, Jharkhand, is a stunning architectural marvel and a serene spiritual destination. Situated approximately 40 km from Ranchi on the Ranchi-Tata highway (NH-43), near Bundu, this temple is dedicated to Surya, the Sun God, and is a significant site for devotees and tourists alike.</p1>
                            
                            <h4>Architectural Highlights</h4>
                            <ul>
                                <li>The temple is uniquely designed in the shape of a gigantic chariot with 18 intricately carved wheels and seven majestic horses, symbolizing the Sun God's celestial vehicle.</li>
                                <li>This distinctive design sets it apart from other temples in the region.</li>
                                <li>The complex also houses shrines dedicated to Lord Shiva, Parvati, and Ganesha, offering a comprehensive spiritual experience.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/suntempleAttract.jpg" alt="Sun Temple Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visitor Information</h3>
                            
                            <h4>Visiting Hours</h4>
                            <ul>
                                <li><strong>Opening Time:</strong> 6:00 AM</li>
                                <li><strong>Closing Time:</strong> 7:30 PM</li>
                                <li><strong>Entry Fee:</strong> Free</li>
                                <li><strong>Time Required:</strong> 1 to 2 hours</li>
                            </ul>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li>The temple is open year-round, but the most auspicious and vibrant time to visit is during the Chhath Puja festival, when devotees gather to offer prayers to the Sun God.</li>
                                <li>The serene environment and the temple's hilltop location provide a peaceful retreat, especially during sunrise and sunset.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo5">Local Services</button>
                </div>
                <div id="demo5" class="collapse">
                    <div id="serviceSection">
                        <div class="local-services-columns">
                            <!-- Hotels Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hotel"></i>
                                    <h3>Recommended Hotels</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Sun View Resort</h3>
                                        <p>Scenic property with views of the temple.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43290</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Bundu Guest House</h3>
                                        <p>Budget-friendly option near the temple.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43291</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Ranchi Heritage</h3>
                                        <p>Comfortable stay with easy temple access.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43292</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
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
                                        <h3><i class="fas fa-car"></i> Sun Temple Taxis</h3>
                                        <p>Reliable service for temple visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43300</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bus"></i> Tourist Shuttle</h3>
                                        <p>Regular service from Ranchi to temple.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43301</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹100/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-camera"></i> Photography Services</h3>
                                        <p>Professional temple photography.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43302</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹500/session</p>
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
            <h2 class="section-title">Best Time to Visit Jharkhand</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Hundru Falls</h3>
                        <ul>
                            <li>Monsoon & post-monsoon (July to February)</li>
                            <li>Avoid during heavy rains due to slippery paths</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Baidyanath Temple</h3>
                        <ul>
                            <li>Shravan month (July–August) for spiritual experience</li>
                            <li>October to March for pleasant weather</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Sun Temple</h3>
                        <ul>
                            <li>Year-round, but especially during Chhath Puja</li>
                            <li>Sunrise and sunset for best views</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Jharkhand</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Ranchi:</strong> Birsa Munda Airport (serves all major destinations)</p>
                    <p><strong>Deoghar:</strong> Deoghar Airport (8 km from Baidyanath Temple)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Ranchi Junction:</strong> Well-connected to major cities</p>
                    <p><strong>Jasidih Junction:</strong> Nearest to Baidyanath Temple (7 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected via national highways and state roads</p>
                    <p>Regular bus services connect all major destinations</p>
                    <p>Suggested Route: Ranchi → Hundru Falls → Sun Temple → Deoghar</p>
                </div>
            </div>
        </div>
    </section>
    
<div class="route-wrapper">
    <h2 class="section-title">🗺 Ranchi Adventure Route</h2>
    
    <div class="pathway">
      <div class="step">
        <div class="emoji">🏙</div>
        Ranchi
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🌊</div>
        Hundru Falls
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">⛩</div>
        Sun Temple
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Deoghar (Baidyanath Temple)
      </div>
    </div>
  
    <div class="path-label">
      Ranchi → Hundru Falls → Sun Temple → Deoghar (Baidyanath Temple)
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
                        <li><a href="#cp1">Hundru Falls</a></li>
                        <li><a href="#cp2">Baidyanath Temple</a></li>
                        <li><a href="#cp3">Sun Temple</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@jharkhandtourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Ranchi, Jharkhand, India</li>
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
                <p>&copy; 2025 Jharkhand Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>