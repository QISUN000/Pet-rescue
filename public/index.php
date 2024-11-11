<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Fetch featured pets
$sql = "SELECT a.*, i.filename 
        FROM animals a 
        LEFT JOIN images i ON a.id = i.animal_id 
        WHERE a.status = 'available' 
        LIMIT 3";
$stmt = $db->prepare($sql);
$stmt->execute();
$featured_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pawsome Animal Shelter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
   

    <!-- Hero Section -->
    <div class="hero-banner position-relative">
        <img src="../assets/images/hero-banner.png" class="w-100" style="max-height: 500px; object-fit: cover;" alt="Hero Banner">
        <div class="position-absolute top-50 end-0 translate-middle-y pe-5">
            <h1 class="text-white">The coolest Animal Shelter<br>in Middle TN</h1>
        </div>
    </div>

    <!-- Ways to Contribute -->
    <div class="container my-5">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h2>There are so many ways that you can contribute to these lovely animals</h2>
            </div>
            <div class="col-md-8">
                <div class="row text-center">
                    <div class="col-md-3">
                        <a href="search" class="text-decoration-none">
                           
                            <div>Adopt</div>
                        </a>
                    </div>
                 
                  
                    <div class="col-md-3">
                        <a href="#" class="text-decoration-none">
                          
                            <div>Donate</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adoption Search -->
    <div class="container ">
        <div class="row align-items-center bg-secondary p-5">
            <div class="col-md-6">
                <img src="../assets/images/adopt-image.jpg" alt="Adopt a pet" class="img-fluid rounded">
            </div>
            <div class="col-md-6">
                <h2>Let us help you to adopt the perfect pet today.</h2>
                <a href="search.php" class="btn btn-primary mt-3">Adoption Search</a>
            </div>
        </div>
    </div>

    <!-- Featured Pets -->
    <div class="container my-5">
        <h2 class="text-center mb-4">A few of our favorite pets</h2>
        <div class="row">
            <?php foreach ($featured_pets as $pet): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($pet['filename']): ?>
                    <img src="uploads/<?= htmlspecialchars($pet['filename']) ?>" 
                         class="card-img-top" style="height: 300px; object-fit: cover;"
                         alt="<?= htmlspecialchars($pet['name']) ?>">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($pet['name']) ?></h5>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require_once 'footer.php'; ?>