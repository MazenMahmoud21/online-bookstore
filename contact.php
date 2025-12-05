<?php
// Page removed - Redirect to home
header('Location: index.php');
exit;
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق. يرجى المحاولة مرة أخرى.';
        $messageType = 'error';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $userMessage = sanitizeInput($_POST['message'] ?? '');
        
        $errors = [];
        
        // Validation
        if (empty($name) || mb_strlen($name) < 2) {
            $errors[] = 'يرجى إدخال الاسم الكامل';
        }
        
        if (empty($email) || !validateEmailFormat($email)) {
            $errors[] = 'يرجى إدخال بريد إلكتروني صحيح';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'رقم الهاتف غير صحيح';
        }
        
        if (empty($subject)) {
            $errors[] = 'يرجى إدخال موضوع الرسالة';
        }
        
        if (empty($userMessage) || mb_strlen($userMessage) < 10) {
            $errors[] = 'يرجى إدخال رسالة (10 أحرف على الأقل)';
        }
        
        if (empty($errors)) {
            // Store in database (optional)
            try {
                $conn = getDBConnection();
                
                // Check if contact_messages table exists
                $result = dbQuery("SHOW TABLES LIKE 'contact_messages'");
                if (count($result) > 0) {
                    dbExecute(
                        "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                        [$name, $email, $phone, $subject, $userMessage]
                    );
                }
            } catch (Exception $e) {
                // Table doesn't exist yet, continue anyway
            }
            
            // Send email notification
            $emailSent = sendContactNotification($name, $email, $subject, $userMessage);
            
            $message = 'تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت ممكن.';
            $messageType = 'success';
            
            // Clear form
            $name = $email = $phone = $subject = $userMessage = '';
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'error';
        }
    }
}

require_once 'includes/header.php';
?>

