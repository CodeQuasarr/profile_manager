<?php

namespace App\Lib;

use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;


class TheCurrent
{

    public static function user(): ?User
    {
        if (Auth::check()) {
            return User::find(Auth::id());
        }
        return null;
    }

    public static function user_id(): ?int
    {
        return Auth::id();
    }


}
