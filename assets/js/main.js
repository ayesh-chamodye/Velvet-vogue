/**
 * Velvet Vogue - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize hero banner slider
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
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
    }

    // Initialize product sliders
    if (document.querySelector('.product-swiper')) {
        new Swiper('.product-swiper', {
            slidesPerView: 1,
            spaceBetween: 10,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 30,
                },
            },
        });
    }

    // Product image gallery
    const mainImage = document.getElementById('mainProductImage');
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Update main image
                mainImage.src = this.src;
                
                // Update active state
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Product quantity selector
    const quantityInput = document.querySelector('.quantity-input');
    const increaseBtn = document.querySelector('.quantity-increase');
    const decreaseBtn = document.querySelector('.quantity-decrease');
    
    if (quantityInput && increaseBtn && decreaseBtn) {
        increaseBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            quantityInput.value = value + 1;
        });
        
        decreaseBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
    }

    // Product options selection
    const colorOptions = document.querySelectorAll('.color-option');
    const sizeOptions = document.querySelectorAll('.size-option');
    
    if (colorOptions.length > 0) {
        colorOptions.forEach(option => {
            option.addEventListener('click', function() {
                colorOptions.forEach(o => o.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    if (sizeOptions.length > 0) {
        sizeOptions.forEach(option => {
            option.addEventListener('click', function() {
                sizeOptions.forEach(o => o.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Cart quantity update
    const cartQuantityInputs = document.querySelectorAll('.cart-quantity');
    
    if (cartQuantityInputs.length > 0) {
        cartQuantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            });
        });
    }

    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    if (paymentMethods.length > 0) {
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('active'));
                this.classList.add('active');
                
                const radioInput = this.querySelector('input[type="radio"]');
                if (radioInput) {
                    radioInput.checked = true;
                }
            });
        });
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    if (forms.length > 0) {
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }

    // Chat bot functionality
    const chatBotToggle = document.getElementById('chatBotToggle');
    const chatBotContainer = document.getElementById('chatBotContainer');
    const chatBotClose = document.getElementById('chatBotClose');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    
    if (chatBotToggle && chatBotContainer && chatBotClose && chatForm && chatInput && chatMessages) {
        // Toggle chat bot
        chatBotToggle.addEventListener('click', function() {
            chatBotContainer.style.display = 'flex';
            chatBotToggle.style.display = 'none';
        });
        
        // Close chat bot
        chatBotClose.addEventListener('click', function() {
            chatBotContainer.style.display = 'none';
            chatBotToggle.style.display = 'block';
        });
        
        // Send message
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = chatInput.value.trim();
            
            if (message) {
                // Add user message to chat
                addMessage(message, 'user');
                
                // Clear input
                chatInput.value = '';
                
                // Send message to server
                sendChatMessage(message);
            }
        });
        
        // Function to add message to chat
        function addMessage(message, type) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', type + '-message');
            
            const messageContent = document.createElement('div');
            messageContent.classList.add('message-content');
            messageContent.textContent = message;
            
            messageElement.appendChild(messageContent);
            chatMessages.appendChild(messageElement);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Function to send message to server
        function sendChatMessage(message) {
            // Show typing indicator
            const typingElement = document.createElement('div');
            typingElement.classList.add('message', 'bot-message', 'typing-indicator');
            
            const typingContent = document.createElement('div');
            typingContent.classList.add('message-content');
            typingContent.innerHTML = '<span>.</span><span>.</span><span>.</span>';
            
            typingElement.appendChild(typingContent);
            chatMessages.appendChild(typingElement);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Send AJAX request to server
            fetch('chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message }),
            })
            .then(response => response.json())
            .then(data => {
                // Remove typing indicator
                chatMessages.removeChild(typingElement);
                
                // Add bot response
                addMessage(data.response, 'bot');
            })
            .catch(error => {
                // Remove typing indicator
                chatMessages.removeChild(typingElement);
                
                // Add error message
                addMessage('Sorry, I encountered an error. Please try again later.', 'bot');
                console.error('Error:', error);
            });
        }
    }
});
