<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail('The :attribute must be at least 8 characters.');

            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('The :attribute must contain at least one uppercase letter.');

            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('The :attribute must contain at least one number.');

            return;
        }

        if (! preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('The :attribute must contain at least one special character.');
        }
    }

    public static function score(string $password): int
    {
        $score = 0;
        if (strlen($password) >= 8) {
            $score += 25;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score += 25;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += 25;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 25;
        }

        return $score;
    }

    public static function label(int $score): string
    {
        return match (true) {
            $score >= 100 => 'Strong',
            $score >= 75  => 'Good',
            $score >= 50  => 'Fair',
            $score > 0    => 'Weak',
            default       => '',
        };
    }
}
