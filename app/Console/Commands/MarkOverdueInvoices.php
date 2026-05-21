<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Mark sent invoices whose due_date has passed as overdue';

    public function handle(): void
    {
        $count = Invoice::withoutGlobalScopes()
            ->where('type', 'invoice')
            ->where('status', 'sent')
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} invoice(s) as overdue.");
    }
}
