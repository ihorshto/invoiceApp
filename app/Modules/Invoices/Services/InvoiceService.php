<?php

namespace App\Modules\Invoices\Services;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    public function list(Company $company, ?string $search, ?string $status): LengthAwarePaginator
    {
        return Invoice::withoutGlobalScopes()
            ->with('client:id,name')
            ->where('company_id', $company->id)
            ->when($search, fn ($q) => $q->where('number', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
            )
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('issue_date')
            ->paginate(15)
            ->withQueryString();
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
    }
}
