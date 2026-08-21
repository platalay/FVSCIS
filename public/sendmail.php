<?php
define('APP_PASSWORD','nhgo bepk ulsr hsfv');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include ไฟล์จาก ZIP ที่แตกมา
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// สร้าง object
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'platalay@gmail.com';          // Gmail ของคุณ
    $mail->Password   = APP_PASSWORD;             // App Password ที่สร้างไว้
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;     // ใช้ SSL
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('no-reply@fisheries.go.th', 'platalay');
    $mail->addAddress('love_130139@hotmail.com', 'platalay'); // ผู้รับ
   //$mail->addAddress('ptsinanun@yahoo.com', 'platalay'); // ผู้รับ
    // Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8'; // ✅ รองรับภาษาไทย
    $mail->Subject = 'ทดสอบส่งอีเมลด้วย PHPMailer';
    $mail->Body    = '<b>สวัสดีครับ</b> นี่คือการทดสอบส่งอีเมลผ่าน Gmail SMTP ด้วย PHPMailer แบบ zip';
    $mail->AltBody = 'สวัสดีครับ นี่คือการทดสอบส่งอีเมลแบบไม่ใช้ HTML';

    $mail->send();
    echo 'ส่งอีเมลสำเร็จ!';
} catch (Exception $e) {
    echo "ส่งอีเมลไม่สำเร็จ. ข้อผิดพลาด: {$mail->ErrorInfo}";
}