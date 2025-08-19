<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Bihar'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Bihar'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Bihar'";
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
    <title>Bihar Tourism - Discover the Land of Enlightenment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="State.css">
</head>
<body>
     
    <header style="background-image: url('IMAGE/biharcover.webp');">
        <div class="container">
            <h1 style="color:antiquewhite">Welcome to Bihar</h1>
            <p>Bihar, the land of enlightenment, is a state brimming with spirituality, ancient history, and cultural richness. From the sacred Bodh Gaya to the ruins of Nalanda University, Bihar offers experiences that touch the soul and ignite intellectual curiosity.</p>
            <p>Explore our guide to the must-visit destinations in this historically significant state.</p>
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
        <h1>Explore Bodh Gaya - The Land of Enlightenment</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="bodhgaya">
            <div class="container">
                <h2 class="section-title">Bodh Gaya - The Land of Enlightenment</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/bodhgaya.jpg" alt="Bodh Gaya">
                        </div>
                        <div class="card-content">
                            <h3>The Sacred Buddhist Site</h3>
                            <p1>Bodh Gaya, located in Bihar, is one of the holiest pilgrimage sites for Buddhists worldwide. It's where Prince Siddhartha Gautama attained enlightenment under the Bodhi Tree, becoming the Buddha over 2,500 years ago.</p1>
                            
                            <h4>Top Attractions in Bodh Gaya</h4>
                            <ul>
                                <li><strong>Mahabodhi Temple:</strong> UNESCO World Heritage Site marking Buddha's enlightenment spot</li>
                                <li><strong>Bodhi Tree:</strong> Descendant of the original tree where Buddha meditated</li>
                                <li><strong>Great Buddha Statue:</strong> 80-foot tall seated Buddha statue</li>
                                <li><strong>Dungeshwari Cave Temples:</strong> Where Buddha practiced severe asceticism</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/attractbodhgaya.webp" alt="Bodh Gaya Attractions">
                        </div>
                        <div class="card-content">
                            <h3>How to Reach Bodh Gaya</h3>
                            
                            <h4>By Air</h4>
                            <ul>
                                <li>The nearest airport is Gaya Airport (12 km from Bodh Gaya)</li>
                            </ul>
                            
                            <h4>By Train</h4>
                            <ul>
                                <li>Gaya Junction (16 km) is the nearest major railway station</li>
                            </ul>
                            
                            <h4>By Road</h4>
                            <ul>
                                <li>Well-connected to Patna, Varanasi, and other cities by buses and taxis</li>
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
                                        <h3><i class="fas fa-bed"></i> Royal Residency</h3>
                                        <p>Luxury hotel near Mahabodhi Temple with modern amenities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Bodhgaya</h3>
                                        <p>Comfortable accommodations with views of the temple complex.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Buddha Heritage</h3>
                                        <p>Budget-friendly option with spiritual ambiance.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,000/night</p>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Cabs Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-taxi"></i>
                                    <h3>Cab Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Bodh Gaya Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for temple visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Buddha Travels</h3>
                                        <p>Specialized service for pilgrimage tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Enlightenment Taxis</h3>
                                        <p>Knowledgeable drivers for Buddhist circuit tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
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
        <h1>Nalanda - Ancient Seat of Learning</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="nalanda">
            <div class="container">
                <h2 class="section-title">Nalanda University Ruins</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/nalanda.webp" alt="Nalanda">
                        </div>
                        <div class="card-content">
                            <h3>The World's First Residential University</h3>
                            <p1>Founded in the 5th century CE, Nalanda was one of the world's first residential universities and a renowned center of learning that attracted students from across Asia. The ruins now stand as a UNESCO World Heritage Site, showcasing India's glorious educational past.</p1>
                            
                            <h4>Top Attractions in Nalanda</h4>
                            <ul>
                                <li><strong>Nalanda University Ruins:</strong> Extensive remains of monasteries, temples, and lecture halls</li>
                                <li><strong>Nalanda Archaeological Museum:</strong> Houses artifacts excavated from the site</li>
                                <li><strong>Hiuen Tsang Memorial Hall:</strong> Dedicated to the famous Chinese scholar who studied here</li>
                                <li><strong>Nava Nalanda Mahavihara:</strong> Modern institute for Buddhist studies</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/nalandaAttract.jpg" alt="Nalanda Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Visiting Information</h3>
                            
                            <h4>Opening Hours</h4>
                            <ul>
                                <li>9:00 AM to 5:00 PM</li>
                                <li>Closed on Fridays</li>
                            </ul>
                            
                            <h4>Entry Fees</h4>
                            <ul>
                                <li>Indians: ₹25</li>
                                <li>Foreigners: ₹300</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Patna (90 km)</li>
                                <li><strong>By Train:</strong> Nearest station is Rajgir (12 km)</li>
                                <li><strong>By Road:</strong> Well connected from Patna, Bodh Gaya</li>
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
                                        <h3><i class="fas fa-bed"></i> Nalanda Regency</h3>
                                        <p>Comfortable hotel near the ruins with modern amenities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43270</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Nalanda</h3>
                                        <p>Mid-range option with good facilities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43271</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Heritage Inn</h3>
                                        <p>Budget-friendly accommodation near the archaeological site.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43272</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/night</p>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Cabs Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-taxi"></i>
                                    <h3>Cab Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Nalanda Cab Service</h3>
                                        <p>Reliable local taxi service for ruins visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43280</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Heritage Travels</h3>
                                        <p>Specialized service for historical tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43281</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Knowledge Taxis</h3>
                                        <p>For combined tours of Nalanda and Rajgir.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43282</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
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
        <h1>Patna - The Historic Capital</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="patna">
            <div class="container">
                <h2 class="section-title">Patna - The Historic Capital</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/patna.jpg" alt="Patna">
                        </div>
                        <div class="card-content">
                            <h3>City of Ancient Glory</h3>
                            <p1>Patna, the capital of Bihar, is one of the oldest continuously inhabited cities in the world with a history spanning over 2,500 years. Known in ancient times as Pataliputra, it was the capital of mighty empires like the Mauryas and Guptas.</p1>
                            
                            <h4>Top Attractions in Patna</h4>
                            <ul>
                                <li><strong>Patna Sahib Gurudwara:</strong> Birthplace of Guru Gobind Singh Ji</li>
                                <li><strong>Golghar:</strong> Historic granary with panoramic city views</li>
                                <li><strong>Patna Museum:</strong> Houses artifacts from Bihar's rich history</li>
                                <li><strong>Ganga Ghats:</strong> Serene riverfront for walks and rituals</li>
                                <li><strong>Mahavir Mandir:</strong> Famous temple dedicated to Lord Hanuman</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/patnaAttract.jpg" alt="Patna Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Best Time to Visit</h3>
                            
                            <h4>October to March</h4>
                            <ul>
                                <li>Pleasant weather (15°C to 25°C)</li>
                                <li>Ideal for sightseeing and cultural events</li>
                            </ul>
                            
                            <h4>Local Experiences</h4>
                            <ul>
                                <li>Try Bihari delicacies like litti-chokha, khaja, thekua</li>
                                <li>Shop for Madhubani paintings and handloom fabrics</li>
                                <li>Attend Chhath Puja on river banks (Oct-Nov)</li>
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
                                        <h3><i class="fas fa-bed"></i> Maurya Patna</h3>
                                        <p>Luxury hotel with excellent facilities in central Patna.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43290</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Chanakya</h3>
                                        <p>Comfortable mid-range option near tourist spots.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43291</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Patna Heritage</h3>
                                        <p>Budget-friendly hotel with good location.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43292</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Cabs Column -->
                            <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-taxi"></i>
                                    <h3>Cab Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Patna Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43300</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Capital Travels</h3>
                                        <p>Specialized service for city tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43301</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Heritage Taxis</h3>
                                        <p>For historical site visits in and around Patna.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43302</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
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
                        <h3>Bodh Gaya</h3>
                        <ul>
                            <li>October to March: Pleasant weather (10°C to 25°C)</li>
                            <li>December to February: Major Buddhist events</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Nalanda</h3>
                        <ul>
                            <li>November to February: Cool weather for exploring ruins</li>
                            <li>October to March: Avoid extreme summer heat</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Patna</h3>
                        <ul>
                            <li>October to March: Best for sightseeing (15°C to 25°C)</li>
                            <li>November: Experience Chhath Puja festivities</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Bihar</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Patna:</strong> Jay Prakash Narayan International Airport</p>
                    <p><strong>Gaya:</strong> Gaya Airport (for Bodh Gaya)</p>
                    <p>Connections to major Indian cities</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Patna Junction:</strong> Major railway hub with connections nationwide</p>
                    <p><strong>Gaya Junction:</strong> For Bodh Gaya visitors</p>
                    <p><strong>Rajgir Station:</strong> For Nalanda visitors</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected via national highways</p>
                    <p>Regular bus services from neighboring states</p>
                    <p>Good road connectivity between major cities</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="route-wrapper">
        <h2 class="section-title">🗺 Patna to Bodh Gaya Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Patna
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏛</div>
            Nalanda
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🛕</div>
            Bodh Gaya
          </div>
        </div>
      
        <div class="path-label">
          Patna → Nalanda → Bodh Gaya (or reverse if arriving differently)
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
                        <li><a href="#cp1">Bodh Gaya</a></li>
                        <li><a href="#cp2">Nalanda</a></li>
                        <li><a href="#cp3">Patna</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@bihartourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Patna, Bihar, India</li>
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
                <p>&copy; 2025 Bihar Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>