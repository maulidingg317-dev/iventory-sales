<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dijaga middleware can:manage-sales
    }

    public function rules(): array
    {
        // Sengaja TIDAK ada aturan untuk harga atau total:
        // keduanya diambil/dihitung server dari database.
        return [
            'items'              => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
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
        ];
    }
}
