# Catatan persiapan code interview

Gunakan catatan ini untuk **belajar**, lalu buka kodenya dan telusuri sendiri alurnya.
Dosen bisa meminta Anda menjelaskan atau mengubah kode secara langsung.

## 1. Alur satu request penjualan (ikuti di kode)
`routes/web.php` (middleware auth + can:manage-sales) → `SaleController@store`
→ `SaleRequest` (validasi) → `SaleService::create` (transaction) → redirect ke `sales.show`.

## 2. Pertanyaan yang hampir pasti muncul
| Pertanyaan | Jawaban singkat | Lihat di |
|---|---|---|
| Mengapa memakai `DB::transaction`? | Header, item, dan stok harus berhasil bersama atau batal bersama. Tanpa itu bisa ada penjualan tanpa pengurangan stok. | `SaleService::create` |
| Mengapa `lockForUpdate`? | Dua kasir menjual produk yang sama bersamaan. Tanpa kunci, keduanya membaca stok lama dan stok bisa jadi negatif (overselling). | `SaleService::create` |
| Mengapa total dihitung di server? | Browser bisa dimanipulasi. Harga dibaca dari database, total dijumlahkan dari item. | `SaleService`, `test_sale_decreases_stock_and_total_is_calculated_by_server` |
| Bagaimana mencegah Staff membuka URL admin? | Middleware `can:` di route (server-side). `@can` di Blade hanya menyembunyikan menu. | `routes/web.php`, `AuthorizationTest` |
| Mengapa `stock` tidak di `$fillable`? | Agar stok tidak bisa diubah lewat form (mass assignment). Hanya lewat transaksi. | `Product::$fillable` |
| Mengapa foreign key di tabel detail? | Satu transaksi punya banyak item; sisi "banyak" menyimpan FK. | migration `create_sales_table` |
| Mengapa soft delete untuk produk? | Riwayat transaksi tetap utuh. Produk yang sudah dipakai juga ditolak untuk dihapus. | `Product`, `ProductController::destroy` |
| Apa itu N+1 dan bagaimana dihindari? | Query berulang per baris. Dihindari dengan `with()` (eager loading) dan `withCount()`. | `ProductController@index`, `CategoryController@index` |
| Bagaimana pembatalan tidak memulihkan stok dua kali? | Baris transaksi dikunci, lalu status dicek `completed` sebelum stok dipulihkan. | `SaleService::cancel` |
| Mengapa produk diproses terurut `product_id`? | Urutan kunci yang konsisten mencegah deadlock antar transaksi. | `sortBy('product_id')` |

## 3. Latihan modifikasi yang mungkin diminta
- Tambah field pada produk (migration baru + `$fillable` + form + validasi).
- Ubah batas stok rendah menjadi `< minimum_stock` (ubah `scopeLowStock`).
- Tambah filter pada daftar (ikuti pola `when(...)` + `withQueryString()`).
- Tambah pesan validasi baru (edit `messages()` pada Form Request).
