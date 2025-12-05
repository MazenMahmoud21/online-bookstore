<?php
/**
 * Email Helper Functions
 * دوال البريد الإلكتروني
 */

require_once __DIR__ . '/config.php';

// Email configuration
define('MAIL_FROM_EMAIL', 'noreply@bookstore.sa');
define('MAIL_FROM_NAME', 'المكتبة الإلكترونية');
define('MAIL_REPLY_TO', 'support@bookstore.sa');

/**
 * Send email using PHP mail function
 * إرسال بريد إلكتروني
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param array $options Additional options
 * @return bool Success status
 */
function sendEmail($to, $subject, $body, $options = []) {
    $fromEmail = $options['from_email'] ?? MAIL_FROM_EMAIL;
    $fromName = $options['from_name'] ?? MAIL_FROM_NAME;
    $replyTo = $options['reply_to'] ?? MAIL_REPLY_TO;
    
    // Headers
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $replyTo;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    // Wrap body in HTML template
    $htmlBody = getEmailTemplate($subject, $body);
    
    // Send email
    $result = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, implode("\r\n", $headers));
    
    // Log email
    logEmail($to, $subject, $result);
    
    return $result;
}

/**
 * Get HTML email template
 * قالب البريد الإلكتروني
 * 
 * @param string $title Email title
 * @param string $content Email content
 * @return string HTML email
 */
