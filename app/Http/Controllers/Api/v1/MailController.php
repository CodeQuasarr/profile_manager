<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Mail\SendSuccessfulAccountCreation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * @description Envoie un mail de confirmation de création de compte au nouvel utilisateur avec le Role de coach.
     * @param string $email
     * @param string $viewPath
     * @param string $theSubject
     * @param string $mailContents
     * @return void | JsonResponse
     */
    public static function sendSuccessfulAccountCreation(string $email, string $viewPath, string $theSubject, array $mailContents = [])
    {
        try {
            Mail::to($email)->send(new SendSuccessfulAccountCreation($viewPath, $theSubject, $mailContents));
        } catch (\Exception $e) {
             Log::error($e->getMessage());
            return response()->json([
                'message' => 'Failed to send Auth API key '. $e->getMessage(),
            ], 500);
        }
    }
}
