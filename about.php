<?php
// Page removed - Redirect to home
header('Location: index.php');
exit;

// Page title
$pageTitle = 'من نحن';

// Get some stats for the page
try {
    $conn = getDBConnection();
    
    // Total books
    $totalBooks = dbQuerySingle("SELECT COUNT(*) as count FROM books")['count'] ?? 0;
    
    // Total customers
    $totalCustomers = dbQuerySingle("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;
    
    // Total publishers
    $totalPublishers = dbQuerySingle("SELECT COUNT(*) as count FROM publishers")['count'] ?? 0;
    
} catch (Exception $e) {
    $totalBooks = 0;
    $totalCustomers = 0;
    $totalPublishers = 0;
}

require_once 'includes/header.php';
?>

<main class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>📚 من نحن</h1>
            <p class="subtitle">وجهتك الأولى للكتب في المملكة العربية السعودية</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>قصتنا</h2>
                    <p>
                        تأسست المكتبة الإلكترونية بهدف تقريب الكتاب من القارئ العربي، وتوفير تجربة تسوق سهلة وممتعة لمحبي القراءة في جميع أنحاء المملكة العربية السعودية.
                    </p>
                    <p>
                        نؤمن بأن الكتاب هو نافذة على عوالم جديدة، ومصدر للمعرفة والإلهام. لذلك نسعى جاهدين لتوفير أكبر مجموعة متنوعة من الكتب بأفضل الأسعار وأسرع خدمة توصيل.
                    </p>
                    <p>
                        مع فريق متخصص وشغوف بالقراءة، نختار لكم أفضل الإصدارات من كبرى دور النشر العربية والعالمية، ونقدم توصيات مخصصة تناسب اهتماماتكم.
                    </p>
                </div>
                <div class="about-image">
                    <div class="image-placeholder">
                        <span>📖</span>
                        <p>صورة المكتبة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="about-stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-icon">📚</span>
                    <span class="stat-number"><?php echo number_format($totalBooks); ?>+</span>
                    <span class="stat-label">كتاب متاح</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">👥</span>
                    <span class="stat-number"><?php echo number_format($totalCustomers); ?>+</span>
                    <span class="stat-label">عميل سعيد</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">🏢</span>
                    <span class="stat-number"><?php echo number_format($totalPublishers); ?>+</span>
                    <span class="stat-label">دار نشر</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">🚚</span>
                    <span class="stat-number">13</span>
                    <span class="stat-label">منطقة توصيل</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="about-values">
        <div class="container">
            <h2>قيمنا</h2>
            <div class="values-grid">
                <div class="value-card">
                    <span class="value-icon">🎯</span>
                    <h3>الجودة</h3>
                    <p>نحرص على تقديم كتب أصلية بجودة عالية من مصادر موثوقة</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">💰</span>
                    <h3>أسعار منافسة</h3>
                    <p>نسعى لتقديم أفضل الأسعار مع عروض وخصومات مستمرة</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">⚡</span>
                    <h3>سرعة التوصيل</h3>
                    <p>توصيل سريع لجميع مناطق المملكة خلال 2-5 أيام عمل</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">🤝</span>
                    <h3>خدمة العملاء</h3>
                    <p>فريق دعم متميز جاهز لمساعدتك على مدار الساعة</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="about-team">
        <div class="container">
            <h2>فريقنا</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">👨‍💼</div>
                    <h3>أحمد الراشد</h3>
                    <p class="role">المدير التنفيذي</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">👩‍💼</div>
                    <h3>نورة العتيبي</h3>
                    <p class="role">مدير العمليات</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">👨‍💻</div>
                    <h3>محمد السعيد</h3>
                    <p class="role">مدير التقنية</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">👩‍🎨</div>
                    <h3>سارة الحربي</h3>
                    <p class="role">مدير التسويق</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container">
            <h2>ابدأ رحلتك مع القراءة اليوم!</h2>
            <p>اكتشف آلاف الكتب واستمتع بتجربة تسوق فريدة</p>
            <div class="cta-buttons">
                <a href="<?php echo url('books.php'); ?>" class="btn btn-primary">تصفح الكتب</a>
                <a href="<?php echo url('contact.php'); ?>" class="btn btn-secondary">تواصل معنا</a>
            </div>
        </div>
    </section>
</main>

<style>
/* About Page Styles */
.about-page {
    padding-bottom: 50px;
}

.about-hero {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    padding: 80px 20px;
    text-align: center;
}

.about-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.about-hero .subtitle {
    font-size: 1.3rem;
    opacity: 0.9;
}

.about-content {
    padding: 60px 20px;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: center;
}

.about-text h2 {
    color: #006c35;
    margin-bottom: 20px;
    font-size: 1.8rem;
}

.about-text p {
    margin-bottom: 15px;
    line-height: 1.8;
    color: #555;
}

.about-image .image-placeholder {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 80px;
    text-align: center;
}

.image-placeholder span {
    font-size: 5rem;
    display: block;
    margin-bottom: 20px;
}

.about-stats {
    background-color: #f8f9fa;
    padding: 60px 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    text-align: center;
}

.stat-item {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.stat-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 10px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #006c35;
    display: block;
}

.stat-label {
    color: #666;
    font-size: 0.95rem;
}

.about-values {
    padding: 60px 20px;
}

.about-values h2 {
    text-align: center;
    color: #006c35;
    margin-bottom: 40px;
    font-size: 1.8rem;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}

.value-card {
    text-align: center;
    padding: 30px 20px;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.value-card:hover {
    border-color: #006c35;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,108,53,0.1);
}

.value-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 15px;
}

.value-card h3 {
    color: #333;
    margin-bottom: 10px;
}

.value-card p {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
}

.about-team {
    background-color: #f8f9fa;
    padding: 60px 20px;
}

.about-team h2 {
    text-align: center;
    color: #006c35;
    margin-bottom: 40px;
    font-size: 1.8rem;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
}

.team-member {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.member-avatar {
    font-size: 4rem;
    margin-bottom: 15px;
}

.team-member h3 {
    color: #333;
    margin-bottom: 5px;
}

.team-member .role {
    color: #006c35;
    font-size: 0.9rem;
}

.about-cta {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    padding: 60px 20px;
    text-align: center;
}

.about-cta h2 {
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.about-cta p {
    font-size: 1.1rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.cta-buttons .btn {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
}

.btn-primary {
    background: white;
    color: #006c35;
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

@media (max-width: 992px) {
    .about-grid,
    .stats-grid,
    .values-grid,
    .team-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .about-grid,
    .stats-grid,
    .values-grid,
    .team-grid {
        grid-template-columns: 1fr;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
