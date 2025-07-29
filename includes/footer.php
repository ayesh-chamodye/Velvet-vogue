        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Velvet Vogue</h5>
                    <p>Your premier destination for trendy fashion. Shop the latest styles in casual, formal, and partywear.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-white text-decoration-none">Shop</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="blog.php" class="text-white text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="account.php" class="text-white text-decoration-none">My Account</a></li>
                        <li class="mb-2"><a href="orders.php" class="text-white text-decoration-none">Order Tracking</a></li>
                        <li class="mb-2"><a href="wishlist.php" class="text-white text-decoration-none">Wishlist</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-white text-decoration-none">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Newsletter</h5>
                    <p>Subscribe to receive updates, access to exclusive deals, and more.</p>
                    <form action="newsletter_subscribe.php" method="POST" class="mt-3">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your Email" name="email" required>
                            <button class="btn btn-light" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Velvet Vogue. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa me-2"></i>
                        <i class="fab fa-cc-mastercard me-2"></i>
                        <i class="fab fa-cc-amex me-2"></i>
                        <i class="fab fa-cc-paypal me-2"></i>
                        <i class="fab fa-cc-stripe"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Chat Bot Button -->
    <div class="chat-bot-button">
        <button class="btn btn-primary rounded-circle shadow" id="chatBotToggle">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>
    
    <!-- Chat Bot Container -->
    <div class="chat-bot-container" id="chatBotContainer">
        <div class="chat-header bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Velvet Assistant</h5>
                <button class="btn-close btn-close-white" id="chatBotClose"></button>
            </div>
        </div>
        <div class="chat-messages p-3" id="chatMessages">
            <div class="message bot-message">
                <div class="message-content">
                    Hello! I'm your Velvet Vogue assistant. How can I help you today?
                </div>
            </div>
        </div>
        <div class="chat-input p-3 border-top">
            <form id="chatForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="chatInput" placeholder="Type your message..." required>
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <?php if(isset($additional_js)): ?>
        <?php foreach($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
