<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'unit_price',
        'unit',
        'quantity',
        'vat_rate',
        'total_ht',
        'total_ttc',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity'   => 'decimal:3',
        'vat_rate'   => 'decimal:2',
        'total_ht'   => 'decimal:2',
        'total_ttc'  => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
