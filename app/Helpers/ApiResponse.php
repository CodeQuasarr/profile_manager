<?php

namespace App\Helpers;


use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiResponse
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public static function return400(string $error = "Mauvaise entrée de donnée"): JsonResponse
    {
        return response()->json(['status' => false, 'code' => ResponseAlias::HTTP_BAD_REQUEST, 'errors' => $error], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public static function return401(string $error = 'Identifiants incorrects'): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                "message" => $error,
                "details" => "Assurez-vous que vous avez entré les bonnes informations d'identification"
            ], ResponseAlias::HTTP_UNAUTHORIZED);
    }

    public static function return403(string $error = "Accès refusé"): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'code' => ResponseAlias::HTTP_FORBIDDEN,
                'errors' => $error
            ], ResponseAlias::HTTP_FORBIDDEN);
    }

    public static function return409(string $error = "Conflict"): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'code' => ResponseAlias::HTTP_CONFLICT,
                'errors' => $error
            ], ResponseAlias::HTTP_CONFLICT);
    }
    public static function return500(string $error = "Une erreur est survenue "): JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'code' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR,
                'errors' => $error
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
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
