<?php
// Include functions
require_once 'includes/functions.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product information
$product = get_product($product_id);

// Redirect if product not found
if (!$product) {
    set_message('Product not found.', 'danger');
    redirect('index.php');
}

// Get product images
$product_images = get_product_images($product_id);

// Get product attributes
$product_attributes = get_product_attributes($product_id);

// Organize attributes by type
$attributes_by_type = [];
foreach ($product_attributes as $attr) {
    if (!isset($attributes_by_type[$attr['attribute_name']])) {
        $attributes_by_type[$attr['attribute_name']] = [];
    }
    $attributes_by_type[$attr['attribute_name']][] = $attr;
}

// Get related products
$related_products = [];
if ($product['category_id']) {
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image 
            FROM products p 
            WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
            ORDER BY RAND() 
            LIMIT 4";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $product['category_id'], $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $related_products[] = $row;
    }
}

// Get product reviews
$reviews = [];
$sql = "SELECT r.*, u.username, u.first_name, u.last_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? AND r.status = 'approved' 
        ORDER BY r.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
}

// Calculate average rating
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $sum = 0;
    foreach ($reviews as $review) {
        $sum += $review['rating'];
    }
    $avg_rating = $sum / $total_reviews;
}

// Set page title and meta information
$page_title = $product['name'];
$meta_description = substr(strip_tags($product['description']), 0, 160);
$meta_keywords = "fashion, clothing, {$product['name']}, {$product['category_name']}";

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <?php if ($product['category_id']): ?>
                <li class="breadcrumb-item"><a href="category.php?category_id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
        </ol>
    </div>
</nav>

