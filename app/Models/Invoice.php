<?php

namespace App\Models;

use App\Models\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'client_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'notes',
        'footer',
        'pdf_path',
        'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'paid_at'    => 'datetime',
        'subtotal'   => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }
}
