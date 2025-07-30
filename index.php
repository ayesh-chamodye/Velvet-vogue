
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

<style>
/* Ensure all product cards have the same height for perfect alignment */
.product-card {
    height: 420px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
}
/* Ensure product images have a fixed height and default background */
.product-image {
    min-height: 220px;
    max-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #faf9fa;
    border-radius: 1rem 1rem 0 0;
    overflow: hidden;
    position: relative;
}
.product-image img {
    max-width: 100%;
    max-height: 200px;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}
/* Crunchyroll-style banner slider enhancements */
.hero-section .hero-slider {
    position: relative;
    width: 100%;
    overflow: hidden;
}
.hero-section .swiper {
    width: 100%;
    height: 50%;
}
.hero-section .swiper-slide {
    min-height: 380px;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    position: relative;
    transition: background-image 0.5s cubic-bezier(.4,0,.2,1);
}
/* ...rest of your CSS... */

// ...existing code up to banners array...
.hero-section .swiper-slide::before {
    content: '';
    position: absolute;
    left: 0; top: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, rgba(34,34,34,0.7) 0%, rgba(34,34,34,0.2) 60%, rgba(34,34,34,0) 100%);
    z-index: 1;
}
.hero-section .swiper-pagination-bullets {
    bottom: 30px !important;
}
.hero-section .swiper-pagination-bullet {
    background: #fff;
    opacity: 0.7;
    width: 12px;
    height: 12px;
    margin: 0 6px !important;
    border-radius: 50%;
    transition: background 0.3s, opacity 0.3s;
}
.hero-section .swiper-pagination-bullet-active {
    background: #9c27b0;
    opacity: 1;
}
.hero-section .swiper-button-next, .hero-section .swiper-button-prev {
    color: #fff;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(0,0,0,0.3);
    top: 50%;
    transform: translateY(-50%);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: background 0.2s;
}
.hero-section .swiper-button-next:hover, .hero-section .swiper-button-prev:hover {
    background: rgba(153, 0, 255, 0.8);
}
.hero-section .swiper-button-next:after, .hero-section .swiper-button-prev:after {
    font-size: 1.7rem;
    font-weight: bold;
}
/* Modern, robust styles for hero/banner section */
.hero-section {
    position: relative;
    width: 100%;
    overflow: hidden;
}
.hero-section .hero-slider {
    position: relative;
    width: 100%;
    overflow: hidden;
}
.hero-section .hero-slide {
    min-height: 380px;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    position: relative;
}
.hero-section .hero-slide::before {
    content: '';
    position: absolute;
    left: 0; top: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, rgba(34,34,34,0.7) 0%, rgba(34,34,34,0.2) 60%, rgba(34,34,34,0) 100%);
    z-index: 1;
}
.hero-section .hero-content {
    position: absolute;
    z-index: 2;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.hero-section .glass-bg {
    background: rgba(27, 26, 26, 0.38);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
    backdrop-filter: blur(12px) saturate(160%);
    -webkit-backdrop-filter: blur(12px) saturate(160%);
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.25);
    padding: 2.5rem 2.5rem 2.5rem 2.5rem;
    max-width: 520px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
}
.hero-section .container,
.hero-section .row,
.hero-section .col-lg-6 {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-section .col-lg-6 {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
}
.hero-section .hero-title {
    color: #fff;
    font-size: 2.5rem;
    font-weight: 700;
    text-shadow: 0 2px 8px rgba(0,0,0,0.3);
    margin-bottom: 0.5rem;
    text-align: center;
    width: 100%;
    display: block;
    white-space: normal;
}
.hero-section .hero-subtitle {
    color: #fff;
    font-size: 1.25rem;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    margin-bottom: 1.5rem;
    text-align: center;
    width: 100%;
    display: block;
    white-space: normal;
}
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.hero-section .btn.btn-primary {
    margin-top: 1rem;
    font-size: 1.1rem;
    padding: 0.75rem 2rem;
    border-radius: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
@media (max-width: 991px) {
    .hero-section .hero-title { font-size: 2rem; }
    .hero-section .hero-subtitle { font-size: 1rem; }
    .hero-section .hero-content { padding: 1.5rem 0; }
}
@media (max-width: 575px) {
    .hero-section .hero-title { font-size: 1.3rem; }
    .hero-section .hero-content { padding: 1rem 0; }
}

</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-slider swiper hero-swiper">
        <div class="swiper-wrapper">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $banner): ?>
                    <div class="swiper-slide" style="background-image: url('<?php echo htmlspecialchars($banner['image_url']); ?>');">
                        <div class="hero-content">
                            <div class="glass-bg">
                                <h1 class="hero-title"><?php echo htmlspecialchars($banner['title']); ?></h1>
                                <?php if (!empty($banner['subtitle'])): ?>
                                    <p class="hero-subtitle"><?php echo htmlspecialchars($banner['subtitle']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($banner['link'])): ?>
                                    <a href="<?php echo htmlspecialchars($banner['link']); ?>" class="btn btn-primary btn-lg">Shop Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default hero slide -->
                <div class="swiper-slide" style="background-image: url('assets/images/hero-bg.jpg');">
                    <div class="hero-content">
                        <div class="glass-bg">
                            <h1 class="hero-title">Welcome to Velvet Vogue</h1>
                            <p class="hero-subtitle">Discover the latest fashion trends and timeless elegance</p>
                            <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- Add Pagination and Navigation -->
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
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
                                <div class="d-flex align-items-center justify-content-center gap-2 py-3" style="gap:0.5rem;">
                                    <?php
                                    $cat_icons = [
                                        'Dresses' => 'fa-person-dress',
                                        'Tops' => 'fa-shirt',
                                        'Bottoms' => 'fa-pants',
                                        'Shoes' => 'fa-shoe-prints',
                                        'Accessories' => 'fa-hat-cowboy',
                                        'Bags' => 'fa-bag-shopping',
                                        'Jackets' => 'fa-jacket',
                                        'Kids' => 'fa-child',
                                        'Men' => 'fa-person',
                                        'Women' => 'fa-person-dress',
                                    ];
                                    $icon_class = isset($cat_icons[$category['name']]) ? $cat_icons[$category['name']] : 'fa-tags';
                                    ?>
                                    <i class="fa-solid <?php echo $icon_class; ?> fa-lg text-secondary"></i>
                                    <h5 class="category-name mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                                </div>
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
                <a href="products.php" class="btn btn-outline-primary mt-3">Show All Products</a>
            </div>
        </div>
        <div class="swiper featured-swiper">
            <div class="swiper-wrapper">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="swiper-slide">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/images/products/default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='assets/images/products/default.jpg';">
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
                                    <img src="assets/images/products/default.jpg" alt="Product <?php echo $i; ?>" onerror="this.onerror=null;this.src='assets/images/products/default.jpg';">
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
                <a href="products.php" class="btn btn-outline-primary mt-3">Show All Products</a>
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
                                <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/images/products/default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null;this.src='assets/images/products/default.jpg';">
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
                                <img src="assets/images/products/default.jpg" alt="Product <?php echo $i; ?>" onerror="this.onerror=null;this.src='assets/images/products/default.jpg';">
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

// Initialize Swiper for hero/banner (Crunchyroll style)
document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.hero-swiper', {
        slidesPerView: 1,
        loop: true,
        effect: 'slide',
        speed: 700,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
    });
    // Featured products swiper (unchanged)
    new Swiper('.featured-swiper', {
        slidesPerView: 1,
        spaceBetween: 30,
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






