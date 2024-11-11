<?php
require_once '../config/database.php';
require_once '../includes/maps_api.php';
require_once 'header.php';

$maps = new MapsAPI(MAPS_API_KEY);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success_message = "Thank you for your message. We will get back to you soon!";
}
?>

<div class="container my-5">
    <div class="row">
        <!-- Contact Form Section -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="mb-4">Contact Us</h2>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?= $success_message ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Map and Location Info Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h3>Our Location</h3>
                    <!-- Map container -->
                    <div id="map" style="height: 400px;" class="mb-4"></div>
                    
                    <!-- Location Details -->
                    <div class="mt-4">
                        <h4>Pawsome Animal Shelter</h4>
                        <p>
                            <i class="bi bi-geo-alt"></i> 123 Pet Street, Nashville, TN<br>
                            <i class="bi bi-telephone"></i> (555) 123-4567<br>
                            <i class="bi bi-envelope"></i> info@pawsome.com
                        </p>
                        
                        <h5>Hours of Operation</h5>
                        <ul class="list-unstyled">
                            <li>Monday - Friday: 9:00 AM - 6:00 PM</li>
                            <li>Saturday: 10:00 AM - 4:00 PM</li>
                            <li>Sunday: Closed</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Nearby Services -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3>Nearby Pet Services</h3>
                    <div id="nearby-services" class="list-group">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script>
let map;
let service;
let infowindow;

async function initMap() {
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

    // Shelter location (Nashville coordinates)
    const shelterLocation = { lat: 36.1627, lng: -86.7816 };
    
    // Create map
    map = new Map(document.getElementById("map"), {
        center: shelterLocation,
        zoom: 14,
        mapId: 'YOUR_MAP_ID' // Optional: Create a map style in Google Cloud Console
    });
    
    // Add marker for shelter
    const shelterMarker = new AdvancedMarkerElement({
        map: map,
        position: shelterLocation,
        title: "Pawsome Animal Shelter",
        content: buildMarkerContent("Pawsome Animal Shelter")
    });
    
    // Info window for shelter
    infowindow = new google.maps.InfoWindow();
    const shelterContent = `
        <div>
            <h5>Pawsome Animal Shelter</h5>
            <p>123 Pet Street<br>Nashville, TN</p>
        </div>
    `;
    
    shelterMarker.addListener("click", () => {
        infowindow.setContent(shelterContent);
        infowindow.open(map, shelterMarker);
    });
    
    // Search for nearby pet services
    const request = {
        location: shelterLocation,
        radius: '1500', // 1.5km radius
        type: ['veterinary_care', 'pet_store']
    };
    
    service = new google.maps.places.PlacesService(map);
    service.nearbySearch(request, (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
            results.forEach(place => {
                // Add marker for each place
                const placeMarker = new AdvancedMarkerElement({
                    map: map,
                    position: place.geometry.location,
                    title: place.name,
                    content: buildMarkerContent(place.name)
                });
                
                // Add to nearby services list
                const serviceItem = document.createElement('a');
                serviceItem.className = 'list-group-item list-group-item-action';
                serviceItem.innerHTML = `
                    <h6 class="mb-1">${place.name}</h6>
                    <small>${place.vicinity}</small>
                `;
                document.getElementById('nearby-services').appendChild(serviceItem);
                
                // Add click listener
                placeMarker.addListener("click", () => {
                    infowindow.setContent(`
                        <div>
                            <h6>${place.name}</h6>
                            <p>${place.vicinity}</p>
                        </div>
                    `);
                    infowindow.open(map, placeMarker);
                });
            });
        }
    });
}

// Helper function to build marker content
function buildMarkerContent(title) {
    const content = document.createElement('div');
    content.classList.add('marker');
    content.innerHTML = `
        <div style="padding: 8px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            <span style="font-weight: 500;">${title}</span>
        </div>
    `;
    return content;
}
</script>

<!-- Updated Google Maps API script loading -->
<script async
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initMap">
</script>
<?php require_once 'footer.php'; ?>