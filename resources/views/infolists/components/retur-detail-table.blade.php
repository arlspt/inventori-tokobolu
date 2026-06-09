<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <th class="text-left px-4 py-2">Varian</th>
                <th class="text-center px-4 py-2">Jumlah Retur</th>
                <th class="text-left px-4 py-2">Alasan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($detail as $item)
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="px-4 py-2">{{ $item->produk?->nama_produk ?? '-' }}</td>
                    <td class="text-center px-4 py-2">{{ $item->jumlah }}</td>
                    <td class="px-4 py-2">
                        {{ match($item->alasan) {
                            'rusak'       => 'Barang Rusak',
                            'expired'     => 'Expired',
                            'salah_kirim' => 'Salah Kirim',
                            'lainnya'     => $item->alasan_lain ?? 'Lainnya',
                            default       => $item->alasan ?? '-',
                        } }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t border-gray-200 dark:border-gray-700">
            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                <td class="text-right px-4 py-2">Total</td>
                <td class="text-center px-4 py-2">{{ $detail->sum('jumlah') }}</td>
                <td class="px-4 py-2"></td>
            </tr>
        </tfoot>
    </table>
</div>
