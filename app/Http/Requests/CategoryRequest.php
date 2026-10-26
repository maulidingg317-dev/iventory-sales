<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi sudah dijaga middleware can:manage-master di routes/web.php.
        return true;
    }

    public function rules(): array
    {
        // Saat update, route punya model {category}; abaikan barisnya sendiri
        // agar nama yang tidak diubah tidak dianggap duplikat.
        $id = $this->route('category')?->id;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->ignore($id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
            'name.unique'   => 'Nama kategori sudah dipakai.',
        ];
    }
}