<!-- Product Detail -->
<section class="product-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6 mb-4">
                <div class="product-gallery">
                    <?php if (empty($product_images)): ?>
                        <img src="assets/images/products/default.jpg" alt="<?php echo $product['name']; ?>" class="product-main-image" id="mainProductImage">
                    <?php else: ?>
                        <img src="<?php echo $product_images[0]['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-main-image" id="mainProductImage">
                        
                        <?php if (count($product_images) > 1): ?>
                            <div class="product-thumbnails mt-3">
                                <?php foreach ($product_images as $index => $image): ?>
                                    <img src="<?php echo $image['image_url']; ?>" alt="<?php echo $product['name']; ?> - Image <?php echo $index + 1; ?>" class="product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-detail-info">
                    <h1><?php echo $product['name']; ?></h1>
                    
                    <div class="product-detail-price mb-3">
                        <?php if ($product['sale_price']): ?>
                            <span class="current-price"><?php echo format_price($product['sale_price']); ?></span>
                            <span class="old-price"><?php echo format_price($product['price']); ?></span>
                            <span class="discount-badge">
                                <?php 
                                $discount = (($product['price'] - $product['sale_price']) / $product['price']) * 100;
                                echo round($discount) . '% OFF';
                                ?>
                            </span>
                        <?php else: ?>
                            <span class="current-price"><?php echo format_price($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-detail-rating mb-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $avg_rating): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="ms-2"><?php echo $total_reviews; ?> Reviews</span>
                    </div>
                    
                    <div class="product-detail-description mb-4">
                        <?php echo $product['description']; ?>
                    </div>
                    
                    <div class="product-detail-meta mb-4">
                        <p><strong>SKU:</strong> <?php echo $product['sku']; ?></p>
                        <p><strong>Category:</strong> <a href="category.php?category_id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></p>
                        <p><strong>Availability:</strong> 
                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-success">In Stock (<?php echo $product['stock']; ?>)</span>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <form action="cart_add.php" method="POST" class="product-detail-form">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <?php if (!empty($attributes_by_type)): ?>
                            <div class="product-detail-options mb-4">
                                <?php foreach ($attributes_by_type as $attr_name => $attrs): ?>
                                    <div class="mb-3">
                                        <label class="option-label"><?php echo ucfirst($attr_name); ?>:</label>
                                        
                                        <?php if ($attr_name == 'color'): ?>
                                            <div class="color-options">
                                                <?php foreach ($attrs as $index => $attr): ?>
                                                    <div class="color-option <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                         style="background-color: <?php echo $attr['attribute_value']; ?>;" 
                                                         data-value="<?php echo $attr['attribute_value']; ?>">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="hidden" name="attributes[color]" value="<?php echo $attrs[0]['attribute_value']; ?>">
                                        <?php elseif ($attr_name == 'size'): ?>
                                            <div class="size-options">
                                                <?php foreach ($attrs as $index => $attr): ?>
                                                    <div class="size-option <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                         data-value="<?php echo $attr['attribute_value']; ?>">
                                                        <?php echo $attr['attribute_value']; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="hidden" name="attributes[size]" value="<?php echo $attrs[0]['attribute_value']; ?>">
                                        <?php else: ?>
                                            <select name="attributes[<?php echo $attr_name; ?>]" class="form-select">
                                                <?php foreach ($attrs as $attr): ?>
                                                    <option value="<?php echo $attr['attribute_value']; ?>">
                                                        <?php echo $attr['attribute_value']; ?>
                                                        <?php if ($attr['price_adjustment'] > 0): ?>
                                                            (+ <?php echo format_price($attr['price_adjustment']); ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-detail-quantity mb-4">
                            <button type="button" class="btn btn-outline-secondary quantity-decrease"><i class="fas fa-minus"></i></button>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="form-control quantity-input">
                            <button type="button" class="btn btn-outline-secondary quantity-increase"><i class="fas fa-plus"></i></button>
                        </div>
                        
                        <div class="product-detail-actions">
                            <button type="submit" class="btn btn-primary" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                            <a href="wishlist_add.php?product_id=<?php echo $product_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-heart me-2"></i> Add to Wishlist
                            </a>
                        </div>
                    </form>
                    
                    <div class="product-detail-share mt-4">
                        <p class="mb-2"><strong>Share:</strong></p>
                        <div class="social-share">
                            <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-pinterest"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="product-tabs mt-5">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Specifications</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews (<?php echo $total_reviews; ?>)</button>
                </li>
            </ul>
            <div class="tab-content p-4 border border-top-0" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <?php echo $product['description']; ?>
                </div>
                <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>SKU</th>
                                <td><?php echo $product['sku']; ?></td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td><?php echo $product['category_name']; ?></td>
                            </tr>
                            <?php if (!empty($attributes_by_type)): ?>
                                <?php foreach ($attributes_by_type as $attr_name => $attrs): ?>
                                    <tr>
                                        <th><?php echo ucfirst($attr_name); ?> Options</th>
                                        <td>
                                            <?php 
                                            $values = array_map(function($attr) {
                                                return $attr['attribute_value'];
                                            }, $attrs);
                                            echo implode(', ', $values);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php if (empty($reviews)): ?>
                                <p>There are no reviews yet.</p>
                            <?php else: ?>
                                <div class="reviews-list">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="review-item mb-4 pb-4 border-bottom">
                                            <div class="d-flex justify-content-between mb-2">
                                                <div>
                                                    <h5 class="mb-0"><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></h5>
                                                    <div class="text-muted small">
                                                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['rating']): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="review-content">
                                                <?php echo $review['comment']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (is_logged_in()): ?>
                                <div class="review-form mt-4">
                                    <h4>Write a Review</h4>
                                    <form action="review_add.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Your Rating</label>
                                            <div class="rating-select">
                                                <div class="stars">
                                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="rating-<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                                        <label for="rating-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="reviewComment" class="form-label">Your Review</label>
                                            <textarea class="form-control" id="reviewComment" name="comment" rows="4" required></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-4">
                                    Please <a href="login.php">login</a> to write a review.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="review-summary">
                                <h4>Customer Reviews</h4>
                                <div class="average-rating mb-3">
                                    <div class="display-4 fw-bold"><?php echo number_format($avg_rating, 1); ?></div>
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $avg_rating): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="text-muted"><?php echo $total_reviews; ?> reviews</div>
                                </div>
                                
                                <?php
                                // Rating breakdown
                                $rating_counts = [0, 0, 0, 0, 0];
                                foreach ($reviews as $review) {
                                    $rating_counts[$review['rating'] - 1]++;
                                }
                                ?>
                                
                                <div class="rating-breakdown">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <div class="rating-bar d-flex align-items-center mb-2">
                                            <div class="rating-label me-2"><?php echo $i; ?> <i class="fas fa-star"></i></div>
                                            <div class="progress flex-grow-1 me-2">
                                                <?php 
                                                $percentage = $total_reviews > 0 ? ($rating_counts[$i - 1] / $total_reviews) * 100 : 0;
                                                ?>
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="rating-count"><?php echo $rating_counts[$i - 1]; ?></div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <section class="related-products mt-5">
                <h3 class="mb-4">Related Products</h3>
                <div class="row">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-md-3 col-6 mb-4">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo !empty($related['primary_image']) ? $related['primary_image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $related['name']; ?>">
                                    <div class="product-actions">
                                        <a href="wishlist_add.php?product_id=<?php echo $related['id']; ?>" class="btn"><i class="fas fa-heart"></i></a>
                                        <a href="product.php?id=<?php echo $related['id']; ?>" class="btn"><i class="fas fa-eye"></i></a>
                                    </div>
                                    <?php if ($related['sale_price']): ?>
                                        <div class="product-badge badge-sale">Sale</div>
                                    <?php elseif (strtotime($related['created_at']) > strtotime('-7 days')): ?>
                                        <div class="product-badge badge-new">New</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="product.php?id=<?php echo $related['id']; ?>"><?php echo $related['name']; ?></a>
                                    </h3>
                                    <div class="product-price">
                                        <?php if ($related['sale_price']): ?>
                                            <span class="current-price"><?php echo format_price($related['sale_price']); ?></span>
                                            <span class="old-price"><?php echo format_price($related['price']); ?></span>
                                        <?php else: ?>
                                            <span class="current-price"><?php echo format_price($related['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <form action="cart_add.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $related['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="add-to-cart">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
