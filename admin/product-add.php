<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Get all categories for dropdown
$categories = [];
$cat_sql = "SELECT id, name FROM categories ORDER BY name";
$cat_result = mysqli_query($conn, $cat_sql);
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $price = (float)$_POST['price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $status = clean_input($_POST['status']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $sku = clean_input($_POST['sku']);
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $dimensions = clean_input($_POST['dimensions']);
    
    // Validate required fields
    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = "Please fill in all required fields correctly.";
    } else {
        // Insert product into database
        $sql = "INSERT INTO products (name, description, price, sale_price, stock, category_id, status, featured, sku, weight, dimensions, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssddisssids", $name, $description, $price, $sale_price, $stock, $category_id, $status, $featured, $sku, $weight, $dimensions);
        
        if (mysqli_stmt_execute($stmt)) {
            $product_id = mysqli_insert_id($conn);
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../assets/img/products/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Process each uploaded image
                $total_files = count($_FILES['images']['name']);
                
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['images']['tmp_name'][$i];
                        $name = basename($_FILES['images']['name'][$i]);
                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                        
                        // Generate unique filename
                        $new_filename = 'product_' . $product_id . '_' . uniqid() . '.' . $extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            // Save image info to database
                            $image_url = 'assets/img/products/' . $new_filename;
                            $is_primary = ($i === 0) ? 1 : 0; // First image is primary
                            
                            $img_sql = "INSERT INTO product_images (product_id, image_url, is_primary, created_at) 
                                        VALUES (?, ?, ?, NOW())";
                            $img_stmt = mysqli_prepare($conn, $img_sql);
                            mysqli_stmt_bind_param($img_stmt, "isi", $product_id, $image_url, $is_primary);
                            mysqli_stmt_execute($img_stmt);
                        }
                    }
                }
            }
            
            // Handle product attributes
            if (isset($_POST['attribute_name']) && is_array($_POST['attribute_name'])) {
                for ($i = 0; $i < count($_POST['attribute_name']); $i++) {
                    if (!empty($_POST['attribute_name'][$i]) && !empty($_POST['attribute_value'][$i])) {
                        $attr_name = clean_input($_POST['attribute_name'][$i]);
                        $attr_value = clean_input($_POST['attribute_value'][$i]);
                        
                        $attr_sql = "INSERT INTO product_attributes (product_id, attribute_name, attribute_value, created_at, updated_at) 
                                    VALUES (?, ?, ?, NOW(), NOW())";
                        $attr_stmt = mysqli_prepare($conn, $attr_sql);
                        mysqli_stmt_bind_param($attr_stmt, "iss", $product_id, $attr_name, $attr_value);
                        mysqli_stmt_execute($attr_stmt);
                    }
                }
            }
            
            $success = "Product added successfully!";
            // Redirect to product edit page after short delay
            header("refresh:2;url=product-edit.php?id=$product_id");
        } else {
            $error = "Failed to add product. Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Velvet Vogue Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6a1b9a;
            --secondary-color: #9c27b0;
            --light-color: #f3e5f5;
            --dark-color: #4a148c;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .brand {
            background-color: var(--dark-color);
            padding: 1rem;
        }
        
        .content-wrapper {
            background-color: #f8f9fa;
        }
        
        .form-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-purple {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-purple:hover {
            background-color: var(--dark-color);
            color: white;
        }
        
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="brand d-flex align-items-center mb-3">
                    <h4 class="mb-0">Velvet Vogue</h4>
                </div>
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-3">
                        <div class="mb-2">
                            <i class="fas fa-user-circle fa-3x"></i>
                        </div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></h6>
                        <small>Administrator</small>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers.php">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="banners.php">
                                <i class="fas fa-images"></i> Banners
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reviews.php">
                                <i class="fas fa-star"></i> Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link bg-danger text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Product</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="products.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-8">
                            <div class="card form-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">
                                            Please provide a product name.
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                                        <div class="invalid-feedback">
                                            Please provide a product description.
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                            <div class="invalid-feedback">
                                                Please provide a valid price.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="sale_price" class="form-label">Sale Price ($)</label>
                                            <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0">
                                            <small class="text-muted">Leave empty if not on sale</small>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                            <div class="invalid-feedback">
                                                Please provide stock quantity.
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="sku" class="form-label">SKU</label>
                                            <input type="text" class="form-control" id="sku" name="sku">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Images -->
                            <div class="card form-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Product Images</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="images" class="form-label">Upload Images</label>
                                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" onchange="previewImages(this)">
                                        <small class="text-muted">You can select multiple images. First image will be the primary image.</small>
                                    </div>
                                    <div id="imagePreviewContainer" class="d-flex flex-wrap mt-3"></div>
                                </div>
                            </div>
                            
                            <!-- Product Attributes -->
                            <div class="card form-card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Product Attributes</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAttributeBtn">
                                        <i class="fas fa-plus"></i> Add Attribute
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="attributesContainer">
                                        <div class="row attribute-row mb-3">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="attribute_name[]" placeholder="Attribute Name (e.g., Color)">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="attribute_value[]" placeholder="Attribute Value (e.g., Red)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger remove-attribute">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sidebar -->
                        <div class="col-md-4">
                            <!-- Product Organization -->
                            <div class="card form-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Organization</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a category.
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="featured" name="featured">
                                        <label class="form-check-label" for="featured">Featured Product</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Details -->
                            <div class="card form-card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Additional Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" class="form-control" id="weight" name="weight" step="0.01" min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dimensions" class="form-label">Dimensions (L x W x H)</label>
                                        <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="e.g., 10 x 5 x 3 cm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Save Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-purple btn-lg">
                                    <i class="fas fa-save"></i> Save Product
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Image preview
        function previewImages(input) {
            const previewContainer = document.getElementById('imagePreviewContainer');
            previewContainer.innerHTML = '';
            
            if (input.files) {
                for (let i = 0; i < input.files.length; i++) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('image-preview');
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(input.files[i]);
                }
            }
        }
        
        // Add/Remove Attributes
        document.getElementById('addAttributeBtn').addEventListener('click', function() {
            const container = document.getElementById('attributesContainer');
            const attributeRow = document.createElement('div');
            attributeRow.className = 'row attribute-row mb-3';
            attributeRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attribute_name[]" placeholder="Attribute Name (e.g., Color)">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attribute_value[]" placeholder="Attribute Value (e.g., Red)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger remove-attribute">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(attributeRow);
            
            // Add event listener to the new remove button
            attributeRow.querySelector('.remove-attribute').addEventListener('click', function() {
                container.removeChild(attributeRow);
            });
        });
        
        // Add event listener to existing remove buttons
        document.querySelectorAll('.remove-attribute').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.attribute-row');
                row.parentNode.removeChild(row);
            });
        });
    </script>
</body>
</html>
