<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Assam'";
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
// $trekking_query = "SELECT * FROM trekking_services WHERE state = 'Assam'";
// if (!empty($selected_city)) {
//     $trekking_query .= " AND city = ?";
// }
// $trekking_query .= " ORDER BY service_type, name";

// $trekking_services = [];
// if ($stmt = $conn->prepare($trekking_query)) {
//     if (!empty($selected_city)) {
//         $stmt->bind_param("s", $selected_city);
//     }
//     $stmt->execute();
//     $result = $stmt->get_result();
//     while ($row = $result->fetch_assoc()) {
//         $trekking_services[] = $row;
//     }
//     $stmt->close();
// }

// Fetch cab drivers data
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Assam'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Assam' UNION SELECT DISTINCT city FROM trekking_services WHERE state = 'Jammu and Kashmir'";
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
    <title>Assam Tourism - Discover the Land of Mystical Temples and Wildlife</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/Asamcover.jpg');">
        <div class="container">
            <h1>Welcome to Assam</h1>
            <p>Assam, the land of the mighty Brahmaputra, is a state of mystical temples, lush tea gardens, and incredible wildlife. From the sacred Kamakhya Temple to the wild Kaziranga National Park, Assam offers experiences that connect you with nature and spirituality.</p>
            <p>Explore our guide to the must-visit destinations in this beautiful northeastern state.</p>
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
        <h1>Explore Kamakhya Temple</h1>
       <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
     </div>
   
 <div id="demo" class="collapse">

    <section class="attractions" id="kamakhya-temple">
        <div class="container">
            <h2 class="section-title">Kamakhya Temple – The Sacred Shakti Peetha of Assam</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/Kamakhya.jpg" alt="Kamakhya Temple">
                    </div>
                    <div class="card-content">
                        <h3>The Spiritual Heart of Tantric Worship</h3>
                        <p1>Perched atop the picturesque Nilachal Hill in Guwahati, the Kamakhya Temple is one of the oldest and most revered centers of Tantric worship in India. Dedicated to Goddess Kamakhya, a form of Shakti (feminine energy), this temple is among the most important of the 51 Shakti Peethas.</p1>
                        
                        <h4>History and Significance</h4>
                        <ul>
                            <li>One of the oldest Shakti Peethas in India</li>
                            <li>Dedicated to Goddess Kamakhya, representing feminine creative energy</li>
                            <li>Center of Tantric practices and rituals</li>
                        </ul>
                        
                        <h4>Unique Features</h4>
                        <ul>
                            <li>No idol of the goddess - worshipped in the form of a yoni (female symbol of creation)</li>
                            <li>Distinctive Nagara-style architecture with beehive-shaped dome</li>
                            <li>Intricate carvings of gods and goddesses adorn the walls</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/KamakhyaAttractions.jpg" alt="Kamakhya Temple Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at Kamakhya Temple</h3>
                        
                        <h4>1. Sanctum Sanctorum</h4>
                        <ul>
                            <li>Contains the sacred yoni (symbol of feminine energy)</li>
                            <li>Devotees offer prayers and seek blessings</li>
                        </ul>
                        
                        <h4>2. Ambubachi Mela</h4>
                        <ul>
                            <li>Annual festival marking the menstruation of Goddess Earth</li>
                            <li>Called the "Mahakumbh of the East" with thousands of pilgrims</li>
                            <li>Held every year in June</li>
                        </ul>
                        
                        <h4>3. Temple Architecture</h4>
                        <ul>
                            <li>Beautiful Nagara-style architecture</li>
                            <li>Spacious courtyard with sculptured panels</li>
                            <li>Panoramic views of Guwahati and Brahmaputra River</li>
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
                                <h3><i class="fas fa-bed"></i> Kamakhya View Hotel</h3>
                                <p>Offers stunning views of the temple and comfortable accommodations.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43210</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/night</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-bed"></i> Heritage Guwahati</h3>
                                <p>Boutique hotel located close to Kamakhya Temple.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43211</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,200/night</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-bed"></i> Brahmaputra Plaza</h3>
                                <p>Modern hotel with rooftop restaurant offering river views.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43212</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,800/night</p>
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
                                <h3><i class="fas fa-car"></i> Guwahati Cab Service</h3>
                                <p>Reliable local taxi service with fixed rates for temple visits.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43220</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹150/km</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-car"></i> Assam Tourist Cabs</h3>
                                <p>AC and non-AC cabs available for local sightseeing.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43221</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹200/km</p>
                            </div>
            
                            <div class="service-item">
                                <h3><i class="fas fa-car"></i> Kamakhya Temple Taxis</h3>
                                <p>Specialized service for temple visitors with knowledgeable drivers.</p>
                                <p class="contact"><i class="fas fa-phone"></i> +91 98765 43222</p>
                                <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹250/km</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
 
    <div class="collap" id="cp2">
        <h1>Kaziranga National Park</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">

    <section class="attractions" id="kaziranga-park">
        <div class="container">
            <h2 class="section-title">Kaziranga National Park – The Wild Heart of Assam</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/Kaziranga.jpg" alt="Kaziranga National Park">
                    </div>
                    <div class="card-content">
                        <h3>A UNESCO World Heritage Site</h3>
                        <p1>Nestled in the floodplains of the Brahmaputra River in Assam, Kaziranga National Park is a proud sanctuary for the largest population of the endangered one-horned rhinoceros. Spread over lush grasslands, swampy lagoons, and dense forests, Kaziranga offers a thrilling window into the wild beauty of Northeast India.</p1>
                        
                        <h4>Wildlife Highlights</h4>
                        <ul>
                            <li>Home to over 2,400 one-horned rhinos</li>
                            <li>Shelters wild elephants, Royal Bengal tigers, swamp deer, and wild water buffalo</li>
                            <li>Vibrant population of exotic birds like pelicans, storks, and herons</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/KazirangaAttractions.jpg" alt="Kaziranga Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at Kaziranga</h3>
                        
                        <h4>1. Safari Experiences</h4>
                        <ul>
                            <li>Jeep Safari: Ideal for wildlife spotting across vast terrains</li>
                            <li>Elephant Safari: Unique early morning ride into misty grasslands</li>
                        </ul>
                        
                        <h4>2. Safari Zones</h4>
                        <ul>
                            <li>Kohora (Central Range)</li>
                            <li>Bagori (Western Range)</li>
                            <li>Agaratoli (Eastern Range)</li>
                            <li>Burapahar (Ghorakati Range)</li>
                        </ul>
                        
                        <h4>3. Wildlife Spotting</h4>
                        <ul>
                            <li>Best place in the world to see one-horned rhinos</li>
                            <li>Good chances of spotting tigers and wild elephants</li>
                            <li>Birdwatching opportunities with over 500 species</li>
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
                                    <h3><i class="fas fa-bed"></i> Kaziranga Resort</h3>
                                    <p>Luxury resort located close to the park entrance.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43230</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Wild Grass Lodge</h3>
                                    <p>Eco-friendly lodge with traditional Assamese architecture.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43231</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,800/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Iora Resort</h3>
                                    <p>Comfortable accommodations with wildlife viewing opportunities.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43232</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,200/night</p>
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
                                    <h3><i class="fas fa-car"></i> Kaziranga Cab Service</h3>
                                    <p>Reliable local taxi service for park visits.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43240</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹300/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Assam Wildlife Taxis</h3>
                                    <p>Specialized service for park visitors with knowledgeable drivers.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43241</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹350/km</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Safari Transport</h3>
                                    <p>Jeep rentals for park safaris with experienced guides.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43242</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹2,000/day</p>
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
    <h1>Majuli Island</h1>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
