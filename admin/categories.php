<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $sql = "INSERT INTO categories (name) VALUES (:name)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':name' => $name]);
                break;

            case 'edit':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $sql = "UPDATE categories SET name = :name WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':name' => $name, ':id' => $id]);
                break;

            case 'delete':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $sql = "DELETE FROM categories WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':id' => $id]);
                break;
        }
        // Redirect to prevent form resubmission
        header('Location: categories.php');
        exit();
    }
}

// Fetch all categories
$sql = "SELECT c.*, COUNT(ac.animal_id) as animal_count 
        FROM categories c 
        LEFT JOIN animal_categories ac ON c.id = ac.category_id 
        GROUP BY c.id 
        ORDER BY c.name";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Categories</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            Add New Category
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Animals</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= $category['animal_count'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-category" 
                                            data-id="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['name']) ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCategoryModal">
                                        Edit
                                    </button>
                                    <?php if ($category['animal_count'] == 0): ?>
                                        <button class="btn btn-sm btn-danger delete-category"
                                                data-id="<?= $category['id'] ?>"
                                                data-name="<?= htmlspecialchars($category['name']) ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteCategoryModal">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-category-id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" id="edit-category-name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete-category-id">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="delete-category-name"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Category Modal
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            document.getElementById('edit-category-id').value = id;
            document.getElementById('edit-category-name').value = name;
        });
    });

    // Handle Delete Category Modal
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            document.getElementById('delete-category-id').value = id;
            document.getElementById('delete-category-name').textContent = name;
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>