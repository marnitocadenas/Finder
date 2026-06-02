<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class AuthPasswordRules
{
    public static function rules(bool $confirmed = true): array
    {
        $rules = [
            'required',
            'string',
            Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
        ];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }

    public static function optionalRules(bool $confirmed = false): array
    {
        $rules = [
            'nullable',
            'string',
            Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
        ];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }
}
