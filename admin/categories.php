<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once 'admin_header.php';

$db = Database::getInstance()->getConnection();

// Fetch all categories with animal count
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

    <!-- Success/Error Message Div -->
    <div id="message-container"></div>

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
                            <tr id="category-row-<?= $category['id'] ?>">
                                <td class="category-name"><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= $category['animal_count'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-category" 
                                            data-id="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['name']) ?>">
                                        Edit
                                    </button>
                                    <?php if ($category['animal_count'] == 0): ?>
                                        <button class="btn btn-sm btn-danger delete-category"
                                                data-id="<?= $category['id'] ?>"
                                                data-name="<?= htmlspecialchars($category['name']) ?>">
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
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="add-category-name" class="form-control" required>
                    <div class="invalid-feedback">Please enter a category name.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add-category">Add Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-category-id">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="edit-category-name" class="form-control" required>
                    <div class="invalid-feedback">Please enter a category name.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-category">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="delete-category-name"></span>?</p>
                <input type="hidden" id="delete-category-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add Category
    $('#add-category').click(function() {
        const name = $('#add-category-name').val();
        
        if (!name.trim()) {
            $('#add-category-name').addClass('is-invalid');
            return;
        }

        $.ajax({
            url: 'add_category.php',
            method: 'POST',
            data: { name: name },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $('#message-container').html(
                        `<div class="alert alert-danger alert-dismissible fade show">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`
                    );
                }
            },
            error: function() {
                $('#message-container').html(
                    `<div class="alert alert-danger alert-dismissible fade show">
                        An error occurred while adding the category.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`
                );
            }
        });
    });

    // Edit Category
    $('.edit-category').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#edit-category-id').val(id);
        $('#edit-category-name').val(name);
        $('#editCategoryModal').modal('show');
    });

    $('#save-category').click(function() {
        const id = $('#edit-category-id').val();
        const name = $('#edit-category-name').val();
        
        if (!name.trim()) {
            $('#edit-category-name').addClass('is-invalid');
            return;
        }

        $.ajax({
            url: 'update_category.php',
            method: 'POST',
            data: { 
                id: id,
                name: name 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`#category-row-${id} .category-name`).text(name);
                    $(`#category-row-${id} .edit-category`).data('name', name);
                    
                    $('#message-container').html(
                        `<div class="alert alert-success alert-dismissible fade show">
                            Category updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`
                    );
                    
                    $('#editCategoryModal').modal('hide');
                } else {
                    $('#message-container').html(
                        `<div class="alert alert-danger alert-dismissible fade show">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`
                    );
                }
            },
            error: function() {
                $('#message-container').html(
                    `<div class="alert alert-danger alert-dismissible fade show">
                        An error occurred while updating the category.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`
                );
            }
        });
    });

    // Delete Category
    $('.delete-category').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#delete-category-id').val(id);
        $('#delete-category-name').text(name);
        $('#deleteCategoryModal').modal('show');
    });

    $('#confirm-delete').click(function() {
        const id = $('#delete-category-id').val();
        
        $.ajax({
            url: 'delete_category.php',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`#category-row-${id}`).remove();
                    $('#deleteCategoryModal').modal('hide');
                    
                    $('#message-container').html(
                        `<div class="alert alert-success alert-dismissible fade show">
                            Category deleted successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`
                    );
                } else {
                    $('#message-container').html(
                        `<div class="alert alert-danger alert-dismissible fade show">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>`
                    );
                }
            },
            error: function() {
                $('#message-container').html(
                    `<div class="alert alert-danger alert-dismissible fade show">
                        An error occurred while deleting the category.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`
                );
            }
        });
    });

    // Clear validation on input
    $('#add-category-name, #edit-category-name').on('input', function() {
        $(this).removeClass('is-invalid');
    });

    // Reset forms when modals are closed
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('input:not([type=hidden])').val('').removeClass('is-invalid');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>