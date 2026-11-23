<?php

namespace App\Auth;

use App\Services\TokenTipi;
use Illuminate\Http\Request;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Passport\Token;

/** Passport TokenGuard + token tipi denetimi (App\Services\TokenTipi). */
class TipliTokenGuard extends TokenGuard
{
    public function user(Request $request)
    {
        $user = parent::user($request);
        if (!$user || !method_exists($user, 'token')) return $user;
        $token = $user->token();
        if ($token instanceof Token && !TokenTipi::uygun($token->name, $user)) return null;
        return $user;
    }
}
