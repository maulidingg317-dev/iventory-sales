<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $service)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'q'         => ['nullable', 'string', 'max:50'],
            'status'    => ['nullable', 'in:confirmed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $purchases = Purchase::with(['supplier', 'user'])
            // Staff hanya melihat transaksi miliknya; Admin melihat semua.
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->filled('q'), fn ($q) => $q->where('purchase_number', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('purchased_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('purchased_at', '<=', $request->date_to))
            ->orderByDesc('purchased_at')->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products'  => Product::orderBy('name')->get(),
        ]);
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $purchase = $this->service->create($request->user(), (int) $data['supplier_id'], $data['items']);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian berhasil disimpan dan stok telah bertambah.');
    }

    public function show(Purchase $purchase)
    {
        $this->authorize('view-transaction', $purchase);

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        try {
            $this->service->cancel($purchase);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Pembelian dibatalkan dan stok dikembalikan.');
    }
}
