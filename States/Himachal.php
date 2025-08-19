<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Himachal Pradesh'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Himachal Pradesh'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Himachal Pradesh'";
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
    <title>Himachal Pradesh Tourism - Discover the Land of Hills</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="State.css">
</head>
<body>
     
    <header style="background-image: url('IMAGE/himachalcover1.jpg');">
        <div class="container">
            <h1>Welcome to Himachal Pradesh</h1>
            <p>Himachal Pradesh, nestled in the majestic Himalayas, is a state of breathtaking landscapes, serene hill stations, and vibrant culture. From the colonial charm of Shimla to the adventure paradise of Manali, Himachal offers experiences that rejuvenate the soul and thrill the senses.</p>
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
        <h1>Explore Shimla - The Queen of Hills</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="shimla">
            <div class="container">
                <h2 class="section-title">Shimla - The Queen of Hills</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/simlaAttract1.jpg" alt="Shimla">
                        </div>
                        <div class="card-content">
                            <h3>The Colonial Hill Station</h3>
                            <p1>Nestled in the majestic Himalayas, Shimla is the capital of Himachal Pradesh and one of India's most popular hill stations. Known as the "Queen of Hills," it is famous for its colonial charm, breathtaking landscapes, and pleasant climate.</p1>
                            
                            <h4>Top Attractions in Shimla</h4>
                            <ul>
                                <li><strong>The Ridge:</strong> The heart of Shimla, offering panoramic views of the Himalayas and home to Christ Church.</li>
                                <li><strong>Mall Road:</strong> The busiest shopping street lined with restaurants, cafes, and colonial-era buildings.</li>
                                <li><strong>Jakhoo Temple:</strong> A sacred temple dedicated to Lord Hanuman with a 108-feet tall statue.</li>
                                <li><strong>Kufri:</strong> A charming hill station famous for skiing, horse riding, and adventure parks.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Simla.jpg" alt="Shimla Attractions">
                        </div>
                        <div class="card-content">
                            <h3>How to Reach Shimla</h3>
                            
                            <h4>By Air</h4>
                            <ul>
                                <li>The nearest airport is Jubbarhatti Airport (23 km from Shimla)</li>
                            </ul>
                            
                            <h4>By Train</h4>
                            <ul>
                                <li>The Kalka-Shimla Toy Train offers a scenic and memorable journey</li>
                            </ul>
                            
                            <h4>By Road</h4>
                            <ul>
                                <li>Well-connected to Delhi, Chandigarh, and other cities via bus and taxi</li>
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
                                        <h3><i class="fas fa-bed"></i> The Oberoi Cecil</h3>
                                        <p>Luxury heritage hotel with colonial charm in the heart of Shimla.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Willow Banks</h3>
                                        <p>Comfortable accommodations with views of the Himalayas.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Snow Valley Resorts</h3>
                                        <p>Budget-friendly option near Mall Road with modern amenities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
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
                                        <h3><i class="fas fa-car"></i> Shimla Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Shimla area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Himalayan Travels</h3>
                                        <p>AC and non-AC cabs available for local sightseeing and airport transfers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Queen of Hills Taxis</h3>
                                        <p>Specialized service for tourists with knowledgeable drivers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹350/km</p>
                                    </div>
                                </div>
                            </div> -->
                        <!-- </div>
                    </div>
                </div> -->
            </div>
        </section>
    </div>
 
    <div class="collap"  id="cp2">
        <h1>Manali - A Paradise for Nature and Adventure Lovers</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="manali">
            <div class="container">
                <h2 class="section-title">Manali</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Manali.jpg" alt="Manali">
                        </div>
                        <div class="card-content">
                            <h3>The Adventure Capital</h3>
                            <p1>Nestled in the Himalayan mountains of Himachal Pradesh, Manali is one of India's most popular hill stations, known for its scenic beauty, adventure sports, and vibrant culture. With snow-capped peaks, lush valleys, and serene rivers, Manali is a favorite destination for honeymooners, backpackers, and adventure seekers.</p1>
                            
                            <h4>Top Attractions in Manali</h4>
                            <ul>
                                <li><strong>Solang Valley:</strong> A paradise for adventure lovers offering skiing, paragliding, zorbing, and ATV rides.</li>
                                <li><strong>Rohtang Pass:</strong> Located at 3,978 meters, offering breathtaking snow-covered landscapes.</li>
                                <li><strong>Hadimba Temple:</strong> A historic wooden temple dedicated to Goddess Hadimba, set amidst deodar forests.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/manaliAttract.jpg" alt="Manali Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Best Time to Visit Manali</h3>
                            
                            <h4>Summer (March to June)</h4>
                            <ul>
                                <li>Best for sightseeing, trekking, and adventure activities</li>
                            </ul>
                            
                            <h4>Winter (November to February)</h4>
                            <ul>
                                <li>Ideal for snowfall and winter sports</li>
                            </ul>
                            
                            <h4>Monsoon (July to September)</h4>
                            <ul>
                                <li>Lush green landscapes but occasional landslides</li>
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
                                        <h3><i class="fas fa-bed"></i> The Himalayan</h3>
                                        <p>Luxury resort with stunning mountain views in Manali.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43270</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹7,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Snow Valley Resorts</h3>
                                        <p>Comfortable accommodations near Solang Valley.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43271</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Apple Country Resort</h3>
                                        <p>Budget-friendly option with beautiful orchard views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43272</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
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
                                        <h3><i class="fas fa-car"></i> Manali Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Manali area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43280</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Adventure Travels</h3>
                                        <p>Specialized service for adventure activities and sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43281</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹350/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Himalayan Taxis</h3>
                                        <p>Knowledgeable drivers for Rohtang Pass and other high-altitude destinations.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43282</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹400/km</p>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <div class="collap"  id="cp3">
        <h1>Dharamshala - The Spiritual and Scenic Retreat</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="dharamshala">
            <div class="container">
                <h2 class="section-title">Dharamshala</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/dhramasala.jpg" alt="Dharamshala">
                        </div>
                        <div class="card-content">
                            <h3>The Tibetan Cultural Hub</h3>
                            <p1>Nestled in the Kangra Valley of Himachal Pradesh, Dharamshala is a serene hill station known for its Tibetan culture, Buddhist monasteries, and breathtaking landscapes. Divided into Lower Dharamshala (commercial hub) and Upper Dharamshala (McLeod Ganj), it serves as the residence of His Holiness the Dalai Lama and is a hub for spiritual seekers, nature lovers, and adventure enthusiasts.</p1>
                            
                            <h4>Top Attractions in Dharamshala</h4>
                            <ul>
                                <li><strong>McLeod Ganj:</strong> Also known as "Little Lhasa", it is the cultural heart of Dharamshala and home to the Dalai Lama Temple.</li>
                                <li><strong>Kangra Fort:</strong> The largest fort in the Himalayas with a history dating back to the 4th century BC.</li>
                                <li><strong>Dharamshala Cricket Stadium:</strong> One of the highest cricket stadiums in the world at 1,457 meters altitude.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/dhramsalaAttract.webp" alt="Dharamshala Attractions">
                        </div>
                        <div class="card-content">
                            <h3>How to Reach Dharamshala</h3>
                            
                            <h4>By Air</h4>
                            <ul>
                                <li>The nearest airport is Gaggal Airport (15 km from Dharamshala)</li>
                            </ul>
                            
                            <h4>By Train</h4>
                            <ul>
                                <li>The nearest railway station is Pathankot (85 km from Dharamshala)</li>
                            </ul>
                            
                            <h4>By Road</h4>
                            <ul>
                                <li>Well-connected to Delhi, Chandigarh, and Manali by buses and taxis</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- <div class="hot">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo5">Local Services</button>
                </div> -->
                <!-- <div id="demo5" class="collapse">
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
                                        <h3><i class="fas fa-bed"></i> Fortune Park Moksha</h3>
                                        <p>Luxury hotel with views of the Dhauladhar range in Dharamshala.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43290</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Tibet</h3>
                                        <p>Authentic Tibetan-style accommodations in McLeod Ganj.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43291</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Green Hotel</h3>
                                        <p>Eco-friendly budget option with beautiful mountain views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43292</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,000/night</p>
                                    </div>
                                </div>
                            </div>
                     -->
                            <!-- Cabs Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-taxi"></i>
                                    <h3>Cab Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Dharamshala Cab Service</h3>
                                        <p>Reliable local taxi service with fixed rates for Dharamshala area.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43300</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Tibetan Travels</h3>
                                        <p>Specialized service for monastery visits and cultural tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43301</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Mountain View Taxis</h3>
                                        <p>Knowledgeable drivers for Kangra Valley sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43302</p>
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
                        <h3>Shimla</h3>
                        <ul>
                            <li>Summer (April-June): Pleasant weather (15°C to 30°C)</li>
                            <li>Winter (December-February): For snowfall and winter sports</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Manali</h3>
                        <ul>
                            <li>Summer (March-June): Best for adventure sports (10°C to 25°C)</li>
                            <li>Winter (November-February): For snowfall and skiing (-5°C to 10°C)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Dharamshala</h3>
                        <ul>
                            <li>Summer (March-June): Best for sightseeing and trekking (15°C to 30°C)</li>
                            <li>Winter (October-February): For snowfall lovers (0°C to 15°C)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Himachal Pradesh</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p><strong>Shimla:</strong> Jubbarhatti Airport (23 km from city)</p>
                    <p><strong>Dharamshala:</strong> Gaggal Airport (15 km from city)</p>
                    <p><strong>Manali:</strong> Nearest airport is Bhuntar Airport (50 km)</p>
                    <p><strong>Kullu:</strong> Bhuntar Airport (10 km from Kullu town)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Shimla:</strong> Kalka-Shimla Toy Train (scenic mountain railway)</p>
                    <p><strong>Dharamshala:</strong> Nearest station is Pathankot (85 km)</p>
                    <p><strong>Manali:</strong> Nearest station is Joginder Nagar (165 km)</p>
                    <p><strong>Kullu:</strong> Nearest station is Joginder Nagar (125 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected via national highways and state roads</p>
                    <p>Regular bus services from Delhi, Chandigarh, and other major cities</p>
                    <p>Scenic road trips through mountain passes</p>
                    <p>Taxis and private vehicles can easily reach all destinations</p>
                </div>
            </div>
        </div>
    </section>

    <div class="route-wrapper">
        <h2 class="section-title">🗺 Delhi to Himachal Pradesh Adventure</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Delhi
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏔</div>
            Shimla
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">❄</div>
            Manali
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🌲</div>
            Kullu
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🧘‍♂</div>
            Dharamshala
          </div>
        </div>
      
        <div class="path-label">
          Delhi → Shimla → Manali → Kullu → Dharamshala
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
                        <li><a href="#cp1">Shimla</a></li>
                        <li><a href="#cp2">Manali</a></li>
                        <li><a href="#cp3">Dharamshala</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@himachaltourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Shimla, Himachal Pradesh, India</li>
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
                <p>&copy; 2025 Himachal Pradesh Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>