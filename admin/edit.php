<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

// Fetch all categories
$sql = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $species = filter_input(INPUT_POST, 'species', FILTER_SANITIZE_STRING);
    $breed = filter_input(INPUT_POST, 'breed', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    try {
        $db->beginTransaction();

        // Update animal info
        $sql = "UPDATE animals SET 
                name = :name, 
                species = :species, 
                breed = :breed, 
                description = :description,
                status = :status
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':species' => $species,
            ':breed' => $breed,
            ':description' => $description,
            ':status' => $status,
            ':id' => $id
        ]);

        // Update category
        $sql = "DELETE FROM animal_categories WHERE animal_id = :animal_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':animal_id' => $id]);

        if ($category_id) {
            $sql = "INSERT INTO animal_categories (animal_id, category_id) VALUES (:animal_id, :category_id)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':animal_id' => $id,
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
                    // Delete old image if exists
                    $sql = "SELECT filename FROM images WHERE animal_id = :animal_id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':animal_id' => $id]);
                    $old_image = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($old_image && file_exists('../public/uploads/' . $old_image['filename'])) {
                        unlink('../public/uploads/' . $old_image['filename']);
                    }

                    // Update or insert new image
                    $sql = "INSERT INTO images (animal_id, filename) 
                           VALUES (:animal_id, :filename) 
                           ON DUPLICATE KEY UPDATE filename = :filename";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':animal_id' => $id,
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

// Fetch animal data with category
$sql = "SELECT a.*, i.filename, ac.category_id 
        FROM animals a 
        LEFT JOIN images i ON a.id = i.animal_id 
        LEFT JOIN animal_categories ac ON a.id = ac.animal_id
        WHERE a.id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    header('Location: index.php');
    exit();
}
?>

<div class="container my-4">
    <h2>Edit Animal</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($animal['name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Species</label>
                    <select name="species" class="form-control" required>
                        <option value="cat" <?= $animal['species'] === 'cat' ? 'selected' : '' ?>>Cat</option>
                        <option value="dog" <?= $animal['species'] === 'dog' ? 'selected' : '' ?>>Dog</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= $animal['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Breed</label>
                    <input type="text" name="breed" class="form-control" 
                           value="<?= htmlspecialchars($animal['breed']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($animal['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="available" <?= $animal['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="adopted" <?= $animal['status'] === 'adopted' ? 'selected' : '' ?>>Adopted</option>
                    </select>
                </div>

                <?php if ($animal['filename']): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img src="../public/uploads/<?= htmlspecialchars($animal['filename']) ?>" 
                             alt="Animal" style="max-width: 200px" class="d-block mb-2">
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">New Image (leave empty to keep current)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Update Animal</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once 'admin_footer.php'; ?>