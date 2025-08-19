<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Punjab'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Punjab'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Punjab'";
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
    <title>Punjab Tourism - Discover the Land of Spirituality and Valor</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style= "background-image: url('IMAGE/punjab.jpg')">
        <div class="container">
            <h1>Welcome to Punjab</h1>
            <p>Punjab, the land of five rivers, is a state brimming with history, spirituality, and cultural richness. From the serene Golden Temple to the patriotic Wagah Border, Punjab offers experiences that touch the soul and ignite national pride.</p>
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
        <h1> Explore Golden Temple</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
 <div id="demo" class="collapse">

    <section class="attractions" id="golden-temple">
        <div class="container">
            <h2 class="section-title">Golden Temple (Harmandir Sahib)</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/GTemple.jpg" alt="Golden Temple">
                    </div>
                    <div class="card-content">
                        <h3>The Spiritual Heart of Sikhism</h3>
                        <p1>Located in Amritsar, the Golden Temple (Sri Harmandir Sahib) is the holiest shrine of Sikhism and a symbol of peace, spirituality, and equality. With its stunning gold-plated structure, serene Sarovar (holy pond), and warm hospitality, the Golden Temple attracts millions of devotees and tourists from all over the world.</p1>
                        
                        <h4>History and Significance</h4>
                        <ul>
                            <li>Built in the 16th century by Guru Arjan Dev Ji, the 5th Sikh Guru</li>
                            <li>The Guru Granth Sahib (holy scripture of Sikhism) is recited inside the temple</li>
                            <li>Represents the Sikh principles of humility, equality, and selfless service</li>
                        </ul>
                        
                        <h4>Architectural Marvel</h4>
                        <ul>
                            <li>The temple is gold-plated, reflecting beautifully in the surrounding Amrit Sarovar (holy pool)</li>
                            <li>Built on a lower level, symbolizing humility</li>
                            <li>Features intricate carvings, marble inlays, and stunning frescoes</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/GoldenAttract.jpg" alt="Golden Temple Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at the Golden Temple</h3>
                        
                        <h4>1. Darbar Sahib (Sanctum Sanctorum)</h4>
                        <ul>
                            <li>The inner sanctum, where the Guru Granth Sahib is placed</li>
                            <li>Devotees listen to soothing Gurbani (hymns) throughout the day</li>
                        </ul>
                        
                        <h4>2. Amrit Sarovar (Holy Tank)</h4>
                        <ul>
                            <li>The temple is surrounded by the sacred pool, believed to have healing properties</li>
                            <li>Visitors can take a peaceful walk around the Parikrama (pathway)</li>
                        </ul>
                        
                        <h4>3. Langar (Community Kitchen)</h4>
                        <ul>
                            <li>The largest free community kitchen in the world, serving over 100,000 people daily</li>
                            <li>Offers free meals to everyone, regardless of caste, religion, or background</li>
                            <li>Run by volunteers as a form of selfless service (Seva)</li>
                        </ul>
                    </div>
                </div>
            </div>
        
        </div>
    </section>
</div>
 
    <div class="collap" id="cp2">
        <h1>Jalianwala Bagh</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">

    <section class="attractions" id="jallianwala-bagh">
        <div class="container">
            <h2 class="section-title">Jallianwala Bagh</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/JalinwalaBagh.jpg" alt="Jallianwala Bagh">
                    </div>
                    <div class="card-content">
                        <h3>A Memorial of Sacrifice and Resilience</h3>
                        <p1>Located in Amritsar, Punjab, Jallianwala Bagh is a historic public garden that stands as a memorial to one of the darkest chapters in Indian history. It is a symbol of sacrifice, patriotism, and the struggle for India's independence.</p1>
                        
                        <h4>Historical Significance</h4>
                        <ul>
                            <li>On April 13, 1919 (Baisakhi festival), thousands of unarmed Indians gathered in Jallianwala Bagh to protest against the Rowlatt Act</li>
                            <li>General Reginald Dyer ordered his troops to open fire on the crowd, killing over 1,000 people and injuring thousands more</li>
                            <li>The massacre played a crucial role in India's freedom struggle, igniting widespread resistance against British rule</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/jaalianwalaAttract.jpg" alt="Jallianwala Bagh Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at Jallianwala Bagh</h3>
                        
                        <h4>1. Martyrs' Well</h4>
                        <ul>
                            <li>Many people jumped into this well to escape the bullets, leading to tragic deaths</li>
                            <li>The well has been preserved as a memorial to honor the fallen</li>
                        </ul>
                        
                        <h4>2. Bullet Marks on Walls</h4>
                        <ul>
                            <li>The walls of Jallianwala Bagh still bear bullet holes, a haunting reminder of the massacre</li>
                            <li>These marks have been preserved to educate visitors about the tragic event</li>
                        </ul>
                        
                        <h4>3. Flame of Liberty (Amar Jyoti)</h4>
                        <ul>
                            <li>A perpetual flame burns in honor of the martyrs who sacrificed their lives</li>
                            <li>The central memorial was inaugurated in 1961 by Dr. Rajendra Prasad, India's first President</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
    
