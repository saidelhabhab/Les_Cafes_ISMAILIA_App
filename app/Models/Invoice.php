<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\InvoiceItem;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'amount',
        'total_amount_with_tva',
        'status',
        'due_date',
        'checkDate',
        'factor_code',         // Added field
        'factor_bar_code',     // Added field
       // 'final_price',         // Added field
        'remaining_price',      // Added field
        'payment_type',        // Added field
        'tva',                 // Added field
        'amount_tva',
        'amount_in_words_en',
        'amount_in_words_fr',
        'amount_in_words_ar',
    ];



    // Define the relationship with the 'Item' model
    public function invoice_items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }


    // Define the relationship with the 'Client' model
    public function client()
    {
        return $this->belongsTo(Client::class); // Assuming 'Invoice' belongs to 'Client'
    }
}
