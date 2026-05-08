<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'logo_path',
        'address',
        'postal_code',
        'city',
        'country',
        'email',
        'phone',
        'vat_number',
        'iban',
        'legal_footer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceTemplates(): HasMany
    {
        return $this->hasMany(InvoiceTemplate::class);
    }

    public function defaultTemplate(): HasMany
    {
        return $this->hasMany(InvoiceTemplate::class)->where('is_default', true);
    }

    public function hasLogo(): bool
    {
        return $this->logo_path !== null;
    }
}
