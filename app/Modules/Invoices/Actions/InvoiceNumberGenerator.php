<?php

namespace App\Modules\Invoices\Actions;

use App\Enums\DocumentType;
use App\Models\Company;
use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function generate(Company $company, DocumentType $type = DocumentType::Invoice): string
    {
        $year   = now()->format('Y');
        $prefix = ($type === DocumentType::Devis ? 'DEV-' : 'INV-') . $year . '-';

        $last = Invoice::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('number', 'like', "{$prefix}%")
            ->orderByDesc('number')
            ->value('number');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
