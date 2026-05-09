<?php

namespace App\Modules\Clients\Services;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function list(Company $company, ?string $search = null): LengthAwarePaginator
    {
        return Client::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(Company $company, array $data): Client
    {
        return Client::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);
        return $client;
    }

    public function delete(Client $client): void
    {
        if ($client->hasInvoices()) {
            throw ValidationException::withMessages([
                'client' => __('clients.cannot_delete_with_invoices'),
            ]);
        }

        $client->delete();
    }
}
