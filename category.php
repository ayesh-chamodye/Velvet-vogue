<?php
// Include functions
require_once 'includes/functions.php';

// Get category ID from URL
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Get category information
$category = null;
if ($category_id > 0) {
    $sql = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $category = mysqli_fetch_assoc($result);
    }
}

// Redirect if category not found
if (!$category) {
    set_message('Category not found.', 'danger');
    redirect('index.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Get products by category
$products = get_products_by_category($category_id, $limit, $offset);

// Get total products count for pagination
$sql = "SELECT COUNT(*) as total FROM products WHERE category_id = ? AND status = 'active'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$total_products = $row['total'];
$total_pages = ceil($total_products / $limit);

// Set page title and meta information
$page_title = $category['name'];
$meta_description = $category['description'] ? $category['description'] : "Shop our collection of {$category['name']} at Velvet Vogue.";
$meta_keywords = "fashion, clothing, {$category['name']}, online shopping";

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $category['name']; ?></li>
        </ol>
    </div>
</nav>

<!-- Category Banner -->
<?php if (!empty($category['image_url'])): ?>
<section class="category-banner">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="banner-image">
                    <img src="<?php echo $category['image_url']; ?>" alt="<?php echo $category['name']; ?>" class="img-fluid">
                    <div class="banner-content">
                        <h1><?php echo $category['name']; ?></h1>
                        <?php if (!empty($category['description'])): ?>
                            <p><?php echo $category['description']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<section class="py-4">
    <div class="container">
        <h1 class="mb-4"><?php echo $category['name']; ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="lead"><?php echo $category['description']; ?></p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Product Listing -->
<section class="product-listing py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filters-sidebar mb-4">
                    <h4>Filters</h4>
                    <hr>
                    
                    <!-- Price Range Filter -->
                    <div class="filter-group mb-4">
                        <h5>Price Range</h5>
                        <form action="category.php" method="GET">
                            <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                            <div class="input-group mb-2">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                        </form>
                    </div>
                    
                    <!-- Size Filter -->
                    <div class="filter-group mb-4">
                        <h5>Size</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="S" id="sizeS">
                            <label class="form-check-label" for="sizeS">Small (S)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="M" id="sizeM">
                            <label class="form-check-label" for="sizeM">Medium (M)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="L" id="sizeL">
                            <label class="form-check-label" for="sizeL">Large (L)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="XL" id="sizeXL">
                            <label class="form-check-label" for="sizeXL">Extra Large (XL)</label>
                        </div>
                    </div>
                    
                    <!-- Color Filter -->
                    <div class="filter-group mb-4">
                        <h5>Color</h5>
                        <div class="color-options">
                            <div class="color-option" style="background-color: #000000;" data-color="Black"></div>
                            <div class="color-option" style="background-color: #FFFFFF; border: 1px solid #ddd;" data-color="White"></div>
                            <div class="color-option" style="background-color: #FF0000;" data-color="Red"></div>
                            <div class="color-option" style="background-color: #0000FF;" data-color="Blue"></div>
                            <div class="color-option" style="background-color: #008000;" data-color="Green"></div>
                            <div class="color-option" style="background-color: #FFFF00;" data-color="Yellow"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products -->
            <div class="col-lg-9">
                <!-- Sorting and View Options -->
                <div class="products-header d-flex justify-content-between align-items-center mb-4">
                    <div class="showing-results">
                        Showing <?php echo min(($offset + 1), $total_products); ?>-<?php echo min(($offset + $limit), $total_products); ?> of <?php echo $total_products; ?> products
                    </div>
                    <div class="sort-options">
                        <select class="form-select form-select-sm">
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                </div>
                
                <!-- Product Grid -->
                <div class="row">
                    <?php if (empty($products)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No products found in this category.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-4 col-6 mb-4">
                                <div class="product-card">
                                    <div class="product-image">
                                        <img src="<?php echo !empty($product['primary_image']) ? $product['primary_image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                                        <div class="product-actions">
                                            <a href="wishlist_add.php?product_id=<?php echo $product['id']; ?>" class="btn"><i class="fas fa-heart"></i></a>
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
                                            <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
                                        </h3>
                                        <div class="product-price">
                                            <?php if ($product['sale_price']): ?>
                                                <span class="current-price"><?php echo format_price($product['sale_price']); ?></span>
                                                <span class="old-price"><?php echo format_price($product['price']); ?></span>
                                            <?php else: ?>
                                                <span class="current-price"><?php echo format_price($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <form action="cart_add.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="add-to-cart">Add to Cart</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="category.php?category_id=<?php echo $category_id; ?>&page=<?php echo ($page - 1); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="category.php?category_id=<?php echo $category_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="category.php?category_id=<?php echo $category_id; ?>&page=<?php echo ($page + 1); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
