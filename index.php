<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured products
$sql = "SELECT p.*, 
        (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as review_count
        FROM products p 
        WHERE p.featured = 1 AND p.status = 'active' 
        ORDER BY p.created_at DESC 
        LIMIT 8";

$featured_products = [];
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_products[] = $row;
    }
}

// Get categories for navigation
$categories_sql = "SELECT * FROM categories WHERE status = 'active' AND parent_id IS NULL ORDER BY name LIMIT 6";
$categories_result = mysqli_query($conn, $categories_sql);
$categories = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        $categories[] = $row;
    }
}

// Get banners
$banners_sql = "SELECT * FROM banners WHERE status = 'active' AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY id DESC LIMIT 3";
$banners_result = mysqli_query($conn, $banners_sql);
$banners = [];
if ($banners_result) {
    while ($row = mysqli_fetch_assoc($banners_result)) {
        $banners[] = $row;
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->

<section class="hero-section">
    <div class="crunchyroll-hero">
        <button class="cr-hero-nav cr-hero-prev" aria-label="Previous" onclick="crHeroScroll(-1)"><span>&#10094;</span></button>
        <div class="cr-hero-track-wrapper">
            <div class="cr-hero-track">
                <?php
                $hasBanners = !empty($banners);
                $slides = $hasBanners ? $banners : [
                    [
                        'image_url' => 'assets/images/hero-bg.jpg',
                        'title' => 'Welcome to Velvet Vogue',
                        'subtitle' => 'Discover the latest fashion trends and timeless elegance',
                        'link' => 'products.php'
                    ]
                ];
                foreach ($slides as $banner):
                    $img = !empty($banner['image_url']) ? htmlspecialchars($banner['image_url']) : 'assets/images/hero-bg.jpg';
                ?>
                <div class="cr-hero-slide">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>" class="cr-hero-img">
                    <div class="cr-hero-overlay"></div>
                    <div class="cr-hero-content">
                        <h1 class="cr-hero-title"><?php echo htmlspecialchars($banner['title']); ?></h1>
                        <?php if (!empty($banner['subtitle'])): ?>
                            <p class="cr-hero-subtitle"><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($banner['link'])): ?>
                            <a href="<?php echo htmlspecialchars($banner['link']); ?>" class="btn btn-primary btn-lg">Shop Now</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="cr-hero-nav cr-hero-next" aria-label="Next" onclick="crHeroScroll(1)"><span>&#10095;</span></button>
    </div>
    <style>
    .crunchyroll-hero {
        position: relative;
        width: 100vw;
        max-width: 100%;
        overflow: hidden;
        background: #111;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cr-hero-track-wrapper {
        overflow-x: hidden;
        width: 100vw;
        max-width: 100%;
    }
    .cr-hero-track {
        display: flex;
        transition: transform 0.6s cubic-bezier(.77,0,.18,1);
        will-change: transform;
    }
    .cr-hero-slide {
        min-width: 100vw;
        max-width: 100vw;
        height: 500px;
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        overflow: hidden;
    }
    .cr-hero-img {
        width: 100vw;
        height: 500px;
        object-fit: cover;
        object-position: center;
        position: absolute;
        top: 0; left: 0;
        z-index: 1;
    }
    .cr-hero-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(90deg, rgba(20,20,20,0.85) 0%, rgba(20,20,20,0.2) 60%, rgba(20,20,20,0.0) 100%);
        z-index: 2;
    }
    .cr-hero-content {
        position: relative;
        z-index: 3;
        color: #fff;
        padding: 60px 40px 80px 60px;
        max-width: 600px;
    }
    .cr-hero-title {
        font-size: 2.8rem;
        font-weight: bold;
        margin-bottom: 1rem;
        text-shadow: 0 2px 16px #000a;
    }
    .cr-hero-subtitle {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        text-shadow: 0 2px 8px #000a;
    }
    .cr-hero-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.5);
        border: none;
        color: #fff;
        font-size: 2.5rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        z-index: 10;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .cr-hero-nav:hover {
        background: rgba(0,0,0,0.8);
    }
    .cr-hero-prev { left: 24px; }
    .cr-hero-next { right: 24px; }
    @media (max-width: 768px) {
        .cr-hero-content { padding: 30px 10px 40px 20px; }
        .cr-hero-title { font-size: 1.5rem; }
        .cr-hero-subtitle { font-size: 1rem; }
        .cr-hero-slide, .cr-hero-img { height: 320px; min-width: 100vw; }
    }
    </style>
    <script>
    let crHeroIndex = 0;
    function crHeroScroll(dir) {
        const track = document.querySelector('.cr-hero-track');
        const slides = document.querySelectorAll('.cr-hero-slide');
        crHeroIndex += dir;
        if (crHeroIndex < 0) crHeroIndex = slides.length - 1;
        if (crHeroIndex >= slides.length) crHeroIndex = 0;
        track.style.transform = `translateX(-${crHeroIndex * 100}vw)`;
    }
    // Optional: autoplay
    setInterval(() => {
        crHeroScroll(1);
    }, 5000);
    </script>
</section>

