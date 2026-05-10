<?php

namespace App\Modules\Invoices\Actions;

use App\Models\Company;
use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function generate(Company $company): string
    {
        $year   = now()->format('Y');
        $prefix = "INV-{$year}-";

        $last = Invoice::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('number', 'like', "{$prefix}%")
            ->orderByDesc('number')
            ->value('number');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
