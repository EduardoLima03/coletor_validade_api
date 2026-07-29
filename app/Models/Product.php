<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'custo'];

    public function barcodes()
    {
        return $this->hasMany(Barcode::class);
    }

    public function setCustoAttribute($value)
    {
        if (is_null($value) || $value === '' || $value === false) {
            $this->attributes['custo'] = 0;
            return;
        }

        $value = trim((string) $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        $this->attributes['custo'] = (float) str_replace(',', '.', $value);
    }
}
