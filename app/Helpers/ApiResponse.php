<?php

namespace App\Helpers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiResponse
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public static function return400(string $error = "Bad Request"): JsonResponse
    {
        return response()->json(['status' => false, 'code' => ResponseAlias::HTTP_BAD_REQUEST, 'errors' => $error], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public static function return401(): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                "message" => "Identifiants incorrects.",
                "details" => "Sssurez-vous que vous avez entré les bonnes informations d'identification"
            ], ResponseAlias::HTTP_UNAUTHORIZED);
    }


    public static function return200(string $message = "Success", $data = null): JsonResponse
    {
        return response()->json(
            [
                'status' => true,
                'code' => ResponseAlias::HTTP_OK,
                'message' => $message,
                'data' => $data
            ],
            ResponseAlias::HTTP_OK
        );
    }

}