</div>

  <div id="demo2" class="collapse">
    <section class="attractions" id="majuli-island">
        <div class="container">
            <h2 class="section-title">Majuli Island – The Cultural Soul of Assam</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/Majuli.jpg" alt="Majuli Island">
                    </div>
                    <div class="card-content">
                        <h3>The World's Largest River Island</h3>
                        <p1>Majuli, the world's largest river island, floats peacefully on the mighty Brahmaputra River in Assam. A land of lush green landscapes, ancient satras (monasteries), and rich tribal heritage, Majuli is India's first river island district and a living museum of Assamese culture, Vaishnavite traditions, and serene rural life.</p1>
                        
                        <h4>Cultural Significance</h4>
                        <ul>
                            <li>Founded by Srimanta Sankardeva in the 15th century</li>
                            <li>Center of Neo-Vaishnavite culture</li>
                            <li>Home to Mishing, Deori, and Assamese tribes</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-img">
                        <img src="IMAGE/MajuliAttractions.jpg" alt="Majuli Island Attractions">
                    </div>
                    <div class="card-content">
                        <h3>Main Attractions at Majuli</h3>
                        
                        <h4>1. Ancient Satras (Monasteries)</h4>
                        <ul>
                            <li>Kamalabari Satra – known for Sattriya dance and culture</li>
                            <li>Auniati Satra – famous for antiques and Assamese crafts</li>
                            <li>Dakhinpat Satra – vibrant with devotional art and music</li>
                        </ul>
                        
                        <h4>2. Traditional Culture</h4>
                        <ul>
                            <li>Witness mask-making, handloom weaving, pottery</li>
                            <li>Experience rice beer brewing traditions</li>
                            <li>Interact with local tribes in their bamboo huts</li>
                        </ul>
                        
                        <h4>3. Scenic Beauty</h4>
                        <ul>
                            <li>Stunning sunrises over the Brahmaputra</li>
                            <li>Peaceful country boat rides</li>
                            <li>Pristine wetlands filled with birds</li>
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
                                    <h3><i class="fas fa-bed"></i> La Maison de Ananda</h3>
                                    <p>Boutique hotel with traditional Mishing tribal architecture.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,800/night</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bed"></i> Ygdrasill Bamboo Cottage</h3>
                                    <p>Eco-friendly bamboo cottages with river views.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
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
                                    <h3><i class="fas fa-ship"></i> Majuli Ferry Service</h3>
                                    <p>Regular ferry services from Nimati Ghat to Majuli.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> ₹50-100 per person</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-bicycle"></i> Island Bike Rentals</h3>
                                    <p>Bicycle rentals to explore the island at your own pace.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> ₹200/day</p>
                                </div>
                
                                <div class="service-item">
                                    <h3><i class="fas fa-car"></i> Majuli Taxis</h3>
                                    <p>Limited taxi service available on the island.</p>
                                    <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                    <p class="contact"><i class="fas fa-rupee-sign"></i> Starting from ₹150/km</p>
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
                        <h3>Kamakhya Temple</h3>
                        <ul>
                            <li>October to April: Pleasant weather ideal for darshan</li>
                            <li>June: Ambubachi Mela (very crowded but spiritually significant)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Kaziranga National Park</h3>
                        <ul>
                            <li>November to April: Park open and best for wildlife sightings</li>
                            <li>February and March: Especially good for animal activity</li>
                            <li>Closed during monsoon (May to October)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Majuli Island</h3>
                        <ul>
                            <li>October to March: Cool, dry weather ideal for exploring</li>
                            <li>November: Raas Mahotsav cultural festival</li>
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
                    <p>Lokpriya Gopinath Bordoloi International Airport in Guwahati serves as the main gateway.</p>
                    <p>For Kaziranga: Jorhat Airport (96 km) or Guwahati Airport (225 km)</p>
                    <p>For Majuli: Jorhat Airport (40 km from ferry point)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Guwahati Railway Station is well-connected to major cities.</p>
                    <p>For Kamakhya: Kamakhya Railway Station (close to temple)</p>
                    <p>For Kaziranga: Furkating Junction (75 km)</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Well-connected via national highways and state roads.</p>
                    <p>Regular bus services between major cities and tourist destinations.</p>
                    <p>For Majuli: Take ferry from Nimati Ghat (20 km from Jorhat)</p>
                </div>
            </div>
        </div>
    </section>
    
    
<div class="route-wrapper">
    <h2 class="section-title">🗺 Guwahati to Majuli Island Route</h2>
    
    <div class="pathway">
      <div class="step">
        <div class="emoji">🏙</div>
        Guwahati
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🛕</div>
        Kamakhya Temple
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🦏</div>
        Kaziranga
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🏡</div>
        Jorhat
      </div>
      <div class="arrow"></div>
      <div class="step">
        <div class="emoji">🏝</div>
        Majuli Island
      </div>
    </div>
  
    <div class="path-label">
      Guwahati → Kamakhya Temple → Kaziranga → Jorhat → Majuli Island
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
                        <li><a href="#cp1">Kamakhya Temple</a></li>
                        <li><a href="#cp2">Kaziranga National Park</a></li>
                        <li><a href="#cp3">Majuli Island</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@assamtourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Guwahati, Assam, India</li>
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
                <p>&copy; 2025 Assam Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>