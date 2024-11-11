<?php
require_once '../config/database.php';
require_once 'header.php';
?>

<div class="container my-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1>Support Our Mission</h1>
        <p class="lead">Your donation helps us provide care for animals in need</p>
    </div>

    <!-- Donation Options -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <h3 class="card-title">One-Time Gift</h3>
                    <p class="card-text">Make a one-time donation to help animals in need</p>
                    <div class="d-grid">
                        <button class="btn btn-primary">Donate Now</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <h3 class="card-title">Monthly Giving</h3>
                    <p class="card-text">Become a monthly donor and provide ongoing support</p>
                    <div class="d-grid">
                        <button class="btn btn-primary">Give Monthly</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <h3 class="card-title">In-Kind Donations</h3>
                    <p class="card-text">Donate supplies, food, or other needed items</p>
                    <div class="d-grid">
                        <button class="btn btn-primary">Learn More</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Impact Section -->
    <div class="row mb-5">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="mb-4">Your Impact</h2>
            <p>Your donations help us:</p>
            <ul class="list-unstyled">
                <li class="mb-2">🏠 Provide shelter for homeless animals</li>
                <li class="mb-2">💊 Cover medical expenses and treatments</li>
                <li class="mb-2">🍽️ Supply food and daily necessities</li>
                <li class="mb-2">❤️ Support rescue operations</li>
            </ul>
        </div>
    </div>

    <!-- Other Ways to Help -->
    <div class="row">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="mb-4">Other Ways to Help</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4>Volunteer</h4>
                            <p>Give your time and skills to help our animals</p>
                            <button class="btn btn-outline-primary">Learn More</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4>Foster</h4>
                            <p>Provide a temporary home for animals in need</p>
                            <button class="btn btn-outline-primary">Learn More</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>