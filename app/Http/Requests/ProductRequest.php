<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dijaga middleware can:manage-master di routes/web.php
    }

    protected function prepareForValidation(): void
    {
        // Rapikan SKU sebelum divalidasi agar "abc-1" dan "ABC-1" dianggap sama.
        if ($this->has('sku')) {
            $this->merge(['sku' => strtoupper(trim((string) $this->sku))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            // unique di sini juga memeriksa produk yang di-soft-delete,
            // konsisten dengan unique index di database.
            'sku'            => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($id)],
            'name'           => ['required', 'string', 'max:150'],
            'category_id'    => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'selling_price'  => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'minimum_stock'  => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required'            => 'SKU wajib diisi.',
            'sku.unique'              => 'SKU sudah dipakai produk lain.',
            'sku.max'                 => 'SKU maksimal 50 karakter.',
            'name.required'           => 'Nama produk wajib diisi.',
            'name.max'                => 'Nama produk maksimal 150 karakter.',
            'category_id.required'    => 'Kategori wajib dipilih.',
            'category_id.exists'      => 'Kategori yang dipilih tidak tersedia.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric'  => 'Harga beli harus berupa angka.',
            'purchase_price.min'      => 'Harga beli tidak boleh negatif.',
            'selling_price.required'  => 'Harga jual wajib diisi.',
            'selling_price.numeric'   => 'Harga jual harus berupa angka.',
            'selling_price.min'       => 'Harga jual tidak boleh negatif.',
            'minimum_stock.required'  => 'Stok minimum wajib diisi.',
            'minimum_stock.integer'   => 'Stok minimum harus berupa bilangan bulat.',
            'minimum_stock.min'       => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
