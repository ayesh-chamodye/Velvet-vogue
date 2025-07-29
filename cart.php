<?php
// Include functions
require_once 'includes/functions.php';

// Get cart items
$cart_items = get_cart_items();
$cart_total = calculate_cart_total();

// Set page title
$page_title = 'Shopping Cart';
$meta_description = 'View your shopping cart at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'fashion, clothing, shopping cart, checkout, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
        </ol>
    </div>
</nav>

<!-- Shopping Cart -->
<section class="cart-page py-5">
    <div class="container">
        <h1 class="mb-4">Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-4x mb-4 text-muted"></i>
                <h3>Your cart is empty</h3>
                <p class="mb-4">Looks like you haven't added any products to your cart yet.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="cart-product d-flex align-items-center">
                                                    <img src="<?php echo !empty($item['image']) ? $item['image'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="cart-product-image">
                                                    <div class="cart-product-info">
                                                        <h5 class="cart-product-title">
                                                            <a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a>
                                                        </h5>
                                                        <?php if (!empty($item['attributes'])): ?>
                                                            <div class="cart-product-attributes small text-muted">
                                                                <?php foreach ($item['attributes'] as $attr_name => $attr_value): ?>
                                                                    <span><?php echo ucfirst($attr_name); ?>: <?php echo $attr_value; ?></span><br>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cart-product-price">
                                                <?php echo format_price($item['price']); ?>
                                            </td>
                                            <td class="cart-product-quantity">
                                                <form action="cart_update.php" method="POST" class="quantity-form">
                                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                                    <div class="input-group">
                                                        <button type="button" class="btn btn-outline-secondary quantity-decrease"><i class="fas fa-minus"></i></button>
                                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control quantity-input">
                                                        <button type="button" class="btn btn-outline-secondary quantity-increase"><i class="fas fa-plus"></i></button>
                                                        <button type="submit" class="btn btn-outline-primary update-quantity"><i class="fas fa-sync-alt"></i></button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="cart-product-total">
                                                <?php echo format_price($item['price'] * $item['quantity']); ?>
                                            </td>
                                            <td class="cart-product-remove">
                                                <a href="cart_remove.php?cart_item_id=<?php echo $item['cart_item_id']; ?>" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="cart-actions d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                        <a href="cart_clear.php" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-2"></i> Clear Cart
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary card">
                        <div class="card-header">
                            <h4 class="mb-0">Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo format_price($cart_total['subtotal']); ?></span>
                            </div>
                            <?php if ($cart_total['discount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-<?php echo format_price($cart_total['discount']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span><?php echo $cart_total['shipping'] > 0 ? format_price($cart_total['shipping']) : 'Free'; ?></span>
                            </div>
                            <?php if ($cart_total['tax'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <span><?php echo format_price($cart_total['tax']); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="cart-total-price"><?php echo format_price($cart_total['total']); ?></strong>
                            </div>
                            
                            <!-- Coupon Code Form -->
                            <form action="apply_coupon.php" method="POST" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="coupon_code" class="form-control" placeholder="Coupon Code">
                                    <button type="submit" class="btn btn-outline-secondary">Apply</button>
                                </div>
                            </form>
                            
                            <a href="checkout.php" class="btn btn-primary w-100">
                                Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="payment-methods mt-4">
                        <h5>We Accept</h5>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-amex"></i>
                            <i class="fab fa-cc-paypal"></i>
                            <i class="fab fa-cc-discover"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
