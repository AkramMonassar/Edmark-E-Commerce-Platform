<?php
// حماية بسيطة من الهجمات المتكررة (تسجيل دخول، استعادة كلمة مرور، نماذج)
if (session_status() === PHP_SESSION_NONE) session_start();

function rate_limit_check($action, $maxAttempts = 5, $windowSeconds = 300) {
    $key = 'rate_' . $action;
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'window_start' => $now];
    }
    
    $data = $_SESSION[$key];
    
    // إذا انتهت النافذة، نبدأ من جديد
    if ($now - $data['window_start'] > $windowSeconds) {
        $_SESSION[$key] = ['attempts' => 1, 'window_start' => $now];
        return true;
    }
    
    // إذا تجاوز الحد الأقصى
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    // زيادة العداد
    $_SESSION[$key]['attempts']++;
    return true;
}

function rate_limit_reset($action) {
    $key = 'rate_' . $action;
    unset($_SESSION[$key]);
}