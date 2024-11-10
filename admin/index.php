<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

// Fetch all animals with their images and categories
$sql = "SELECT a.*, i.filename, c.name as category_name 
        FROM animals a 
        LEFT JOIN images i ON a.id = i.animal_id 
        LEFT JOIN animal_categories ac ON a.id = ac.animal_id
        LEFT JOIN categories c ON ac.category_id = c.id
        ORDER BY a.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Animals</h2>
        <a href="create.php" class="btn btn-success">Add New Animal</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animals as $animal): ?>
                            <tr>
                                <td>
                                    <?php if ($animal['filename']): ?>
                                        <img src="../public/uploads/<?= htmlspecialchars($animal['filename']) ?>" 
                                             alt="<?= htmlspecialchars($animal['name']) ?>"
                                             style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light" style="width: 50px; height: 50px;"></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($animal['name']) ?></td>
                                <td><?= htmlspecialchars($animal['species']) ?></td>
                                <td><?= htmlspecialchars($animal['breed']) ?></td>
                                <td><?= htmlspecialchars($animal['category_name'] ?? 'No Category') ?></td>
                                <td>
                                    <span class="badge <?= $animal['status'] === 'available' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars($animal['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $animal['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            data-id="<?= $animal['id'] ?>"
                                            data-name="<?= htmlspecialchars($animal['name']) ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Animal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="deleteAnimalName"></span>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="delete.php" class="d-inline">
                    <input type="hidden" name="id" id="deleteAnimalId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete modal
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            
            deleteModal.querySelector('#deleteAnimalId').value = id;
            deleteModal.querySelector('#deleteAnimalName').textContent = name;
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>