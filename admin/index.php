<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column
$allowed_sorts = ['name', 'created_at', 'status'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'created_at';
}

// Validate order
$allowed_orders = ['ASC', 'DESC'];
if (!in_array(strtoupper($order), $allowed_orders)) {
    $order = 'DESC';
}

// Get the opposite order for toggle
$toggle_order = ($order === 'ASC') ? 'DESC' : 'ASC';

// Fetch all animals with their images and categories
$sql = "SELECT a.*, i.filename, c.name as category_name 
        FROM animals a 
        LEFT JOIN images i ON a.id = i.animal_id 
        LEFT JOIN animal_categories ac ON a.id = ac.animal_id
        LEFT JOIN categories c ON ac.category_id = c.id
        ORDER BY a.$sort $order";
$stmt = $db->prepare($sql);
$stmt->execute();
$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to show sort indicator
function getSortIndicator($column, $currentSort, $currentOrder) {
    if ($column === $currentSort) {
        return $currentOrder === 'ASC' ? ' ↑' : ' ↓';
    }
    return '';
}
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
                            <th>
                                <a href="?sort=name&order=<?= ($sort === 'name') ? $toggle_order : 'ASC' ?>" 
                                   class="text-decoration-none text-dark">
                                    Name<?= getSortIndicator('name', $sort, $order) ?>
                                </a>
                            </th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Category</th>
                            <th>
                                <a href="?sort=status&order=<?= ($sort === 'status') ? $toggle_order : 'ASC' ?>" 
                                   class="text-decoration-none text-dark">
                                    Status<?= getSortIndicator('status', $sort, $order) ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=created_at&order=<?= ($sort === 'created_at') ? $toggle_order : 'DESC' ?>" 
                                   class="text-decoration-none text-dark">
                                    Created<?= getSortIndicator('created_at', $sort, $order) ?>
                                </a>
                            </th>
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
                                    <span class="badge <?= $animal['status'] === 'available' ? 'bg-success' : 
                                        ($animal['status'] === 'pending' ? 'bg-warning' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars(ucfirst($animal['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($animal['created_at'])) ?></td>
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

<!-- Rest of your code (delete modal, etc.) -->

<?php require_once 'admin_footer.php'; ?>