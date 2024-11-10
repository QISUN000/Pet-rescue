<?php
require_once '../config/database.php';
require_once 'header.php';

$db = Database::getInstance()->getConnection();

// Pagination settings
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get categories for filter dropdown
$sql = "SELECT * FROM categories";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle search
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Count total results
$count_sql = "SELECT COUNT(DISTINCT a.id) as total FROM animals a 
              LEFT JOIN animal_categories ac ON a.id = ac.animal_id 
              WHERE 1=1";
$params = [];

if ($keyword) {
    $count_sql .= " AND (a.name LIKE :keyword OR a.description LIKE :keyword OR a.breed LIKE :keyword)";
    $params[':keyword'] = "%$keyword%";
}

if ($category) {
    $count_sql .= " AND ac.category_id = :category";
    $params[':category'] = $category;
}

$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_results = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_results / $items_per_page);

$sql = "SELECT DISTINCT a.*, i.filename FROM animals a 
        LEFT JOIN animal_categories ac ON a.id = ac.animal_id 
        LEFT JOIN images i ON a.id = i.animal_id
        WHERE 1=1";

$params = [];

if ($keyword) {
    $sql .= " AND (a.name LIKE :keyword OR a.description LIKE :keyword OR a.breed LIKE :keyword)";
    $params[':keyword'] = "%$keyword%";
}

if ($category) {
    $sql .= " AND ac.category_id = :category";
    $params[':category'] = $category;
}

$sql .= " LIMIT :offset, :limit";  // Use named parameters
$params[':offset'] = (int)$offset;
$params[':limit'] = (int)$items_per_page;

$stmt = $db->prepare($sql);
foreach($params as $key => &$val) {
    if($key == ':offset' || $key == ':limit') {
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $val);
    }
}
$stmt->execute();
$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container my-5">
    <h2 class="mb-4">Find the perfect pet</h2>
    <div class="mb-4">Let us help you find the pet that truly needs you.</div>
    
    <form method="GET" class="mb-5">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" name="keyword" class="form-control" 
                       placeholder="Search by name, breed..." 
                       value="<?= htmlspecialchars($keyword) ?>">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Search Now</button>
            </div>
            <?php if ($keyword || $category): ?>
                <div class="col-md-3">
                    <a href="search.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($total_results > 0): ?>
        <h3 class="mb-4">Featured Pets (<?= $total_results ?> found)</h3>
        <div class="row g-4">
            <?php foreach ($animals as $animal): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <?php if ($animal['filename']): ?>
                            <img src="uploads/<?= htmlspecialchars($animal['filename']) ?>" 
                                 class="card-img-top" 
                                 style="height: 300px; object-fit: cover;"
                                 alt="<?= htmlspecialchars($animal['name']) ?>">
                        <?php else: ?>
                            <div class="bg-light" style="height: 300px;"></div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($animal['name']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?= htmlspecialchars($animal['breed']) ?> • 
                                    <?= htmlspecialchars($animal['species']) ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>&keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>&keyword=<?= urlencode($keyword) ?>&category=<?= urlencode($category) ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center my-5">
            <h3>No pets found</h3>
            <p class="text-muted">Try adjusting your search criteria</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>