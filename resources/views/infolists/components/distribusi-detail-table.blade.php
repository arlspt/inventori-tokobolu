<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <th class="text-left px-4 py-2">Varian</th>
                <th class="text-center px-4 py-2">Jumlah</th>
                <th class="text-right px-4 py-2">Harga</th>
                <th class="text-right px-4 py-2">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($detail as $item)
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="px-4 py-2">{{ $item->produk?->nama_produk ?? '-' }}</td>
                    <td class="text-center px-4 py-2">{{ $item->jumlah }}</td>
                    <td class="text-right px-4 py-2">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right px-4 py-2">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t border-gray-200 dark:border-gray-700">
            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                <td colspan="3" class="text-right px-4 py-2">Total</td>
                <td class="text-right px-4 py-2">
                    Rp {{ number_format($detail->sum('subtotal'), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
