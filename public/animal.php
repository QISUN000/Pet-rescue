<?php
require_once '../config/database.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

// Fetch animal with its image and category
$sql = "SELECT a.*, i.filename, c.name as category_name 
        FROM animals a 
        LEFT JOIN images i ON a.id = i.animal_id 
        LEFT JOIN animal_categories ac ON a.id = ac.animal_id
        LEFT JOIN categories c ON ac.category_id = c.id
        WHERE a.id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    header('Location: index.php');
    exit();
}

// Handle adoption form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    try {
        $db->beginTransaction();

        // Update animal status to 'pending'
        $sql = "UPDATE animals SET status = 'pending' WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);

        // You could also store the adoption request details if needed
        // For now, just update the status
        
        $db->commit();
        $success_message = "Thank you for your interest in adopting " . htmlspecialchars($animal['name']) . ". We will contact you soon!";
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = "An error occurred. Please try again.";
    }
}
?>

<div class="container my-5">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <?php if ($animal['filename']): ?>
                <img src="uploads/<?= htmlspecialchars($animal['filename']) ?>" 
                     alt="<?= htmlspecialchars($animal['name']) ?>"
                     class="img-fluid rounded">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($animal['name']) ?></h2>
            <p class="text-muted">
                <?= htmlspecialchars($animal['breed']) ?> • 
                <?= htmlspecialchars($animal['species']) ?> • 
                <?= htmlspecialchars($animal['category_name']) ?>
            </p>
            <div class="mb-4">
                <?= nl2br(htmlspecialchars($animal['description'])) ?>
            </div>

            <?php if ($animal['status'] === 'available'): ?>
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Adopt <?= htmlspecialchars($animal['name']) ?></h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="4"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Adoption Request</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    This pet is no longer available for adoption.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>