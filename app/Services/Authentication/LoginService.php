<?php

namespace App\Services\Authentication;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class LoginService
{
    public function handleLogin(array $data)
    {
        $loginDetails = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        if (!Auth::attempt($loginDetails)) {
            throw new UnprocessableEntityHttpException('Invalid login details.');
        }
    }
}
