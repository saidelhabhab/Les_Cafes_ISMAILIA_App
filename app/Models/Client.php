<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cin',
        'name',
        'email',
        'phone',
        'address',
        'final_price',
        'remaining_price'

    ];


    public function invoices()
{
    return $this->hasMany(Invoice::class);
}

}
