<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_amount'   => (int) Invoice::sum('total'),
            'paid_amount'    => (int) Invoice::where('status', 'paid')->sum('total'),
            'pending_amount' => (int) Invoice::where('status', 'sent')->sum('total'),
            'overdue_amount' => (int) Invoice::where('status', 'overdue')->sum('total'),
            'total_count'    => Invoice::count(),
            'paid_count'     => Invoice::where('status', 'paid')->count(),
            'pending_count'  => Invoice::where('status', 'sent')->count(),
            'overdue_count'  => Invoice::where('status', 'overdue')->count(),
        ];

        return Inertia::render('Dashboard', compact('stats'));
    }
}
