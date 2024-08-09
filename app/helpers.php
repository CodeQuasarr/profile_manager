<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

if (!function_exists('generateToken')) {
    /**
     * Generate a token
     *
     * @param int $userId
     * @param int $delayExpiration
     * @return string
     */
    function generateToken(int $userId, int $delayExpiration = 30): string
    {
        $expiresAt = Carbon::now()->addMinutes($delayExpiration);

        $payloadJson = json_encode([
            'user_id' => $userId,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

        // Chiffrement du payload pour sécuriser le payload lors de l'envoi du token
        $encryptedPayload = Crypt::encryptString($payloadJson);

        // Signature du token
        $secretKey = config('app.token_secret_key');
        $signature = hash_hmac('sha256', $encryptedPayload, $secretKey);

        // Construction du token final
        return base64_encode($encryptedPayload . '.' . $signature);
    }

}

if (!function_exists('verifyToken')) {
    /**
     * @param string $token
     * @return mixed
     * @throws Exception
     */
    function verifyToken(string $token): mixed
    {
        // Décodage du token
        $decodedToken = base64_decode($token);
        [$encryptedPayload, $signature] = explode('.', $decodedToken);

        if (!$encryptedPayload || !$signature) {
            throw new \Exception('Invalid token');
        }

        $secretKey = config('app.token_secret_key');
        $expectedSignature = hash_hmac('sha256', $encryptedPayload, $secretKey);

        // Vérification de la signature
        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Invalid token signature');
        }
        return json_decode(Crypt::decryptString($encryptedPayload), true);
    }
}

if (!function_exists('check_token_expiration')) {

    /**
     * @param string $token
     * @return void
     * @throws Exception
     */
    function check_token_expiration(string $token): void
    {
        $payload = verifyToken($token);

        if (Carbon::parse($payload['expires_at'])->isPast()) {
            throw new \Exception('Token expired');
        }
    }
}

if (!function_exists('uppercase_first_letter')) {
    /**
     * @param string $string
     * @return string
     */
    function uppercase_first_letter(string $string): string
    {
        return ucfirst($string);
    }
}

if (!function_exists('uppercase_char')) {
    /**
     * @param string $string
     * @return string
     */
    function uppercase_char(string $string): string
    {
        return strtoupper($string);
    }
}
