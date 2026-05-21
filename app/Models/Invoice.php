<?php

namespace App\Models;

use App\Enums\DevisStatus;
use App\Enums\DocumentType;
use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory, HasCompanyScope;

    protected $fillable = [
        'company_id',
        'client_id',
        'source_document_id',
        'type',
        'number',
        'status',
        'issue_date',
        'due_date',
        'valid_until',
        'estimated_start_date',
        'subtotal',
        'vat_amount',
        'total',
        'currency',
        'notes',
        'footer',
        'chantier_address',
        'payment_conditions',
        'pdf_path',
        'paid_at',
        'accepted_at',
    ];

    protected $casts = [
        'type'                 => DocumentType::class,
        'issue_date'           => 'date',
        'due_date'             => 'date',
        'valid_until'          => 'date',
        'estimated_start_date' => 'date',
        'paid_at'              => 'datetime',
        'accepted_at'          => 'datetime',
        'subtotal'             => 'decimal:2',
        'vat_amount'           => 'decimal:2',
        'total'                => 'decimal:2',
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

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'source_document_id');
    }

    public function convertedInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'source_document_id');
    }

    public function isInvoice(): bool
    {
        return $this->type === DocumentType::Invoice;
    }

    public function isDevis(): bool
    {
        return $this->type === DocumentType::Devis;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid->value;
    }

    public function isAccepted(): bool
    {
        return $this->isDevis() && $this->status === DevisStatus::Accepted->value;
    }

    public function isEditable(): bool
    {
        if ($this->isDevis()) {
            return in_array($this->status, [DevisStatus::Draft->value, DevisStatus::Sent->value], true);
        }

        return in_array($this->status, [InvoiceStatus::Draft->value, InvoiceStatus::Sent->value], true);
    }

    public function canConvertToInvoice(): bool
    {
        return $this->isDevis()
            && in_array($this->status, [DevisStatus::Accepted->value, DevisStatus::Sent->value], true)
            && ! $this->convertedInvoice()->exists();
    }
}
