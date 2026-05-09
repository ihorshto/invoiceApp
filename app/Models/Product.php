<?php

namespace App\Models;

use App\Models\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'unit_price',
        'unit',
        'vat_rate',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'vat_rate'   => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Models\InvoiceItem::class);
    }
}
