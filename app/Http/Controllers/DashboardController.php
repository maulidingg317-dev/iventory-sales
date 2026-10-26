<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        // Semua angka dihitung dari database (query agregat), bukan ditulis statis.
        $stats = [
            'total_products'    => Product::count(),
            'low_stock'         => Product::lowStock()->count(),
            'sales_today'       => Sale::where('status', 'completed')
                                        ->whereDate('sold_at', today())->sum('total'),
            'sales_this_month'  => Sale::where('status', 'completed')
                                        ->whereYear('sold_at', now()->year)
                                        ->whereMonth('sold_at', now()->month)->sum('total'),
            'total_transactions'=> Sale::where('status', 'completed')->count(),
        ];

        $lowStockProducts = Product::with('category')
            ->lowStock()->orderBy('stock')->limit(5)->get();

        return view('dashboard', compact('stats', 'lowStockProducts'));
    }
}
