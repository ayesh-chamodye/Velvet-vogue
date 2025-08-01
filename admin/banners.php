<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle banner actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = clean_input($_POST['title']);
                $subtitle = clean_input($_POST['subtitle']);
                $link = clean_input($_POST['link']);
                $status = clean_input($_POST['status']);
                $sort_order = (int)$_POST['sort_order'];
                
                // Handle image upload
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../uploads/banners/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $image_url = 'uploads/banners/' . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                        $sql = "INSERT INTO banners (title, subtitle, image_url, link, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "sssssi", $title, $subtitle, $image_url, $link, $status, $sort_order);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $success_message = "Banner added successfully!";
                        } else {
                            $error_message = "Error adding banner.";
                        }
                    }
                }
                break;
                
            case 'update_status':
                $banner_id = (int)$_POST['banner_id'];
                $status = clean_input($_POST['status']);
                
                $sql = "UPDATE banners SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $banner_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Banner status updated!";
                }
                break;
                
            case 'delete':
                $banner_id = (int)$_POST['banner_id'];
                
                // Get image path to delete file
                $sql = "SELECT image_url FROM banners WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $banner_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $banner = mysqli_fetch_assoc($result);
                
                if ($banner && file_exists('../' . $banner['image_url'])) {
                    unlink('../' . $banner['image_url']);
                }
                
                $sql = "DELETE FROM banners WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $banner_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Banner deleted successfully!";
                }
                break;
        }
    }
}

// Get all banners
$sql = "SELECT * FROM banners ORDER BY sort_order ASC, created_at DESC";
$banners = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners Management - Velvet Vogue Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same as other admin pages) -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <!-- Sidebar content -->
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Banners Management</h1>
                    <button class="btn btn-sm btn-purple" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                        <i class="fas fa-plus"></i> Add New Banner
                    </button>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <!-- Banners Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Subtitle</th>
                                        <th>Link</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($banner = mysqli_fetch_assoc($banners)): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                    $img_path = (!empty($banner['image_url']) && file_exists('../' . $banner['image_url']))
                                                        ? '../' . $banner['image_url']
                                                        : '../assets/images/default-banner.jpg';
                                                ?>
                                                <img src="<?php echo $img_path; ?>" alt="Banner" style="width: 80px; height: 40px; object-fit: cover;">
                                            </td>
                                            <td><?php echo htmlspecialchars($banner['title']); ?></td>
                                            <td><?php echo htmlspecialchars($banner['subtitle']); ?></td>
                                            <td>
                                                <a href="<?php echo isset($banner['link']) ? htmlspecialchars($banner['link']) : '#'; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($banner['link']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo $banner['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $banner['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo ucfirst($banner['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $banner['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $banner['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $banner['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $banner['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $banner['id']; ?>">Delete Banner</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete this banner?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Banner Modal -->
    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link URL</label>
                            <input type="url" class="form-control" name="link">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="0">
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
                        <button type="submit" class="btn btn-purple">Add Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>