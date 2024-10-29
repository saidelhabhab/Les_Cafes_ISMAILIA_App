<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'year', 'month', 'quantity', 'unit', 'purchase_date', 'price', 'total_price']; // Added 'total_price'

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}

