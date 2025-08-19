<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'West Bengal'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'West Bengal'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'West Bengal'";
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
    <title>West Bengal Tourism - Discover the Cultural Heart of India</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style= "background-image: url('IMAGE/westcover.jpg')">
        <div class="container">
            <h1>Welcome to West Bengal</h1>
            <p>West Bengal, the cultural heart of India, is a land of diverse experiences - from the bustling streets of Kolkata to the serene tea gardens of Darjeeling and the historical treasures of Murshidabad. Explore our guide to the must-visit destinations in this vibrant state.</p>
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
        <h1>Explore Kolkata</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="kolkata">
            <div class="container">
                <h2 class="section-title">Kolkata: The City of Joy & Culture</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Kolkata, the capital of West Bengal, is a city where history, art, literature, and modernity blend seamlessly. Known as the Cultural Capital of India, it boasts colonial-era architecture, vibrant festivals, delicious food, and literary heritage.</p1>
                            
                            <h3>Top Attractions in Kolkata</h3>
                            
                            <h4>1. Victoria Memorial</h4>
                            <ul>
                                <li>A stunning white marble monument dedicated to Queen Victoria</li>
                                <li>Features a museum with British-era artifacts, paintings, and statues</li>
                                <li>Timings: 10:00 AM – 5:00 PM (Closed on Mondays)</li>
                            </ul>
                            
                            <h4>2. Howrah Bridge</h4>
                            <ul>
                                <li>An iconic cantilever bridge over the Hooghly River</li>
                                <li>Best visited in the evening for a breathtaking view with city lights</li>
                            </ul>
                            
                            <h4>3. Dakshineswar Kali Temple</h4>
                            <ul>
                                <li>A famous Hindu temple dedicated to Goddess Kali</li>
                                <li>Built in 1855, it is associated with Sri Ramakrishna Paramahansa</li>
                                <li>Timings: 6:00 AM – 12:30 PM & 3:00 PM – 8:30 PM</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Famous Food of Kolkata</h3>
                            <ul>
                                <li><strong>Kolkata Biryani</strong> – Aromatic rice with potatoes and juicy meat</li>
                                <li><strong>Phuchka (Pani Puri)</strong> – A spicy street food delight</li>
                                <li><strong>Rosogolla & Mishti Doi</strong> – Famous Bengali sweets</li>
                                <li><strong>Kathi Rolls</strong> – Flaky parathas stuffed with kebabs and chutney</li>
                                <li><strong>Macher Jhol</strong> – Traditional Bengali fish curry</li>
                            </ul>
                            
                            <h3>Best Time to Visit Kolkata</h3>
                            <ul>
                                <li><strong>October – March:</strong> Pleasant weather, best for sightseeing</li>
                                <li><strong>Durga Puja (September – October):</strong> The city comes alive with grand celebrations</li>
                                <li><strong>Avoid peak summer (April – June):</strong> Hot and humid climate</li>
                            </ul>
                            
                            <h3>How to Reach Kolkata</h3>
                            <ul>
                                <li><strong>By Air:</strong> Netaji Subhas Chandra Bose International Airport (CCU) connects to major cities</li>
                                <li><strong>By Train:</strong> Major railway stations: Howrah Junction & Sealdah</li>
                                <li><strong>By Road:</strong> Well-connected by NH 16 & NH 19, with buses from nearby cities</li>
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
                                        <h3><i class="fas fa-bed"></i> The Oberoi Grand</h3>
                                        <p>Luxury heritage hotel in central Kolkata.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 33 2249 2323</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> ITC Sonar</h3>
                                        <p>5-star hotel with excellent dining options.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 33 2345 4545</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹7,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> The Park Kolkata</h3>
                                        <p>Modern hotel with great location.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 33 2249 3121</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,500/night</p>
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
                                        <h3><i class="fas fa-car"></i> Kolkata Yellow Taxis</h3>
                                        <p>Iconic yellow cabs for city travel.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Uber/Ola</h3>
                                        <p>App-based cab services available throughout Kolkata.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹150/km</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Kolkata City Tours</h3>
                                        <p>Guided tours with professional drivers.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43212</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹1,500/day</p>
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
        <h1>Explore Darjeeling</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="darjeeling">
            <div class="container">
                <h2 class="section-title">Darjeeling: The Queen of the Hills</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Nestled in the foothills of the Himalayas, Darjeeling is one of India's most picturesque hill stations, known for its tea plantations, colonial charm, and breathtaking mountain views. With the majestic Kanchenjunga as its backdrop, Darjeeling offers a perfect blend of nature, adventure, and cultural richness.</p1>
                            
                            <h3>Top Attractions in Darjeeling</h3>
                            
                            <h4>1. Tiger Hill</h4>
                            <ul>
                                <li>Famous for its stunning sunrise views over the Kanchenjunga and Mount Everest</li>
                                <li>Best visited early in the morning (around 4:30 AM in summer, 5:30 AM in winter)</li>
                            </ul>
                            
                            <h4>2. Darjeeling Himalayan Railway (Toy Train)</h4>
                            <ul>
                                <li>A UNESCO World Heritage Site, offering a joy ride from Darjeeling to Ghum</li>
                                <li>The train ride offers breathtaking views of mountains, valleys, and tea gardens</li>
                                <li>Timings: Multiple departures daily from Darjeeling station</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Famous Food of Darjeeling</h3>
                            <ul>
                                <li><strong>Momos</strong> – Steamed or fried dumplings with spicy chutney</li>
                                <li><strong>Thukpa</strong> – A flavorful Tibetan noodle soup</li>
                                <li><strong>Darjeeling Tea</strong> – World-famous, aromatic tea</li>
                                <li><strong>Shaphalay</strong> – A Tibetan stuffed bread</li>
                                <li><strong>Churpi</strong> – Local Himalayan cheese</li>
                            </ul>
                            
                            <h3>Best Time to Visit Darjeeling</h3>
                            <ul>
                                <li><strong>March – June:</strong> Pleasant summer weather with clear skies</li>
                                <li><strong>September – December:</strong> Crisp autumn and winter views of Kanchenjunga</li>
                                <li><strong>Avoid Monsoon (July – August):</strong> Due to heavy rains and landslides</li>
                            </ul>
                            
                            <h3>How to Reach Darjeeling</h3>
                            <ul>
                                <li><strong>By Air:</strong> Bagdogra Airport (IXB) (70 km away), followed by a taxi or shared jeep</li>
                                <li><strong>By Train:</strong> New Jalpaiguri (NJP) Railway Station (75 km away), then take a toy train or taxi</li>
                                <li><strong>By Road:</strong> Well-connected by road from Siliguri, Kalimpong, and Gangtok</li>
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
                                        <h3><i class="fas fa-bed"></i> Mayfair Darjeeling</h3>
                                        <p>Luxury heritage hotel with colonial charm.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 354 225 6378</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹9,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Cedar Inn</h3>
                                        <p>Comfortable stay with mountain views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 354 225 4123</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Seven Seventeen</h3>
                                        <p>Budget-friendly option near Mall Road.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 354 225 6789</p>
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
                                        <h3><i class="fas fa-car"></i> Darjeeling Taxi Union</h3>
                                        <p>Local taxi service for sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹1,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Himalayan Travels</h3>
                                        <p>For Tiger Hill sunrise trips and longer tours.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹2,000/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Shared Jeeps</h3>
                                        <p>Economical shared rides to nearby attractions.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43222</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/person</p>
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
        <h1>Explore Murshidabad</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="murshidabad">
            <div class="container">
                <h2 class="section-title">Murshidabad: A Journey Through Bengal's Royal Past</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-content">
                            <p1>Located on the banks of the Bhagirathi River in West Bengal, Murshidabad is a city steeped in history, heritage, and architectural splendor. Once the capital of Bengal under the Nawabs, Murshidabad is a treasure trove of Mughal-era palaces, gardens, and tombs.</p1>
                            
                            <h3>Top Attractions in Murshidabad</h3>
                            
                            <h4>1. Hazarduari Palace (Palace of a Thousand Doors)</h4>
                            <ul>
                                <li>The crown jewel of Murshidabad, built in 1837 by Duncan MacLeod for Nawab Nazim Humayun Jah</li>
                                <li>Now a museum with a collection of weapons, paintings, royal furniture, manuscripts, and artifacts</li>
                                <li>Known for its 1,000 doors (real and fake), grand chandeliers, and vast Durbar Hall</li>
                                <li>Timings: 9:00 AM – 5:00 PM (Closed on Fridays)</li>
                            </ul>
                            
                            <h4>2. Nizamat Imambara</h4>
                            <ul>
                                <li>Located just opposite the Hazarduari Palace, it is one of the largest Shia Muslim congregation halls in India</li>
                                <li>Built in 1847 after the original was destroyed by fire</li>
                                <li>Features beautiful Mughal architecture and is especially active during Muharram</li>
                            </ul>
                            
                            <h4>3. Katra Masjid</h4>
                            <ul>
                                <li>A massive mosque built by Murshid Quil Khan, the founder of Murshidabad</li>
                                <li>It served as both a mosque and a madrasa (Islamic school)</li>
                                <li>The Nawab's tomb lies at the entrance of the mosque, symbolizing humility</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3>Best Time to Visit Murshidabad</h3>
                            <ul>
                                <li><strong>October to March:</strong> Pleasant weather for sightseeing</li>
                                <li><strong>Avoid peak summer (April–June):</strong> Due to high temperatures</li>
                                <li><strong>Muharram and Jhulan Yatra:</strong> Offer unique cultural experiences</li>
                            </ul>
                            
                            <h3>How to Reach Murshidabad</h3>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Netaji Subhas Chandra Bose International Airport (Kolkata) – approx. 200 km</li>
                                <li><strong>By Train:</strong> Murshidabad Railway Station and Berhampore Court Station are well connected to Kolkata and other cities</li>
                                <li><strong>By Road:</strong> Easily accessible via NH-34, buses and taxis available from Kolkata and Siliguri</li>
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
                                        <h3><i class="fas fa-bed"></i> The Bari Kothi</h3>
                                        <p>Heritage property with royal ambiance.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 3482 270123</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Hotel Manjusha</h3>
                                        <p>Comfortable stay near Hazarduari Palace.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 3482 270456</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,200/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Murshidabad Tourist Lodge</h3>
                                        <p>Budget accommodation with basic amenities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 3482 270789</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,800/night</p>
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
                                        <h3><i class="fas fa-car"></i> Murshidabad Tours</h3>
                                        <p>Local taxi service for heritage sightseeing.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹1,200/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Heritage Cabs</h3>
                                        <p>Guided tours of historical sites.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹1,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Auto Rickshaws</h3>
                                        <p>Economical option for short distances.</p>
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
    
    <section class="best-time" id="best-time">
        <div class="container">
            <h2 class="section-title">Best Time to Visit</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Kolkata</h3>
                        <ul>
                            <li>October – March: Pleasant weather, best for sightseeing</li>
                            <li>Durga Puja (September – October): Grand celebrations</li>
                            <li>Avoid peak summer (April – June): Hot and humid</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Darjeeling</h3>
                        <ul>
                            <li>March – June: Pleasant summer weather with clear skies</li>
                            <li>September – December: Crisp autumn and winter views</li>
                            <li>Avoid Monsoon (July – August): Heavy rains and landslides</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Murshidabad</h3>
                        <ul>
                            <li>October to March: Pleasant weather for sightseeing</li>
                            <li>Avoid peak summer (April–June): High temperatures</li>
                            <li>Muharram and Jhulan Yatra: Unique cultural experiences</li>
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
                    <p><strong>Kolkata:</strong> Netaji Subhas Chandra Bose International Airport (CCU)</p>
                    <p><strong>Darjeeling:</strong> Bagdogra Airport (IXB) - 70 km away</p>
                    <p><strong>Murshidabad:</strong> Nearest airport is Kolkata (200 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p><strong>Kolkata:</strong> Howrah Junction & Sealdah stations</p>
                    <p><strong>Darjeeling:</strong> New Jalpaiguri (NJP) - 75 km away</p>
                    <p><strong>Murshidabad:</strong> Murshidabad Railway Station and Berhampore Court Station</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p><strong>Kolkata:</strong> Well-connected by NH 16 & NH 19</p>
                    <p><strong>Darjeeling:</strong> Well-connected by road from Siliguri, Kalimpong, and Gangtok</p>
                    <p><strong>Murshidabad:</strong> Easily accessible via NH-34 from Kolkata</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="route-wrapper">
        <h2 class="section-title">🗺 Kolkata to Murshidabad Adventure Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🏙</div>
            Kolkata
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏛</div>
            Victoria Memorial
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🌉</div>
            Howrah Bridge
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🛕</div>
            Dakshineswar Kali Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏞</div>
            Darjeeling
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🌄</div>
            Tiger Hill
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🚂</div>
            Darjeeling Himalayan Railway
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Murshidabad
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Hazarduari Palace
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🕌</div>
            Nizamat Imambara
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🕌</div>
            Katra Masjid
          </div>
        </div>
      
        <div class="path-label">
          Kolkata → Victoria Memorial → Howrah Bridge → Dakshineswar Kali Temple → Darjeeling → Tiger Hill → Darjeeling Himalayan Railway → Murshidabad → Hazarduari Palace → Nizamat Imambara → Katra Masjid
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
                        <li><a href="#cp1">Kolkata</a></li>
                        <li><a href="#cp2">Darjeeling</a></li>
                        <li><a href="#cp3">Murshidabad</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@westbengaltourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Kolkata, West Bengal, India</li>
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
                <p>&copy; 2025 West Bengal Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>