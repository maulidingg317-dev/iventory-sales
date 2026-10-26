{{--
    Baris item dinamis untuk form pembelian & penjualan.
    Variabel: $products (Collection<Product>), $mode ('purchase' | 'sale').
    Catatan: total di layar hanya perkiraan. Total sebenarnya dihitung ulang oleh server.
--}}
@php
    $priceKey = $mode === 'purchase' ? 'purchase_price' : 'selling_price';
    $productData = $products->map(fn ($p) => [
        'id'    => $p->id,
        'label' => $p->sku . ' - ' . $p->name . ' (stok ' . $p->stock . ')',
        'price' => (float) $p->{$priceKey},
    ])->values();
@endphp

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Produk</th>
                <th style="width:120px">Qty</th>
                <th style="width:190px">{{ $mode === 'purchase' ? 'Harga beli' : 'Harga jual' }}</th>
                <th style="width:170px" class="text-end">Subtotal</th>
                <th style="width:50px"></th>
            </tr>
        </thead>
        <tbody id="items-body"></tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total (perkiraan)</th>
                <th class="text-end" id="grand-total">Rp 0</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
<button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="add-row">+ Tambah item</button>

@push('scripts')
<script>
(function () {
    const products = @json($productData);
    const mode = @json($mode);
    const oldItems = Object.values(@json(old('items', [])));
    const body = document.getElementById('items-body');
    const totalEl = document.getElementById('grand-total');
    let index = 0;

    const rupiah = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
    const findProduct = id => products.find(p => String(p.id) === String(id));

    function recalc() {
        let grand = 0;
        body.querySelectorAll('tr').forEach(tr => {
            tr.update();
            grand += tr.subtotal;
        });
        totalEl.textContent = rupiah(grand);
    }

    function addRow(item) {
        item = item || {};
        const i = index++;
        const tr = document.createElement('tr');

        // Produk (dibuat dengan DOM API, bukan innerHTML, agar nama produk tidak diinterpretasi sebagai HTML)
        const tdProduct = document.createElement('td');
        const select = document.createElement('select');
        select.name = 'items[' + i + '][product_id]';
        select.className = 'form-select';
        select.required = true;
        select.add(new Option('-- pilih produk --', ''));
        products.forEach(p => select.add(new Option(p.label, p.id)));
        if (item.product_id) select.value = item.product_id;
        tdProduct.appendChild(select);

        // Quantity
        const tdQty = document.createElement('td');
        const qty = document.createElement('input');
        qty.type = 'number'; qty.min = 1; qty.step = 1; qty.required = true;
        qty.name = 'items[' + i + '][quantity]';
        qty.className = 'form-control';
        qty.value = item.quantity || 1;
        tdQty.appendChild(qty);

        // Harga: pembelian = input; penjualan = teks (harga diambil server dari database)
        const tdPrice = document.createElement('td');
        let priceInput = null;
        const priceText = document.createElement('span');
        if (mode === 'purchase') {
            priceInput = document.createElement('input');
            priceInput.type = 'number'; priceInput.min = 0; priceInput.step = '0.01'; priceInput.required = true;
            priceInput.name = 'items[' + i + '][unit_cost]';
            priceInput.className = 'form-control';
            if (item.unit_cost !== undefined) priceInput.value = item.unit_cost;
            tdPrice.appendChild(priceInput);
        } else {
            tdPrice.appendChild(priceText);
        }

        const tdSub = document.createElement('td');
        tdSub.className = 'text-end';

        const tdDel = document.createElement('td');
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-outline-danger';
        del.textContent = '\u00d7';
        del.addEventListener('click', () => { tr.remove(); recalc(); });
        tdDel.appendChild(del);

        const unitPrice = () => {
            if (priceInput) return parseFloat(priceInput.value) || 0;
            const p = findProduct(select.value);
            return p ? p.price : 0;
        };

        tr.subtotal = 0;
        tr.update = function () {
            const price = unitPrice();
            if (!priceInput) priceText.textContent = rupiah(price);
            tr.subtotal = (parseFloat(qty.value) || 0) * price;
            tdSub.textContent = rupiah(tr.subtotal);
        };

        select.addEventListener('change', () => {
            const p = findProduct(select.value);
            if (priceInput && p) priceInput.value = p.price;   // isi awal harga beli
            recalc();
        });
        qty.addEventListener('input', recalc);
        if (priceInput) priceInput.addEventListener('input', recalc);

        [tdProduct, tdQty, tdPrice, tdSub, tdDel].forEach(td => tr.appendChild(td));
        body.appendChild(tr);
        recalc();
    }

    document.getElementById('add-row').addEventListener('click', () => addRow());

    if (oldItems.length) {
        oldItems.forEach(addRow);   // pulihkan input setelah validasi gagal
    } else {
        addRow();
    }
})();
</script>
@endpush
