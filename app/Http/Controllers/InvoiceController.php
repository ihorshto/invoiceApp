<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Modules\Invoices\Actions\CreateInvoiceAction;
use App\Modules\Invoices\Actions\MarkAsPaidAction;
use App\Modules\Invoices\Actions\UpdateInvoiceAction;
use App\Modules\Invoices\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request): Response
    {
        $company  = $request->user()->company;
        $invoices = $this->service->list($company, $request->input('search'), $request->input('status'));

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => $request->only('search', 'status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->company;

        return Inertia::render('Invoices/Create', [
            'clients'  => Client::withoutGlobalScopes()->where('company_id', $company->id)->get(['id', 'name']),
            'products' => Product::withoutGlobalScopes()->where('company_id', $company->id)->where('is_active', true)->get(['id', 'name', 'unit_price', 'unit', 'vat_rate']),
        ]);
    }

    public function store(Request $request, CreateInvoiceAction $action): RedirectResponse
    {
        $data = $request->validate([
            'client_id'      => ['required', 'integer', 'exists:clients,id'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:issue_date'],
            'currency'       => ['sometimes', 'string', 'size:3'],
            'notes'          => ['nullable', 'string'],
            'footer'         => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.001'],
            'items.*.vat_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.unit'        => ['nullable', 'string'],
            'items.*.product_id'  => ['nullable', 'integer'],
        ]);

        $invoice = $action->execute($request->user()->company, $data);

        return redirect()->route('invoices.show', $invoice);
    }

    public function show(Invoice $invoice): Response
    {
        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice->load('client', 'items.product'),
        ]);
    }

    public function edit(Request $request, Invoice $invoice): Response
    {
        $company = $request->user()->company;

        return Inertia::render('Invoices/Edit', [
            'invoice'  => $invoice->load('items'),
            'clients'  => Client::withoutGlobalScopes()->where('company_id', $company->id)->get(['id', 'name']),
            'products' => Product::withoutGlobalScopes()->where('company_id', $company->id)->where('is_active', true)->get(['id', 'name', 'unit_price', 'unit', 'vat_rate']),
        ]);
    }

    public function update(Request $request, Invoice $invoice, UpdateInvoiceAction $action): RedirectResponse
    {
        $data = $request->validate([
            'client_id'      => ['required', 'integer', 'exists:clients,id'],
            'issue_date'     => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:issue_date'],
            'currency'       => ['sometimes', 'string', 'size:3'],
            'notes'          => ['nullable', 'string'],
            'footer'         => ['nullable', 'string'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.001'],
            'items.*.vat_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.unit'        => ['nullable', 'string'],
            'items.*.product_id'  => ['nullable', 'integer'],
        ]);

        $action->execute($invoice, $data);

        return redirect()->route('invoices.show', $invoice);
    }

    public function markPaid(Invoice $invoice, MarkAsPaidAction $action): RedirectResponse
    {
        $action->execute($invoice);
        return redirect()->route('invoices.show', $invoice);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->service->delete($invoice);
        return redirect()->route('invoices.index');
    }
}
