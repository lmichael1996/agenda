<?php
/**
 * CAPTCHA semplice con checkbox
 */

if (!defined('AGENDA_APP')) {
    http_response_code(403);
    exit('Accesso non autorizzato');
}

class CaptchaManager {
    
    /**
     * Genera CAPTCHA checkbox
     */
    public static function generateCaptcha() {
        $challenge_id = uniqid('captcha_', true);
        $_SESSION['captcha_challenge_id'] = $challenge_id;
        $_SESSION['captcha_time'] = time();
        
        return [
            'type' => 'checkbox',
            'challenge_id' => $challenge_id
        ];
    }
    
    /**
     * Verifica CAPTCHA
     */
    public static function verifyCaptcha($type, $userInput) {
        if ($type === 'checkbox') {
            // Verifica che il checkbox sia stato cliccato
            return !empty($userInput);
        }
        return false;
    }
    
    /**
     * Pulisci sessioni CAPTCHA
     */
    public static function cleanupCaptcha() {
        unset($_SESSION['captcha_challenge_id'], $_SESSION['captcha_time']);
    }
}
?>