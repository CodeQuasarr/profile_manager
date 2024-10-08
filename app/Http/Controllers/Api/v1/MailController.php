<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\SendSuccessfulAccountCreation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * @description Sends an account creation confirmation e-mail to the new user with the Coach Role
     * @param string $email
     * @param string $viewPath
     * @param string $theSubject
     * @param array $mailContents
     * @return void | JsonResponse
     */
    public static function sendSuccessfulAccountCreation(string $email, string $viewPath, string $theSubject, array $mailContents = [])
    {
        try {
            Mail::to($email)->send(new SendSuccessfulAccountCreation($viewPath, $theSubject, $mailContents));
        } catch (\Exception $e) {
             Log::error($e->getMessage());
             return ApiResponse::return500('Une erreur est survenue lors de l\'envoi de l\'e-mail de confirmation de création de compte');
        }
    }
}
