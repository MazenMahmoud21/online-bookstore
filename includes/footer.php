        </div>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-brand">
                    <div class="footer-logo">
                        <i data-feather="book-open"></i>
                        <span>Egyptian Bookstore</span>
                    </div>
                    <p>Your premier destination for books in Egypt. We offer the best selection with competitive prices and fast delivery across Cairo and beyond.</p>
                    <div class="footer-badges">
                        <span class="badge-trust"><i data-feather="shield"></i> Secure Site</span>
                        <span class="badge-trust"><i data-feather="truck"></i> Fast Delivery</span>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3><i data-feather="link"></i> Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo url('index.php'); ?>"><i data-feather="home"></i> Home</a></li>
                        <li><a href="<?php echo url('books.php'); ?>"><i data-feather="book-open"></i> Books</a></li>
                        <li><a href="<?php echo url('index.php'); ?>"><i data-feather="info"></i> About Us</a></li>
                        <li><a href="<?php echo url('index.php'); ?>"><i data-feather="mail"></i> Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><i data-feather="headphones"></i> Customer Service</h3>
                    <ul>
                        <li><a href="#"><i data-feather="help-circle"></i> FAQ</a></li>
                        <li><a href="#"><i data-feather="truck"></i> Shipping Policy</a></li>
                        <li><a href="#"><i data-feather="rotate-ccw"></i> Returns & Exchanges</a></li>
                        <li><a href="#"><i data-feather="lock"></i> Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><i data-feather="phone"></i> Contact Us</h3>
                    <ul class="contact-info">
                        <li>
                            <i data-feather="map-pin"></i>
                            <span>Cairo, Egypt</span>
                        </li>
                        <li>
                            <i data-feather="phone"></i>
                            <a href="tel:+201234567890">+20 123 456 7890</a>
                        </li>
                        <li>
                            <i data-feather="mail"></i>
                            <a href="mailto:info@bookstore.eg">info@bookstore.eg</a>
                        </li>
                    </ul>
                    
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#" title="Twitter" aria-label="Twitter">
                            <i data-feather="twitter"></i>
                        </a>
                        <a href="#" title="Instagram" aria-label="Instagram">
                            <i data-feather="instagram"></i>
                        </a>
                        <a href="#" title="Facebook" aria-label="Facebook">
                            <i data-feather="facebook"></i>
                        </a>
                        <a href="#" title="YouTube" aria-label="YouTube">
                            <i data-feather="youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-newsletter">
                <div class="newsletter-content">
                    <h3><i data-feather="mail"></i> Subscribe to Newsletter</h3>
                    <p>Get the latest offers and new releases</p>
                </div>
                <form class="newsletter-form" action="#" method="POST">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="send"></i>
                        <span>Subscribe</span>
                    </button>
                </form>
            </div>
            
            <div class="footer-bottom">
                <p>All Rights Reserved &copy; <?php echo date('Y'); ?> Egyptian Bookstore</p>
                <div class="footer-payment">
                    <span>Payment Methods:</span>
                    <i data-feather="credit-card" title="Credit Card"></i>
                    <i data-feather="smartphone" title="Mobile Payment"></i>
                    <i data-feather="dollar-sign" title="Cash on Delivery"></i>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i data-feather="loader"></i>
        </div>
    </div>
    
    <script>
        // Initialize Feather Icons in footer
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    </script>
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