function getEmailTemplate($title, $content) {
    return '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #006c35, #00a651);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 30px;
            line-height: 1.8;
            color: #333;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #006c35, #00a651);
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn:hover {
            background: linear-gradient(135deg, #005a2b, #008841);
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>📚 المكتبة الإلكترونية</h1>
        </div>
        <div class="email-body">
            ' . $content . '
        </div>
        <div class="email-footer">
            <p>هذه الرسالة آلية، يرجى عدم الرد عليها مباشرة.</p>
            <p>&copy; ' . date('Y') . ' المكتبة الإلكترونية - جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>';
}

/**
 * Send password reset email
 * إرسال بريد استعادة كلمة المرور
 * 
 * @param string $email User email
 * @param string $name User name
 * @param string $token Reset token
 * @return bool Success status
 */
function sendPasswordResetEmail($email, $name, $token) {
    $resetLink = url('reset_password.php?token=' . urlencode($token));
    
    $subject = 'استعادة كلمة المرور - المكتبة الإلكترونية';
    $body = '
        <h2>مرحباً ' . htmlspecialchars($name) . '،</h2>
        <p>لقد تلقينا طلباً لاستعادة كلمة المرور الخاصة بحسابك.</p>
        <p>انقر على الزر أدناه لإنشاء كلمة مرور جديدة:</p>
        <p style="text-align: center;">
            <a href="' . $resetLink . '" class="btn">إعادة تعيين كلمة المرور</a>
        </p>
        <p>أو انسخ الرابط التالي في متصفحك:</p>
        <p style="word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 5px;">' . $resetLink . '</p>
        <p><strong>ملاحظة:</strong> هذا الرابط صالح لمدة ساعة واحدة فقط.</p>
        <p>إذا لم تطلب استعادة كلمة المرور، يمكنك تجاهل هذه الرسالة.</p>
        <p>مع تحياتنا،<br>فريق المكتبة الإلكترونية</p>
    ';
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send order confirmation email
 * إرسال بريد تأكيد الطلب
 * 
 * @param string $email Customer email
 * @param string $name Customer name
 * @param array $orderDetails Order details
 * @return bool Success status
 */
function sendOrderConfirmationEmail($email, $name, $orderDetails) {
    $subject = 'تأكيد طلبك رقم #' . $orderDetails['order_id'] . ' - المكتبة الإلكترونية';
    
    // Build order items table
    $itemsHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr style="background-color: #006c35; color: white;">
            <th style="padding: 10px; text-align: right;">الكتاب</th>
            <th style="padding: 10px; text-align: center;">الكمية</th>
            <th style="padding: 10px; text-align: left;">السعر</th>
        </tr>';
    
    foreach ($orderDetails['items'] as $item) {
        $itemsHtml .= '<tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">' . htmlspecialchars($item['title']) . '</td>
            <td style="padding: 10px; text-align: center;">' . $item['quantity'] . '</td>
            <td style="padding: 10px; text-align: left;">' . number_format($item['price'], 2) . ' ر.س</td>
        </tr>';
    }
    
    $itemsHtml .= '<tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="2" style="padding: 10px;">الإجمالي</td>
            <td style="padding: 10px; text-align: left;">' . number_format($orderDetails['total'], 2) . ' ر.س</td>
        </tr>
    </table>';
    
    $body = '
        <h2>مرحباً ' . htmlspecialchars($name) . '،</h2>
        <p>شكراً لطلبك من المكتبة الإلكترونية! 🎉</p>
        <p>تم استلام طلبك بنجاح وسيتم معالجته قريباً.</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">تفاصيل الطلب:</h3>
            <p><strong>رقم الطلب:</strong> #' . $orderDetails['order_id'] . '</p>
            <p><strong>تاريخ الطلب:</strong> ' . $orderDetails['date'] . '</p>
            <p><strong>حالة الطلب:</strong> ' . $orderDetails['status'] . '</p>
        </div>
        
        <h3>المنتجات المطلوبة:</h3>
        ' . $itemsHtml . '
        
        <p style="text-align: center;">
            <a href="' . url('customer/order_details.php?id=' . $orderDetails['order_id']) . '" class="btn">تتبع طلبك</a>
        </p>
        
        <p>إذا كان لديك أي استفسار، لا تتردد في التواصل معنا.</p>
        <p>مع تحياتنا،<br>فريق المكتبة الإلكترونية</p>
    ';
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send welcome email
 * إرسال بريد الترحيب
 * 
 * @param string $email User email
 * @param string $name User name
 * @return bool Success status
 */
function sendWelcomeEmail($email, $name) {
    $subject = 'مرحباً بك في المكتبة الإلكترونية!';
    $body = '
        <h2>مرحباً ' . htmlspecialchars($name) . '! 🎉</h2>
        <p>نحن سعداء بانضمامك إلى عائلة المكتبة الإلكترونية!</p>
        <p>يمكنك الآن الاستفادة من جميع مميزات حسابك:</p>
        <ul>
            <li>📚 تصفح آلاف الكتب في مختلف المجالات</li>
            <li>🛒 إضافة الكتب إلى سلة المشتريات</li>
            <li>❤️ حفظ الكتب المفضلة في قائمة الأمنيات</li>
            <li>⭐ تقييم الكتب ومشاركة رأيك</li>
            <li>📦 تتبع طلباتك بسهولة</li>
        </ul>
        <p style="text-align: center;">
            <a href="' . url('books.php') . '" class="btn">ابدأ التسوق الآن</a>
        </p>
        <p>إذا كنت بحاجة إلى مساعدة، فريق الدعم جاهز لخدمتك!</p>
        <p>مع تحياتنا،<br>فريق المكتبة الإلكترونية</p>
    ';
    
    return sendEmail($email, $subject, $body);
}

/**
 * Send contact form notification
 * إرسال إشعار نموذج الاتصال
 * 
 * @param string $name Sender name
 * @param string $email Sender email
 * @param string $subject Message subject
 * @param string $message Message content
 * @return bool Success status
 */
function sendContactNotification($name, $email, $subject, $message) {
    $adminEmail = 'admin@bookstore.sa';
    
    $emailSubject = 'رسالة جديدة من نموذج الاتصال: ' . $subject;
    $body = '
        <h2>رسالة جديدة من نموذج الاتصال</h2>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <p><strong>الاسم:</strong> ' . htmlspecialchars($name) . '</p>
            <p><strong>البريد الإلكتروني:</strong> ' . htmlspecialchars($email) . '</p>
            <p><strong>الموضوع:</strong> ' . htmlspecialchars($subject) . '</p>
            <p><strong>الرسالة:</strong></p>
            <div style="background: white; padding: 15px; border-radius: 5px; border-right: 4px solid #006c35;">
                ' . nl2br(htmlspecialchars($message)) . '
            </div>
        </div>
        <p style="margin-top: 20px;">
            <a href="mailto:' . htmlspecialchars($email) . '" class="btn">الرد على المرسل</a>
        </p>
    ';
    
    return sendEmail($adminEmail, $emailSubject, $body, ['reply_to' => $email]);
}

/**
 * Log email sending
 * تسجيل إرسال البريد
 * 
 * @param string $to Recipient
 * @param string $subject Subject
 * @param bool $success Success status
 */
function logEmail($to, $subject, $success) {
    $logFile = __DIR__ . '/../logs/email.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if not exists
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $status = $success ? 'SUCCESS' : 'FAILED';
    $logEntry = date('Y-m-d H:i:s') . " | {$status} | To: {$to} | Subject: {$subject}\n";
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
