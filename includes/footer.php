        </div>
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-brand">
                    <div class="footer-logo">
                        <i class="ph-duotone ph-books"></i>
                        <span>المكتبة الإلكترونية</span>
                    </div>
                    <p>وجهتك الأولى للكتب العربية في المملكة العربية السعودية. نوفر لك أفضل الكتب بأسعار منافسة وتوصيل سريع.</p>
                    <div class="footer-badges">
                        <span class="badge-trust"><i class="ph ph-shield-check"></i> موقع آمن</span>
                        <span class="badge-trust"><i class="ph ph-truck"></i> توصيل سريع</span>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3><i class="ph ph-link"></i> روابط سريعة</h3>
                    <ul>
                        <li><a href="<?php echo url('index.php'); ?>"><i class="ph ph-house"></i> الرئيسية</a></li>
                        <li><a href="<?php echo url('books.php'); ?>"><i class="ph ph-book-open"></i> الكتب</a></li>
                        <li><a href="<?php echo url('about.php'); ?>"><i class="ph ph-info"></i> عن المكتبة</a></li>
                        <li><a href="<?php echo url('contact.php'); ?>"><i class="ph ph-envelope"></i> اتصل بنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><i class="ph ph-headset"></i> خدمة العملاء</h3>
                    <ul>
                        <li><a href="<?php echo url('faq.php'); ?>"><i class="ph ph-question"></i> الأسئلة الشائعة</a></li>
                        <li><a href="<?php echo url('shipping.php'); ?>"><i class="ph ph-truck"></i> سياسة الشحن</a></li>
                        <li><a href="<?php echo url('returns.php'); ?>"><i class="ph ph-arrow-u-up-left"></i> الإرجاع والاستبدال</a></li>
                        <li><a href="<?php echo url('privacy.php'); ?>"><i class="ph ph-lock"></i> سياسة الخصوصية</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><i class="ph ph-phone"></i> تواصل معنا</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="ph-duotone ph-map-pin"></i>
                            <span>الرياض، المملكة العربية السعودية</span>
                        </li>
                        <li>
                            <i class="ph-duotone ph-phone"></i>
                            <a href="tel:920012345">920012345</a>
                        </li>
                        <li>
                            <i class="ph-duotone ph-envelope"></i>
                            <a href="mailto:info@bookstore.sa">info@bookstore.sa</a>
                        </li>
                    </ul>
                    
                    <h4>تابعنا</h4>
                    <div class="social-links">
                        <a href="#" title="تويتر" aria-label="تويتر">
                            <i class="ph-duotone ph-x-logo"></i>
                        </a>
                        <a href="#" title="انستغرام" aria-label="انستغرام">
                            <i class="ph-duotone ph-instagram-logo"></i>
                        </a>
                        <a href="#" title="فيسبوك" aria-label="فيسبوك">
                            <i class="ph-duotone ph-facebook-logo"></i>
                        </a>
                        <a href="#" title="يوتيوب" aria-label="يوتيوب">
                            <i class="ph-duotone ph-youtube-logo"></i>
                        </a>
                        <a href="#" title="واتساب" aria-label="واتساب">
                            <i class="ph-duotone ph-whatsapp-logo"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-newsletter">
                <div class="newsletter-content">
                    <h3><i class="ph ph-envelope-simple"></i> اشترك في النشرة البريدية</h3>
                    <p>احصل على أحدث العروض والإصدارات الجديدة</p>
                </div>
                <form class="newsletter-form" action="#" method="POST">
                    <input type="email" placeholder="بريدك الإلكتروني" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-paper-plane-tilt"></i>
                        <span>اشتراك</span>
                    </button>
                </form>
            </div>
            
            <div class="footer-bottom">
                <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> المكتبة الإلكترونية</p>
                <div class="footer-payment">
                    <span>طرق الدفع:</span>
                    <i class="ph-duotone ph-credit-card" title="بطاقة ائتمان"></i>
                    <i class="ph-duotone ph-apple-logo" title="Apple Pay"></i>
                    <i class="ph-duotone ph-bank" title="تحويل بنكي"></i>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <i class="ph-duotone ph-spinner"></i>
        </div>
    </div>
    
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
