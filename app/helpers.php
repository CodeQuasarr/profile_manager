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

        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        // Encode the header and payload to base64
        $headerEncoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');

        // Generate the signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", config('app.token_secret_key'), true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
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
        // Split the token into its three parts
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);

        $signature = base64_decode(strtr($signatureEncoded, '-_', '+/'));
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", config('app.token_secret_key'), true);

        // Check if the provided signature matches the expected signature
        if ($signature !== $expectedSignature) {
            throw new Exception('Signature invalide');
        }

        $payload = base64_decode(strtr($payloadEncoded, '-_', '+/'));;
        $data = json_decode($payload, true);

        if ($data['expires_at'] < time()) {
            throw new Exception('Le token a expiré');
        }

        return $data;
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
            throw new \Exception('Le token a expiré');
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
