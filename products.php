<?php
// Page title
$page_title = 'Products';
include 'includes/header.php';
// Get categories for filter
$categories = get_categories();

// Get search and filter params
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : '';

$filters = [];
if ($category_id) $filters['category_id'] = $category_id;
if ($min_price !== '') $filters['min_price'] = $min_price;
if ($max_price !== '') $filters['max_price'] = $max_price;

$products = search_products($q, $filters, 24, 0);
?>

<div class="row mb-4">
    <div class="col-12">
        <form class="row g-2 align-items-end" method="get" action="products.php">
            <div class="col-md-4">
                <label for="q" class="form-label">Search</label>
                <input type="text" class="form-control" id="q" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search products...">
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($category_id == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="min_price" class="form-label">Min Price</label>
                <input type="number" step="0.01" class="form-control" id="min_price" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
            </div>
            <div class="col-md-2">
                <label for="max_price" class="form-label">Max Price</label>
                <input type="number" step="0.01" class="form-control" id="max_price" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
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
                        <?php if (!empty($product['sale_price'])): ?>
                            <div class="product-badge badge-sale">Sale</div>
                        <?php elseif (!empty($product['created_at']) && strtotime($product['created_at']) > strtotime('-7 days')): ?>
                            <div class="product-badge badge-new">New</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                        </h3>
                        <div class="product-price">
                            <?php if (!empty($product['sale_price'])): ?>
                                <span class="current-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php else: ?>
                                <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No products found. Try adjusting your search or filters.</div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
