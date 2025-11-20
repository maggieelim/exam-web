<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidZone implements Rule
{
    public function passes($attribute, $value)
    {
        if (!$value) return true;

        $zones = ['1a', '1b', '1c', '2a', '2b', '2c', '3a', '3b', '3c', '3d'];
        $parts = explode('-', strtolower(trim($value)));

        foreach ($parts as $p) {
            if (!in_array($p, $zones)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'Zona tidak valid. Gunakan kode seperti 1a, 2b, atau range 1a-2b.';
    }
}
