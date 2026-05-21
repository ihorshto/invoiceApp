<?php

namespace App\Http\Controllers;

use App\Enums\DevisStatus;
use App\Enums\DocumentType;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Modules\Invoices\Actions\ConvertToInvoiceAction;
use App\Modules\Invoices\Actions\CreateInvoiceAction;
use App\Modules\Invoices\Actions\GeneratePdfAction;
use App\Modules\Invoices\Actions\UpdateInvoiceAction;
use App\Modules\Invoices\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevisController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request): Response
    {
        $company = $request->user()->company;
        $devis   = $this->service->list(
            $company,
            $request->input('search'),
            $request->input('status'),
            DocumentType::Devis
        );

        return Inertia::render('Devis/Index', [
            'devis'   => $devis,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->company;

        return Inertia::render('Devis/Create', [
            'clients'  => Client::withoutGlobalScopes()->where('company_id', $company->id)->get(['id', 'name']),
            'products' => Product::withoutGlobalScopes()->where('company_id', $company->id)->where('is_active', true)->get(['id', 'name', 'unit_price', 'unit', 'vat_rate']),
        ]);
    }

    public function store(Request $request, CreateInvoiceAction $action): RedirectResponse
    {
        $data = $request->validate([
            'client_id'              => ['required', 'integer', 'exists:clients,id'],
            'issue_date'             => ['required', 'date'],
            'valid_until'            => ['required', 'date', 'after_or_equal:issue_date'],
            'estimated_start_date'   => ['nullable', 'date'],
            'currency'               => ['sometimes', 'string', 'size:3'],
            'notes'                  => ['nullable', 'string'],
            'footer'                 => ['nullable', 'string'],
            'chantier_address'       => ['nullable', 'string'],
            'payment_conditions'     => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.description'    => ['required', 'string'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.001'],
            'items.*.vat_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.unit'           => ['nullable', 'string'],
            'items.*.product_id'     => ['nullable', 'integer'],
        ]);

        $data['type'] = DocumentType::Devis->value;

        $devis = $action->execute($request->user()->company, $data);

        return redirect()->route('devis.show', $devis);
    }

    public function show(Invoice $devis): Response
    {
        abort_unless($devis->isDevis(), 404);

        $devis->load('client', 'items.product', 'convertedInvoice');

        return Inertia::render('Devis/Show', [
            'devis'              => $devis,
            'canConvert'         => $devis->canConvertToInvoice(),
            'convertedInvoice'   => $devis->convertedInvoice ? $devis->convertedInvoice->only(['id', 'number']) : null,
        ]);
    }

    public function edit(Request $request, Invoice $devis): Response
    {
        abort_unless($devis->isDevis(), 404);

        $company = $request->user()->company;

        return Inertia::render('Devis/Edit', [
            'devis'    => $devis->load('items'),
            'clients'  => Client::withoutGlobalScopes()->where('company_id', $company->id)->get(['id', 'name']),
            'products' => Product::withoutGlobalScopes()->where('company_id', $company->id)->where('is_active', true)->get(['id', 'name', 'unit_price', 'unit', 'vat_rate']),
        ]);
    }

    public function update(Request $request, Invoice $devis, UpdateInvoiceAction $action): RedirectResponse
    {
        abort_unless($devis->isDevis(), 404);

        $data = $request->validate([
            'client_id'              => ['required', 'integer', 'exists:clients,id'],
            'issue_date'             => ['required', 'date'],
            'valid_until'            => ['required', 'date', 'after_or_equal:issue_date'],
            'estimated_start_date'   => ['nullable', 'date'],
            'currency'               => ['sometimes', 'string', 'size:3'],
            'notes'                  => ['nullable', 'string'],
            'footer'                 => ['nullable', 'string'],
            'chantier_address'       => ['nullable', 'string'],
            'payment_conditions'     => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.description'    => ['required', 'string'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.001'],
            'items.*.vat_rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.unit'           => ['nullable', 'string'],
            'items.*.product_id'     => ['nullable', 'integer'],
        ]);

        $action->execute($devis, $data);

        return redirect()->route('devis.show', $devis);
    }

    public function destroy(Invoice $devis): RedirectResponse
    {
        abort_unless($devis->isDevis(), 404);

        $this->service->delete($devis);

        return redirect()->route('devis.index');
    }

    public function updateStatus(Request $request, Invoice $devis): RedirectResponse
    {
        abort_unless($devis->isDevis(), 404);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', DevisStatus::values())],
        ]);

        $updates = ['status' => $data['status']];

        if ($data['status'] === DevisStatus::Accepted->value) {
            $updates['accepted_at'] = now();
        } elseif ($devis->status === DevisStatus::Accepted->value) {
            $updates['accepted_at'] = null;
        }

        $devis->update($updates);

        return redirect()->route('devis.show', $devis);
    }

    public function pdf(Invoice $devis, GeneratePdfAction $action): \Illuminate\Http\Response
    {
        abort_unless($devis->isDevis(), 404);

        return $action->stream($devis);
    }

    public function convert(Invoice $devis, ConvertToInvoiceAction $action): RedirectResponse
    {
        abort_unless($devis->isDevis(), 404);

        $invoice = $action->execute($devis);

        return redirect()->route('invoices.show', $invoice);
    }
}
