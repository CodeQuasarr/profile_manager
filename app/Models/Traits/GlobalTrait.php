<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait GlobalTrait
{
    /**
     * @description Scope pour filtrer les données par champ et valeur
     *
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @return Builder
     */
    public function scopeFieldValue(Builder $query, string $field, $value): Builder
    {
        if (is_array($value) || $value instanceof Collection) {
            return $query->whereIn($field, $value);
        }
        return $query->where($field, $value);
    }
}
