<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dijaga middleware can:manage-sales
    }

    public function rules(): array
    {
        return [
            'supplier_id'          => ['required', 'exists:suppliers,id'],
            'items'                => ['required', 'array', 'min:1', 'max:50'],
            // whereNull('deleted_at'): produk yang sudah di-soft-delete tidak boleh dibeli.
            'items.*.product_id'   => ['required', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost'    => ['required', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'        => 'Pemasok wajib dipilih.',
            'supplier_id.exists'          => 'Pemasok yang dipilih tidak tersedia.',
            'items.required'              => 'Tambahkan minimal satu item produk.',
            'items.min'                   => 'Tambahkan minimal satu item produk.',
            'items.max'                   => 'Maksimal 50 item per transaksi.',
            'items.*.product_id.required' => 'Produk pada setiap baris wajib dipilih.',
            'items.*.product_id.exists'   => 'Ada produk yang tidak tersedia atau sudah dihapus.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh muncul dua kali dalam satu transaksi.',
            'items.*.quantity.required'   => 'Kuantitas wajib diisi.',
            'items.*.quantity.integer'    => 'Kuantitas harus berupa bilangan bulat.',
            'items.*.quantity.min'        => 'Kuantitas harus lebih dari 0.',
            'items.*.quantity.max'        => 'Kuantitas terlalu besar.',
            'items.*.unit_cost.required'  => 'Harga beli wajib diisi.',
            'items.*.unit_cost.numeric'   => 'Harga beli harus berupa angka.',
            'items.*.unit_cost.min'       => 'Harga beli tidak boleh negatif.',
        ];
    }
}
