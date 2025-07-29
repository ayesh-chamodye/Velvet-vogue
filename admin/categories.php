<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = clean_input($_POST['name']);
                $description = clean_input($_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $status = clean_input($_POST['status']);
                
                $sql = "INSERT INTO categories (name, description, parent_id, status, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssis", $name, $description, $parent_id, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Category added successfully!";
                } else {
                    $error_message = "Error adding category.";
                }
                break;
                
            case 'update':
                $category_id = (int)$_POST['category_id'];
                $name = clean_input($_POST['name']);
                $description = clean_input($_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $status = clean_input($_POST['status']);
                
                $sql = "UPDATE categories SET name = ?, description = ?, parent_id = ?, status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssisi", $name, $description, $parent_id, $status, $category_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Category updated successfully!";
                } else {
                    $error_message = "Error updating category.";
                }
                break;
                
            case 'delete':
                $category_id = (int)$_POST['category_id'];
                
                // Check if category has products
                $check_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($stmt, "i", $category_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $count = mysqli_fetch_assoc($result)['count'];
                
                if ($count > 0) {
                    $error_message = "Cannot delete category. It has products assigned to it.";
                } else {
                    $sql = "DELETE FROM categories WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $category_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = "Category deleted successfully!";
                    } else {
                        $error_message = "Error deleting category.";
                    }
                }
                break;
        }
    }
}

// Get all categories
$sql = "SELECT c.*, p.name as parent_name, 
        (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
        FROM categories c 
        LEFT JOIN categories p ON c.parent_id = p.id 
        ORDER BY c.name";
$categories = mysqli_query($conn, $sql);

// Get parent categories for dropdown
$parent_categories = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");

$page_title = "Categories Management";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Categories Management</h1>
    <button class="btn btn-sm btn-purple" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Categories Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Parent Category</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $category['product_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo $category['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($category['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $category['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Category Name</label>
                                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Parent Category</label>
                                                <select class="form-select" name="parent_id">
                                                    <option value="">None (Main Category)</option>
                                                    <?php 
                                                    mysqli_data_seek($parent_categories, 0);
                                                    while ($parent = mysqli_fetch_assoc($parent_categories)): 
                                                        if ($parent['id'] != $category['id']): // Don't allow self as parent
                                                    ?>
                                                        <option value="<?php echo $parent['id']; ?>" <?php echo $category['parent_id'] == $parent['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($parent['name']); ?>
                                                        </option>
                                                    <?php 
                                                        endif;
                                                    endwhile; 
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-purple">Update Category</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $category['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <p>Are you sure you want to delete the category "<?php echo htmlspecialchars($category['name']); ?>"?</p>
                                            <?php if ($category['product_count'] > 0): ?>
                                                <div class="alert alert-warning">
                                                    <strong>Warning:</strong> This category has <?php echo $category['product_count']; ?> products. You cannot delete it.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <?php if ($category['product_count'] == 0): ?>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select class="form-select" name="parent_id">
                            <option value="">None (Main Category)</option>
                            <?php 
                            mysqli_data_seek($parent_categories, 0);
                            while ($parent = mysqli_fetch_assoc($parent_categories)): 
                            ?>
                                <option value="<?php echo $parent['id']; ?>">
                                    <?php echo htmlspecialchars($parent['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-purple">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

