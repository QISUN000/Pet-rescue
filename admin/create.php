<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

// Fetch all categories for the dropdown
$sql = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $species = filter_input(INPUT_POST, 'species', FILTER_SANITIZE_STRING);
    $breed = filter_input(INPUT_POST, 'breed', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    $data = [
        'name' => $_POST['name'] ?? '',
        'species' => $_POST['species'] ?? '',
        'breed' => $_POST['breed'] ?? '',
        'description' => $_POST['description'] ?? '',
        'category_id' => $_POST['category_id'] ?? ''
    ];

    // Validate form data
    $errors = validateAnimalData($data);

    // Validate image if uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
        $imageErrors = validateImage($_FILES['image']);
        if (!empty($imageErrors)) {
            $errors['image'] = $imageErrors;
        }
    }
    if (empty($errors)) {
        $data = sanitizeInput($data);
        try {
            $db->beginTransaction();

            // Insert animal
            $sql = "INSERT INTO animals (name, species, breed, description, status) 
                VALUES (:name, :species, :breed, :description, :status)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':species' => $species,
                ':breed' => $breed,
                ':description' => $description,
                ':status' => 'available'
            ]);

            $animal_id = $db->lastInsertId();

            // Insert category relationship
            if ($category_id) {
                $sql = "INSERT INTO animal_categories (animal_id, category_id) VALUES (:animal_id, :category_id)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':animal_id' => $animal_id,
                    ':category_id' => $category_id
                ]);
            }

            // Handle file upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['image']['name'];
                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($file_ext, $allowed)) {
                    $new_filename = uniqid() . '.' . $file_ext;
                    if (!file_exists('../public/uploads')) {
                        mkdir('../public/uploads', 0777, true);
                    }
                    $upload_path = '../public/uploads/' . $new_filename;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $sql = "INSERT INTO images (animal_id, filename) VALUES (:animal_id, :filename)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            ':animal_id' => $animal_id,
                            ':filename' => $new_filename
                        ]);
                    }
                }
            }

            $db->commit();
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}


?>

<div class="container my-4">
    <h2>Add New Animal</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name"
                        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        value="<?= isset($data['name']) ? htmlspecialchars($data['name']) : '' ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Species</label>
                    <select name="species" class="form-control" required>
                        <option value="cat">Cat</option>
                        <option value="dog">Dog</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Breed</label>
                    <input type="text" name="breed" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Save Animal</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'admin_footer.php'; ?>