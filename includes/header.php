<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Velvet Vogue' : 'Velvet Vogue - Fashion E-commerce'; ?></title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="<?php echo isset($meta_description) ? $meta_description : 'Velvet Vogue - Your premier destination for trendy fashion. Shop the latest styles in casual, formal, and partywear.'; ?>">
    <meta name="keywords" content="<?php echo isset($meta_keywords) ? $meta_keywords : 'fashion, clothing, dresses, casual wear, formal wear, partywear, online shopping'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS for carousels -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <?php if(isset($additional_css)): ?>
        <?php foreach($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-dark text-white py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span><i class="fas fa-phone-alt me-2"></i> +1 234 567 890</span>
                    <span class="ms-3"><i class="fas fa-envelope me-2"></i> info@velvetvogue.com</span>
                </div>
                <div class="col-md-6 text-end">
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="header py-3 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 col-6">
                    <a href="index.php" class="logo text-decoration-none">
                        <h1 class="m-0 fw-bold">Velvet Vogue</h1>
                    </a>
                </div>
                <div class="col-md-5 d-none d-md-block">
                    <form action="products.php" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search for products..." required>
                            <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 col-6 text-end">
                    <div class="header-actions">
                        <?php if(is_logged_in()): ?>
                            <div class="dropdown d-inline-block">
                                <a class="btn btn-link text-dark text-decoration-none dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="account.php">My Account</a></li>
                                    <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                    <li><a class="dropdown-item" href="wishlist.php">My Wishlist</a></li>
                                    <?php if(is_admin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="admin/index.php">Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-link text-dark text-decoration-none"><i class="fas fa-user"></i> Login</a>
                        <?php endif; ?>
                        <a href="wishlist.php" class="btn btn-link text-dark text-decoration-none"><i class="fas fa-heart"></i></a>
                        <a href="cart.php" class="btn btn-link text-dark text-decoration-none position-relative">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo get_cart_item_count(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    
                    <?php
                    // Get categories for navigation
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        $active = (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'active' : '';
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link ' . $active . '" href="category.php?category_id=' . $category['id'] . '">' . $category['name'] . '</a>';
                        echo '</li>';
                    }
                    ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact Us</a>
                    </li>
                </ul>
                <div class="d-flex d-md-none mt-3">
                    <form action="products.php" method="GET" class="search-form w-100">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search for products..." required>
                            <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content py-4">
        <div class="container">
            <?php display_message(); ?>