<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Find your perfect style</p>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-2 col-md-4 col-6 mb-4">
                        <div class="category-card">
                            <a href="products.php?category=<?php echo $category['id']; ?>">
                                <div class="category-image">
                                    <img src="<?php echo !empty($category['image_url']) ? htmlspecialchars($category['image_url']) : 'assets/images/categories/default.jpg'; ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                </div>
                                <h5 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h5>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default categories -->
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="category-card">
                        <a href="products.php">
                            <div class="category-image">
                                <img src="assets/images/categories/dresses.jpg" alt="Dresses">
                            </div>
                            <h5 class="category-name">Dresses</h5>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="category-card">
                        <a href="products.php">
                            <div class="category-image">
                                <img src="assets/images/categories/tops.jpg" alt="Tops">
                            </div>
                            <h5 class="category-name">Tops</h5>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="category-card">
                        <a href="products.php">
                            <div class="category-image">
                                <img src="assets/images/categories/bottoms.jpg" alt="Bottoms">
                            </div>
                            <h5 class="category-name">Bottoms</h5>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-products py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Handpicked favorites just for you</p>
            </div>
        </div>
        
        <div class="swiper featured-swiper">
            <div class="swiper-wrapper">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/images/products/default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-actions">
                                        <?php if (is_logged_in()): ?>
                                            <a href="wishlist_add.php?product_id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-heart"></i></a>
                                        <?php endif; ?>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-eye"></i></a>
                                    </div>
                                    <?php if ($product['sale_price']): ?>
                                        <div class="product-badge badge-sale">Sale</div>
                                    <?php elseif (strtotime($product['created_at']) > strtotime('-7 days')): ?>
                                        <div class="product-badge badge-new">New</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                    </h3>
                                    <div class="product-price">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="current-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                            <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($product['avg_rating']): ?>
                                        <div class="product-rating">
                                            <?php
                                            $rating = round($product['avg_rating']);
                                            for ($i = 1; $i <= 5; $i++):
                                            ?>
                                                <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="rating-count">(<?php echo $product['review_count']; ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                    <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default products if none found -->
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="assets/images/products/default.jpg" alt="Product <?php echo $i; ?>">
                                    <div class="product-actions">
                                        <a href="#" class="btn"><i class="fas fa-heart"></i></a>
                                        <a href="#" class="btn"><i class="fas fa-eye"></i></a>
                                    </div>
                                    <div class="product-badge badge-new">New</div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">Sample Product <?php echo $i; ?></h3>
                                    <div class="product-price">
                                        <span class="current-price">$99.99</span>
                                    </div>
                                    <div class="product-rating">
                                        <i class="fas fa-star active"></i>
                                        <i class="fas fa-star active"></i>
                                        <i class="fas fa-star active"></i>
                                        <i class="fas fa-star active"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="rating-count">(12)</span>
                                    </div>
                                    <button class="add-to-cart">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- New Arrivals Section -->
<section class="new-arrivals py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">Fresh styles, just landed</p>
            </div>
        </div>
        
        <div class="row">
            <?php
            // Get new arrivals (latest products)
            $sql = "SELECT p.*, 
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                    (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
                    (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as review_count
                    FROM products p 
                    WHERE p.status = 'active' 
                    ORDER BY p.created_at DESC 
                    LIMIT 8";
            
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/images/products/default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="product-actions">
                                    <?php if (is_logged_in()): ?>
                                        <a href="wishlist_add.php?product_id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-heart"></i></a>
                                    <?php endif; ?>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-eye"></i></a>
                                </div>
                                <div class="product-badge badge-new">New</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($product['sale_price']): ?>
                                        <span class="current-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($product['avg_rating']): ?>
                                    <div class="product-rating">
                                        <?php
                                        $rating = round($product['avg_rating']);
                                        for ($i = 1; $i <= 5; $i++):
                                        ?>
                                            <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="rating-count">(<?php echo $product['review_count']; ?>)</span>
                                    </div>
                                <?php endif; ?>
                                <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // Show default products if none found
                for ($i = 1; $i <= 4; $i++) {
                    ?>
                    <div class="col-md-3 col-6 mb-4">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="assets/images/products/default.jpg" alt="Product <?php echo $i; ?>">
                                <div class="product-actions">
                                    <a href="#" class="btn"><i class="fas fa-heart"></i></a>
                                    <a href="#" class="btn"><i class="fas fa-eye"></i></a>
                                </div>
                                <div class="product-badge badge-new">New</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">Sample Product <?php echo $i; ?></h3>
                                <div class="product-price">
                                    <span class="current-price">$99.99</span>
                                </div>
                                <button class="add-to-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3>Stay Updated</h3>
                <p>Subscribe to our newsletter for the latest fashion trends and exclusive offers.</p>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form" action="newsletter_subscribe.php" method="POST">
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Add to cart function
function addToCart(productId) {
    // Check if user is logged in
    <?php if (is_logged_in()): ?>
        fetch('cart_add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Product added to cart!');
                // Update cart count if exists
                updateCartCount();
            } else {
                alert('Error adding product to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    <?php else: ?>
        // Redirect to login
        window.location.href = 'login.php';
    <?php endif; ?>
}

// Update cart count
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
            }
        });
}

// Initialize Swiper
document.addEventListener('DOMContentLoaded', function() {
    // Hero Swiper
    var heroSwiper = new Swiper('.hero-swiper', {
        slidesPerView: 1,
        loop: true,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 500, // Default fade speed for autoplay
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Use Swiper's default navigation and fade for all controls (fixes disappearing image bug)
    // Featured Swiper
    new Swiper('.featured-swiper', {
        slidesPerView: 1,
        spaceBetween: 10,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 3,
            },
            1024: {
                slidesPerView: 4,
            },
        },
    });
});
</script>






