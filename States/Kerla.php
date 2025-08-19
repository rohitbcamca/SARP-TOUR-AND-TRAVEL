<?php
session_start();
require_once '../db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Get selected city from query parameter
$selected_city = isset($_GET['city']) ? $_GET['city'] : '';

// Fetch hotels data
$hotels_query = "SELECT * FROM hotels WHERE state = 'Kerala'";
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
$cab_query = "SELECT * FROM cab_drivers WHERE state = 'Kerala'";
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
$cities_query = "SELECT DISTINCT city FROM hotels WHERE state = 'Kerala'";
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
    <title>Kerala Tourism - God's Own Country</title>
    <link rel="stylesheet" href="State.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
     
    <header style="background-image: url('IMAGE/kerala-banner.jpg');">
        <div class="container">
            <h1 style="color:aliceblue">Welcome to Kerala</h1>
            <p>Kerala, known as "God's Own Country," is a tropical paradise of serene backwaters, lush hill stations, and pristine beaches. From the tranquil houseboats of Alleppey to the misty tea gardens of Munnar, Kerala offers unforgettable experiences of nature, culture, and relaxation.</p>
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
        <h1>Explore Alleppey (Alappuzha)</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo">Know More</button>
    </div>
   
    <div id="demo" class="collapse">
        <section class="attractions" id="alleppey">
            <div class="container">
                <h2 class="section-title">Alleppey (Alappuzha): The Venice of the East</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Allepy.jpg" alt="Alleppey Backwaters">
                        </div>
                        <div class="card-content">
                            <h3>The Backwater Paradise</h3>
                            <p1>Located in the heart of Kerala's backwaters, Alleppey is a stunning destination known for its serene houseboat cruises, lush green landscapes, and tranquil beaches. Often called the "Venice of the East," Alleppey is famous for its picturesque canals, lagoons, and paddy fields.</p1>
                            
                            <h4>Key Attractions in Alleppey</h4>
                            <ul>
                                <li><strong>Houseboat Cruises:</strong> Experience the beauty of Kerala's backwaters by staying in a luxurious houseboat. Enjoy authentic Kerala cuisine while sailing through scenic waterways.</li>
                                <li><strong>Alappuzha Beach:</strong> A golden sandy beach ideal for relaxing and enjoying sunset views, home to the Alleppey Lighthouse.</li>
                                <li><strong>Vembanad Lake:</strong> The largest lake in Kerala, perfect for birdwatching, boating, and photography. Hosts the famous Nehru Trophy Boat Race every August.</li>
                                <li><strong>Marari Beach:</strong> A less crowded, pristine beach ideal for swimming and Ayurvedic treatments.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/AllepyAttract.jpg" alt="Alleppey Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Travel Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Winter (October to February):</strong> Ideal for sightseeing, houseboat cruises, and beach visits.</li>
                                <li><strong>Monsoon (June to September):</strong> Best for Ayurvedic treatments and lush green landscapes.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Cochin International Airport (83 km).</li>
                                <li><strong>By Train:</strong> Alleppey Railway Station connects to major cities like Kochi, Trivandrum, and Bangalore.</li>
                                <li><strong>By Road:</strong> Well-connected by NH-66, with buses and taxis available from Kochi and other cities.</li>
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
                                        <h3><i class="fas fa-bed"></i> Backwater Ripples</h3>
                                        <p>Luxury resort with private houseboat docking and lake views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43250</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Palm Grove Lake Resort</h3>
                                        <p>Heritage property with traditional Kerala architecture and backwater access.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43251</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹3,800/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Marari Beach Resort</h3>
                                        <p>Eco-friendly beachfront resort with Ayurvedic spa facilities.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43252</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,200/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Houseboats Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-ship"></i>
                                    <h3>Houseboat Services</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-ship"></i> Rainbow Cruises</h3>
                                        <p>Luxury houseboats with AC, private decks, and Kerala cuisine.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43260</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹8,000/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-ship"></i> Kerala Backwaters</h3>
                                        <p>Traditional houseboats with experienced crew and authentic meals.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43261</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-ship"></i> Emerald Cruises</h3>
                                        <p>Premium houseboats with sunset views and cultural performances.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43262</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹10,000/day</p>
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
        <h1>Explore Munnar</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo1">Know More</button>
    </div>
    
    <div id="demo1" class="collapse">
        <section class="attractions" id="munnar">
            <div class="container">
                <h2 class="section-title">Munnar: The Serene Hill Station of Kerala</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Munnar.jpg" alt="Munnar Tea Gardens">
                        </div>
                        <div class="card-content">
                            <h3>The Kashmir of South India</h3>
                            <p1>Munnar is a breathtaking hill station nestled in the Western Ghats of Kerala. Known for its rolling tea plantations, misty mountains, waterfalls, and cool climate, Munnar is a paradise for nature lovers, honeymooners, and adventure seekers.</p1>
                            
                            <h4>Key Attractions in Munnar</h4>
                            <ul>
                                <li><strong>Tea Gardens & Tata Tea Museum:</strong> Walk through endless tea plantations and witness tea processing. Enjoy tea tasting sessions with authentic Kerala flavors.</li>
                                <li><strong>Mattupetty Dam & Lake:</strong> A scenic reservoir offering boating, picnic spots, and stunning hill views.</li>
                                <li><strong>Echo Point:</strong> A unique place where your voice echoes through the valley, perfect for photography and nature walks.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/MunnarAttarct.jpg" alt="Munnar Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Travel Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Winter (October - February):</strong> Best time for sightseeing and outdoor activities.</li>
                                <li><strong>Summer (March - May):</strong> Pleasant weather, ideal for family vacations.</li>
                                <li><strong>Monsoon (June - September):</strong> Lush greenery, but frequent rains.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Cochin International Airport (110 km).</li>
                                <li><strong>By Train:</strong> Nearest railway station is Aluva (110 km) or Ernakulam (130 km).</li>
                                <li><strong>By Road:</strong> Well-connected by scenic road routes from Kochi, Coimbatore, and Madurai.</li>
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
                                        <h3><i class="fas fa-bed"></i> Tea Valley Resort</h3>
                                        <p>Luxury resort amidst tea plantations with panoramic views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43270</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Fragrant Nature Munnar</h3>
                                        <p>Boutique hotel with Ayurvedic spa and mountain views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43271</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹4,200/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Amber Dale Luxury Hotel</h3>
                                        <p>Modern hotel with infinity pool and valley views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43272</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,800/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Tour Operators Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-hiking"></i>
                                    <h3>Tour Operators</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-map-marked-alt"></i> Munnar Travels</h3>
                                        <p>Full-day sightseeing tours covering tea gardens, dams, and viewpoints.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43280</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-car"></i> Kerala Hill Tours</h3>
                                        <p>Private car rentals with experienced drivers for customized itineraries.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43281</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹2,500/day</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-leaf"></i> Tea Trail Adventures</h3>
                                        <p>Specialized tea plantation walks and factory visits.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43282</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹800/person</p>
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
        <h1>Explore Fort Kochi</h1>
        <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#demo2">Know More</button>
    </div>

    <div id="demo2" class="collapse">
        <section class="attractions" id="fort-kochi">
            <div class="container">
                <h2 class="section-title">Fort Kochi: A Historic Blend of Cultures and Heritage</h2>
                <div class="attraction-cards">
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/Kochi.jpg" alt="Fort Kochi">
                        </div>
                        <div class="card-content">
                            <h3>The Colonial Gem of Kerala</h3>
                            <p1>Fort Kochi is a charming coastal town known for its colonial-era architecture, vibrant culture, Chinese fishing nets, and stunning seaside views. With influences from the Portuguese, Dutch, and British, this heritage town offers a perfect mix of history, art, and scenic beauty.</p1>
                            
                            <h4>Key Attractions in Fort Kochi</h4>
                            <ul>
                                <li><strong>Chinese Fishing Nets:</strong> Iconic fishing nets introduced by Chinese traders in the 14th century. Best visited during sunrise or sunset.</li>
                                <li><strong>St. Francis Church:</strong> India's oldest European church (built in 1503), where Vasco da Gama was originally buried.</li>
                                <li><strong>Mattancherry Palace (Dutch Palace):</strong> A 16th-century palace famous for Kerala murals, royal artifacts, and historical exhibits.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-img">
                            <img src="IMAGE/KochiAttract.jpg" alt="Fort Kochi Attractions">
                        </div>
                        <div class="card-content">
                            <h3>Travel Information</h3>
                            
                            <h4>Best Time to Visit</h4>
                            <ul>
                                <li><strong>Winter (October to March):</strong> Pleasant weather for sightseeing.</li>
                                <li><strong>Monsoon (June to September):</strong> Best for experiencing Kerala's lush greenery.</li>
                            </ul>
                            
                            <h4>How to Reach</h4>
                            <ul>
                                <li><strong>By Air:</strong> Nearest airport is Cochin International Airport (37 km).</li>
                                <li><strong>By Train:</strong> Ernakulam Junction Railway Station (12 km) is the nearest.</li>
                                <li><strong>By Ferry:</strong> Frequent ferries from Ernakulam to Fort Kochi.</li>
                                <li><strong>By Road:</strong> Well-connected by buses, taxis, and auto-rickshaws.</li>
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
                                        <h3><i class="fas fa-bed"></i> Brunton Boatyard</h3>
                                        <p>Luxury heritage hotel with colonial architecture and harbor views.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43290</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹7,500/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Old Harbour Hotel</h3>
                                        <p>Charming boutique hotel in a 300-year-old building.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43291</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹6,000/night</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-bed"></i> Forte Kochi</h3>
                                        <p>Modern hotel with rooftop restaurant near the Chinese nets.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43292</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹5,200/night</p>
                                    </div>
                                </div>
                            </div> -->
                    
                            <!-- Tour Guides Column -->
                            <!-- <div class="service-column">
                                <div class="service-category">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <h3>Tour Guides</h3>
                                </div>
                                <div class="services-grid">
                                    <div class="service-item">
                                        <h3><i class="fas fa-user-tie"></i> Kochi Heritage Walks</h3>
                                        <p>Expert-guided walking tours of Fort Kochi's historical sites.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43300</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹800/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-ship"></i> Kochi Water Tours</h3>
                                        <p>Boat tours covering Fort Kochi's waterfront attractions.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43301</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,200/person</p>
                                    </div>
                    
                                    <div class="service-item">
                                        <h3><i class="fas fa-utensils"></i> Kochi Food Tours</h3>
                                        <p>Culinary tours exploring Fort Kochi's diverse cuisine.</p>
                                        <p class="contact"><i class="fas fa-phone"></i> +91 98765 43302</p>
                                        <p class="contact"><i class="fas fa-rupee-sign"></i> From ₹1,500/person</p>
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
            <h2 class="section-title">Best Time to Visit Kerala</h2>
            <div class="attraction-cards">
                <div class="card">
                    <div class="card-content">
                        <h3>Alleppey</h3>
                        <ul>
                            <li><strong>Winter (October to February):</strong> Ideal for houseboat cruises and beach visits.</li>
                            <li><strong>Monsoon (June to September):</strong> Best for Ayurvedic treatments and lush landscapes.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Munnar</h3>
                        <ul>
                            <li><strong>Winter (October - February):</strong> Best for sightseeing and outdoor activities.</li>
                            <li><strong>Summer (March - May):</strong> Pleasant weather for family vacations.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <h3>Fort Kochi</h3>
                        <ul>
                            <li><strong>Winter (October to March):</strong> Pleasant weather for sightseeing.</li>
                            <li><strong>Monsoon (June to September):</strong> Lush greenery but frequent rains.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how-to-reach" id="how-to-reach">
        <div class="container">
            <h2 class="section-title">How to Reach Kerala</h2>
            <div class="transport-options">
                <div class="transport-card">
                    <i class="fas fa-plane"></i>
                    <h3>By Air</h3>
                    <p>Cochin International Airport is the main gateway, with good domestic and international connectivity.</p>
                    <p>Other airports: Trivandrum International Airport, Calicut International Airport.</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-train"></i>
                    <h3>By Train</h3>
                    <p>Major railway stations: Ernakulam (Kochi), Thiruvananthapuram, Kottayam, and Alappuzha.</p>
                    <p>Well-connected to all major Indian cities.</p>
                </div>
                
                <div class="transport-card">
                    <i class="fas fa-bus"></i>
                    <h3>By Road</h3>
                    <p>Excellent road network with national highways connecting to neighboring states.</p>
                    <p>Regular bus services from Tamil Nadu, Karnataka, and other states.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="route-wrapper">
        <h2 class="section-title">🗺 Kochi Adventure Route</h2>
        
        <div class="pathway">
          <div class="step">
            <div class="emoji">✈</div>
            Cochin International Airport
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">⛵</div>
            Alleppey
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏞</div>
            Munnar
          </div>
          <div class="arrow"></div>
          <div class="step">
            <div class="emoji">🏰</div>
            Fort Kochi
          </div>
        </div>
      
        <div class="path-label">
          Cochin International Airport → Alleppey → Munnar → Fort Kochi
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
                        <li><a href="#cp1">Alleppey</a></li>
                        <li><a href="#cp2">Munnar</a></li>
                        <li><a href="#cp3">Fort Kochi</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@keralatourism.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Thiruvananthapuram, Kerala, India</li>
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
                <p>&copy; 2025 Kerala Tourism. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>