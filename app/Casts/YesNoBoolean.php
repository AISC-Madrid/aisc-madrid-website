<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class YesNoBoolean implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return $value === 'yes';
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return $value ? 'yes' : 'no';
    }
}
