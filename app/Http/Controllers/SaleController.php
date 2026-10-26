<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(private SaleService $service)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'q'         => ['nullable', 'string', 'max:50'],
            'status'    => ['nullable', 'in:completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $sales = Sale::with('user')
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->filled('q'), fn ($q) => $q->where('sale_number', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('sold_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('sold_at', '<=', $request->date_to))
            ->orderByDesc('sold_at')->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $sale = $this->service->create($request->user(), $request->validated()['items']);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan berhasil disimpan dan stok telah berkurang.');
    }

    public function show(Sale $sale)
    {
        $this->authorize('view-transaction', $sale);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $this->service->cancel($sale);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}
