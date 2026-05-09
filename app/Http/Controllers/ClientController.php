<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Modules\Clients\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $service) {}

    public function index(Request $request): Response
    {
        $company = auth()->user()->company;

        return Inertia::render('Clients/Index', [
            'clients' => $company
                ? $this->service->list($company, $request->get('search'))
                : [],
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $company = auth()->user()->company;

        abort_if(! $company, 403, 'Настройте компанію спочатку.');

        $this->service->create($company, $request->validated());

        return redirect()->route('clients.index')->with('success', __('clients.created'));
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', ['client' => $client]);
    }

    public function update(StoreClientRequest $request, Client $client): RedirectResponse
    {
        $this->service->update($client, $request->validated());

        return redirect()->route('clients.index')->with('success', __('clients.updated'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->service->delete($client);

        return redirect()->route('clients.index')->with('success', __('clients.deleted'));
    }
}
