<?php

namespace Tests\Unit\Actions;

use App\Enums\DocumentType;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Modules\Invoices\Actions\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorDevisTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private InvoiceNumberGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company   = Company::factory()->create();
        $this->generator = new InvoiceNumberGenerator();
    }

    public function test_generates_invoice_prefix_by_default(): void
    {
        $number = $this->generator->generate($this->company);
        $this->assertStringStartsWith('INV-' . now()->format('Y') . '-', $number);
        $this->assertSame('INV-' . now()->format('Y') . '-0001', $number);
    }

    public function test_generates_devis_prefix_for_devis_type(): void
    {
        $number = $this->generator->generate($this->company, DocumentType::Devis);
        $this->assertStringStartsWith('DEV-' . now()->format('Y') . '-', $number);
        $this->assertSame('DEV-' . now()->format('Y') . '-0001', $number);
    }

    public function test_invoice_and_devis_numbering_are_independent(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);

        foreach (range(1, 5) as $i) {
            Invoice::factory()->create([
                'company_id' => $this->company->id,
                'client_id'  => $client->id,
                'type'       => 'invoice',
                'number'     => 'INV-' . now()->format('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
            ]);
        }

        $devisNumber = $this->generator->generate($this->company, DocumentType::Devis);
        $this->assertSame('DEV-' . now()->format('Y') . '-0001', $devisNumber);
    }

    public function test_sequential_devis_numbers_increment_correctly(): void
    {
        $client = Client::factory()->create(['company_id' => $this->company->id]);

        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id'  => $client->id,
            'type'       => 'devis',
            'due_date'   => null,
            'number'     => 'DEV-' . now()->format('Y') . '-0003',
        ]);

        $next = $this->generator->generate($this->company, DocumentType::Devis);
        $this->assertSame('DEV-' . now()->format('Y') . '-0004', $next);
    }
}
