<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {

            return parent::authenticate();
        } catch (ValidationException $e) {

            throw ValidationException::withMessages([

                // pindahkan error ke password
                'data.password' => 'Password atau Email yang dimasukkan salah.',

            ]);
        }
    }
}
