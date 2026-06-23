<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm border-collapse">
        @php
    $distribusi = $detail->first()?->distribusi ?? null;

    // ✅ hitung jumlah retur per produk_id
    $returPerProduk = collect();
    if ($distribusi) {
        $returPerProduk = $distribusi->retur
            ->whereNull('deleted_at')
            ->flatMap(fn($r) => $r->detail)
            ->groupBy('produk_id')
            ->map(fn($items) => $items->sum('jumlah'));
    }

    $totalRetur = 0;
@endphp

<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <th class="text-left px-4 py-2">Varian</th>
                <th class="text-center px-4 py-2">Qty</th>
                <th class="text-right px-4 py-2">Harga Satuan</th>
                <th class="text-center px-4 py-2 text-red-500">Retur</th>
                <th class="text-right px-4 py-2">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($detail as $item)
                @php
                    $jumlahRetur = $returPerProduk->get($item->produk_id, 0);
                    $subtotalRetur = $jumlahRetur * $item->harga;
                    $totalRetur += $subtotalRetur;
                @endphp
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="px-4 py-2">{{ $item->produk?->nama_produk ?? '-' }}</td>
                    <td class="text-center px-4 py-2">{{ $item->jumlah }}</td>
                    <td class="text-right px-4 py-2">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-center px-4 py-2">
                        @if ($jumlahRetur > 0)
                            <span class="text-red-500 font-medium">{{ $jumlahRetur }}</span>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="text-right px-4 py-2">
                        @if ($jumlahRetur > 0)
                            {{-- ✅ subtotal setelah dikurangi retur --}}
                            <span class="line-through text-gray-400 text-xs">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                            <br>
                            <span class="font-semibold">
                                Rp {{ number_format($item->subtotal - $subtotalRetur, 0, ',', '.') }}
                            </span>
                        @else
                        <span class="font-semibold">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t border-gray-200 dark:border-gray-700">
            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                <td colspan="4" class="text-right px-4 py-2">Total</td>
                <td class="text-right px-4 py-2">
                    Rp {{ number_format(max(0, $detail->sum('subtotal') - $totalRetur), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