<main class="contact-page">
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>📞 اتصل بنا</h1>
            <p class="subtitle">نحن هنا لمساعدتك! تواصل معنا في أي وقت</p>
        </div>
    </section>

    <section class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="contact-form-section">
                    <h2>أرسل لنا رسالة</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">الاسم الكامل <span class="required">*</span></label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">البريد الإلكتروني <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">رقم الهاتف</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="05xxxxxxxx">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">الموضوع <span class="required">*</span></label>
                                <select id="subject" name="subject" required>
                                    <option value="">اختر الموضوع</option>
                                    <option value="استفسار عام" <?php echo ($subject ?? '') === 'استفسار عام' ? 'selected' : ''; ?>>استفسار عام</option>
                                    <option value="استفسار عن طلب" <?php echo ($subject ?? '') === 'استفسار عن طلب' ? 'selected' : ''; ?>>استفسار عن طلب</option>
                                    <option value="مشكلة تقنية" <?php echo ($subject ?? '') === 'مشكلة تقنية' ? 'selected' : ''; ?>>مشكلة تقنية</option>
                                    <option value="اقتراح" <?php echo ($subject ?? '') === 'اقتراح' ? 'selected' : ''; ?>>اقتراح</option>
                                    <option value="شكوى" <?php echo ($subject ?? '') === 'شكوى' ? 'selected' : ''; ?>>شكوى</option>
                                    <option value="طلب كتاب" <?php echo ($subject ?? '') === 'طلب كتاب' ? 'selected' : ''; ?>>طلب كتاب غير متوفر</option>
                                    <option value="أخرى" <?php echo ($subject ?? '') === 'أخرى' ? 'selected' : ''; ?>>أخرى</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">رسالتك <span class="required">*</span></label>
                            <textarea id="message" name="message" rows="6" required placeholder="اكتب رسالتك هنا..."><?php echo htmlspecialchars($userMessage ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-submit">إرسال الرسالة</button>
                    </form>
                </div>
                
                <!-- Contact Info -->
                <div class="contact-info-section">
                    <h2>معلومات التواصل</h2>
                    
                    <div class="info-cards">
                        <div class="info-card">
                            <span class="info-icon">📍</span>
                            <div class="info-content">
                                <h3>العنوان</h3>
                                <p>الرياض، المملكة العربية السعودية</p>
                                <p>شارع الملك فهد، برج المكتبة</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <span class="info-icon">📞</span>
                            <div class="info-content">
                                <h3>الهاتف</h3>
                                <p>920000000</p>
                                <p>الدعم الفني: 920000001</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <span class="info-icon">📧</span>
                            <div class="info-content">
                                <h3>البريد الإلكتروني</h3>
                                <p>info@bookstore.sa</p>
                                <p>support@bookstore.sa</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <span class="info-icon">⏰</span>
                            <div class="info-content">
                                <h3>ساعات العمل</h3>
                                <p>السبت - الخميس</p>
                                <p>9:00 صباحاً - 9:00 مساءً</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="social-section">
                        <h3>تابعنا على</h3>
                        <div class="social-links">
                            <a href="#" class="social-link twitter">𝕏</a>
                            <a href="#" class="social-link instagram">📷</a>
                            <a href="#" class="social-link whatsapp">💬</a>
                            <a href="#" class="social-link snapchat">👻</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>الأسئلة الشائعة</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>❓ كيف يمكنني تتبع طلبي؟</h3>
                    <p>يمكنك تتبع طلبك من خلال صفحة "طلباتي" في حسابك، أو عبر رقم التتبع المرسل إلى بريدك الإلكتروني.</p>
                </div>
                <div class="faq-item">
                    <h3>❓ ما هي مدة التوصيل؟</h3>
                    <p>التوصيل داخل الرياض خلال 1-2 يوم عمل، وباقي مناطق المملكة خلال 3-5 أيام عمل.</p>
                </div>
                <div class="faq-item">
                    <h3>❓ هل يمكنني إرجاع الكتب؟</h3>
                    <p>نعم، يمكنك إرجاع الكتب خلال 7 أيام من الاستلام بشرط أن تكون بحالتها الأصلية.</p>
                </div>
                <div class="faq-item">
                    <h3>❓ ما هي طرق الدفع المتاحة؟</h3>
                    <p>نقبل الدفع عند الاستلام، البطاقات البنكية، مدى، Apple Pay، وStc Pay.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Contact Page Styles */
.contact-page {
    padding-bottom: 50px;
}

.contact-hero {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    padding: 80px 20px;
    text-align: center;
}

.contact-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.contact-hero .subtitle {
    font-size: 1.3rem;
    opacity: 0.9;
}

.contact-content {
    padding: 60px 20px;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 50px;
}

.contact-form-section h2,
.contact-info-section h2 {
    color: #006c35;
    margin-bottom: 25px;
    font-size: 1.5rem;
}

.contact-form {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #e74c3c;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #006c35;
}

.btn-submit {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    border: none;
    padding: 15px 40px;
    font-size: 1.1rem;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,108,53,0.3);
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.info-cards {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: transform 0.3s;
}

.info-card:hover {
    transform: translateX(-5px);
}

.info-icon {
    font-size: 2rem;
}

.info-content h3 {
    color: #006c35;
    margin-bottom: 5px;
    font-size: 1rem;
}

.info-content p {
    margin: 3px 0;
    color: #555;
    font-size: 0.95rem;
}

.social-section {
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
}

.social-section h3 {
    margin-bottom: 15px;
    color: #333;
}

.social-links {
    display: flex;
    gap: 12px;
}

.social-link {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 1.3rem;
    color: white;
    transition: transform 0.3s;
}

.social-link:hover {
    transform: scale(1.1);
}

.social-link.twitter { background: #1da1f2; }
.social-link.instagram { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.social-link.whatsapp { background: #25d366; }
.social-link.snapchat { background: #fffc00; color: #333; }

.faq-section {
    background: #f8f9fa;
    padding: 60px 20px;
}

.faq-section h2 {
    text-align: center;
    color: #006c35;
    margin-bottom: 40px;
    font-size: 1.8rem;
}

.faq-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    max-width: 1000px;
    margin: 0 auto;
}

.faq-item {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.faq-item h3 {
    color: #333;
    margin-bottom: 12px;
    font-size: 1rem;
}

.faq-item p {
    color: #666;
    line-height: 1.7;
    font-size: 0.95rem;
}

@media (max-width: 992px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .faq-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
