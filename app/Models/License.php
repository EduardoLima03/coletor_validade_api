<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'client_name',
        'cnpj',
        'license_key',
        'plan',
        'max_stores',
        'max_users',
        'valid_from',
        'valid_until',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'active' => 'boolean',
        ];
    }

    public static function generateKey(): string
    {
        return strtoupper(implode('-', [
            Str::random(8),
            Str::random(8),
            Str::random(8),
            Str::random(8),
        ]));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now());
    }

    public function isValid(): bool
    {
        return $this->active
            && $this->valid_from->isPast()
            && $this->valid_until->isFuture();
    }
}
