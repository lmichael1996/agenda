<?php
/**
 * Sistema CAPTCHA sicuro con protezioni multiple
 */

if (!defined('AGENDA_APP')) {
    http_response_code(403);
    exit('Accesso non autorizzato');
}

class CaptchaManager {
    
    // Tempo minimo per completare CAPTCHA (anti-bot)
    const MIN_SOLVE_TIME = 2; // secondi
    
    // Tempo massimo validità CAPTCHA
    const MAX_VALID_TIME = 300; // 5 minuti
    
    // Numero massimo tentativi per IP
    const MAX_ATTEMPTS_PER_IP = 10;
    
    /**
     * Genera CAPTCHA sicuro
     */
    public static function generateCaptcha() {
        // Genera challenge ID criptograficamente sicuro
        $challenge_id = bin2hex(random_bytes(16));
        
        // Hash challenge per prevenire manipolazioni
        $challenge_hash = hash('sha256', $challenge_id . $_SERVER['HTTP_USER_AGENT'] . session_id());
        
        // Salva dati in sessione
        $_SESSION['captcha_challenge_id'] = $challenge_id;
        $_SESSION['captcha_hash'] = $challenge_hash;
        $_SESSION['captcha_time'] = time();
        $_SESSION['captcha_ip'] = self::getClientIP();
        
        // Tracking tentativi per IP
        self::trackAttempt();
        
        return [
            'type' => 'checkbox',
            'challenge_id' => $challenge_id
        ];
    }
    
    /**
     * Verifica CAPTCHA con controlli multipli
     */
    public static function verifyCaptcha($challengeId, $userSolved) {
        // Controlli di base
        if (empty($challengeId) || empty($userSolved)) {
            self::logSuspiciousActivity('Empty challenge or response');
            return false;
        }
        
        // Verifica challenge ID in sessione
        if (!isset($_SESSION['captcha_challenge_id']) || $_SESSION['captcha_challenge_id'] !== $challengeId) {
            self::logSuspiciousActivity('Invalid challenge ID');
            return false;
        }
        
        // Verifica hash integrità
        $expectedHash = hash('sha256', $challengeId . $_SERVER['HTTP_USER_AGENT'] . session_id());
        if (!isset($_SESSION['captcha_hash']) || !hash_equals($_SESSION['captcha_hash'], $expectedHash)) {
            self::logSuspiciousActivity('Hash mismatch - possible manipulation');
            return false;
        }
        
        // Controllo timing (anti-bot)
        if (!isset($_SESSION['captcha_time']) || (time() - $_SESSION['captcha_time']) < self::MIN_SOLVE_TIME) {
            self::logSuspiciousActivity('Solved too quickly - possible bot');
            return false;
        }
        
        // Controllo scadenza
        if ((time() - $_SESSION['captcha_time']) > self::MAX_VALID_TIME) {
            self::logSuspiciousActivity('Challenge expired');
            return false;
        }
        
        // Verifica IP consistente
        if (!isset($_SESSION['captcha_ip']) || $_SESSION['captcha_ip'] !== self::getClientIP()) {
            self::logSuspiciousActivity('IP address changed during challenge');
            return false;
        }
        
        // Controlla rate limiting per IP
        if (self::isRateLimited()) {
            self::logSuspiciousActivity('Rate limit exceeded');
            return false;
        }
        
        // Verifica che il valore sia quello atteso
        if ($userSolved !== '1') {
            return false;
        }
        
        // Tutto OK - pulisci e conferma
        self::cleanupCaptcha();
        return true;
    }
    
    /**
     * Ottieni IP client reale
     */
    private static function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Tracking tentativi per prevenire spam
     */
    private static function trackAttempt() {
        $ip = self::getClientIP();
        $key = 'captcha_attempts_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        // Pulisci tentativi vecchi (oltre 1 ora)
        $_SESSION[$key] = array_filter($_SESSION[$key], function($time) {
            return (time() - $time) < 3600;
        });
        
        // Aggiungi tentativo corrente
        $_SESSION[$key][] = time();
    }
    
    /**
     * Controlla se IP è rate limited
     */
    private static function isRateLimited() {
        $ip = self::getClientIP();
        $key = 'captcha_attempts_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        // Conta tentativi nell'ultima ora
        $recentAttempts = array_filter($_SESSION[$key], function($time) {
            return (time() - $time) < 3600;
        });
        
        return count($recentAttempts) > self::MAX_ATTEMPTS_PER_IP;
    }
    
    /**
     * Log attività sospette
     */
    private static function logSuspiciousActivity($reason) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_id' => session_id(),
            'reason' => $reason,
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        
        // Log in sessione per debug (in produzione usare file log)
        if (!isset($_SESSION['security_logs'])) {
            $_SESSION['security_logs'] = [];
        }
        
        $_SESSION['security_logs'][] = $logData;
        
        // Mantieni solo ultimi 50 log
        if (count($_SESSION['security_logs']) > 50) {
            $_SESSION['security_logs'] = array_slice($_SESSION['security_logs'], -50);
        }
    }
    
    /**
     * Pulisci sessioni CAPTCHA
     */
    public static function cleanupCaptcha() {
        $keys = [
            'captcha_challenge_id',
            'captcha_hash', 
            'captcha_time',
            'captcha_ip'
        ];
        
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Ottieni statistiche sicurezza (per admin)
     */
    public static function getSecurityStats() {
        return [
            'total_attempts' => count($_SESSION['security_logs'] ?? []),
            'recent_logs' => array_slice($_SESSION['security_logs'] ?? [], -10),
            'rate_limited' => self::isRateLimited()
        ];
    }
}
?>