<div class="collap" id="cp3">
    <h1>Wagah Border</h1>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
</div>

  <div id="demo2" class="collapse">
    <section class="attractions" id="wagah-border">
        <div class="container">
            <h2 class="section-title">Wagah Border</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/baghaborder.jpg" alt="Wagah Border">
                    </div>
                    <div class="card-content">
                        <h3>The Grand Display of Patriotism</h3>
                        <p1>Located 22 km from Amritsar, the Wagah Border is the only road crossing between India and Pakistan and is famous for its daily Beating Retreat Ceremony. This electrifying event, held every evening, is a symbol of national pride, military discipline, and the friendly rivalry between India and Pakistan.</p1>
                        
                        <h4>The Beating Retreat Ceremony</h4>
                        <ul>
                            <li>Takes place every evening before sunset (timings change seasonally)</li>
                            <li>Soldiers from both sides perform a synchronized parade, filled with high kicks, sharp salutes, and powerful stomping</li>
                            <li>The flags of India and Pakistan are lowered simultaneously, symbolizing mutual respect</li>
                            <li>Ends with a firm handshake between soldiers before the border gates close</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/AtttractionBagha.jpg" alt="Wagah Border Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at Wagah Border</h3>
                        
                        <h4>1. The Grand Parade</h4>
                        <ul>
                            <li>The main highlight, featuring energetic drill movements, patriotic slogans, and loud applause</li>
                            <li>Visitors cheer for the BSF soldiers as they perform power-packed gestures</li>
                        </ul>
                        
                        <h4>2. The Swarna Jayanti Dwar (Golden Jubilee Gate)</h4>
                        <ul>
                            <li>A symbolic entry point marking 50 years of India's independence</li>
                            <li>A great place for photographs and learning about Indo-Pak history</li>
                        </ul>
                        
                        <h4>3. The Attari-Wagah Border Gate</h4>
                        <ul>
                            <li>The official checkpoint between India and Pakistan</li>
                            <li>Offers a rare glimpse into the neighboring country's flag and border post</li>
                        </ul>
                        
                        <h4>4. Amar Jawan Jyoti Memorial</h4>
                        <ul>
                            <li>A memorial dedicated to fallen soldiers of the Border Security Force (BSF)</li>
                            <li>A place to pay tribute to India's brave warriors</li>
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
                        <h3>Golden Temple</h3>
                        <ul>
                            <li>Early morning or late evening for a peaceful experience</li>
                            <li>Baisakhi (April) and Gurpurabs (Sikh festivals) offer a grand celebration</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Jallianwala Bagh</h3>
                        <ul>
                            <li>Early morning or evening for a peaceful experience</li>
                            <li>Best visited alongside the Golden Temple, as both are close to each other</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Wagah Border</h3>
                        <ul>
                            <li>Evening (before sunset) to witness the Beating Retreat Ceremony</li>
                            <li>Winter months (October to March) offer pleasant weather</li>
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
                    <p>Sri Guru Ram Dass Jee International Airport serves Amritsar with domestic and international flights.</p>
                    <p>Distance to Golden Temple: 12 km</p>
                    <p>Distance to Wagah Border: 35 km</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Amritsar Railway Station is well-connected to major cities across India.</p>
                    <p>Distance to Golden Temple: 2 km</p>
                    <p>Distance to Wagah Border: 24 km</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Amritsar is well-connected via national highways and state roads.</p>
                    <p>Regular bus services from Delhi, Chandigarh, and other major cities.</p>
                    <p>Taxis and private vehicles can easily reach all attractions.</p>
                </div>
            </div>
        </div>
    </section>
    
    <div class="route-wrapper">
        <h2 class="section-title">🗺 Amritsar Adventure Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">🕌</div>
            Golden Temple
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏞</div>
            Jallianwala Bagh
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🇮🇳</div>
            Wagah Border
          </div>
        </div>
      
        <div class="path-label">
          Amritsar City (Golden Temple + Jallianwala Bagh) → Wagah Border (Evening Trip)
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
                        <li><a href="#cp1">Golden Temple</a></li>
                        <li><a href="#cp2">Jallianwala Bagh</a></li>
                        <li><a href="#cp3">Wagah Border</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@punjabtourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Amritsar, Punjab, India</li>
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
                <p>&copy; 2025 Punjab Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>