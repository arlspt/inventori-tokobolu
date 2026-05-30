<div class="overflow-hidden rounded-lg border border-gray-200">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 text-gray-700">
                <th class="text-left px-4 py-2">Varian</th>
                <th class="text-center px-4 py-2">Jumlah Produksi</th>
                <th class="text-center px-4 py-2">Gagal</th>
                <th class="text-center px-4 py-2">Berhasil</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($detail as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $item->produk?->nama_produk ?? '-' }}</td>
                    <td class="text-center px-4 py-2">{{ $item->jumlah_produksi }}</td>
                    <td class="text-center px-4 py-2">
                        @if ($item->gagal > 0)
                            <span class="text-red-500 font-medium">{{ $item->gagal }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="text-center px-4 py-2 font-semibold text-green-600">
                        {{ $item->jumlah_produksi - $item->gagal }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t border-gray-200">
            <tr class="bg-gray-50 font-semibold">
                <td class="text-right px-4 py-2">Total</td>
                <td class="text-center px-4 py-2">{{ $detail->sum('jumlah_produksi') }}</td>
                <td class="text-center px-4 py-2 text-red-500">
                    {{ $detail->sum('gagal') > 0 ? $detail->sum('gagal') : '—' }}
                </td>
                <td class="text-center px-4 py-2 text-green-600">
                    {{ $detail->sum(fn($i) => $i->jumlah_produksi - $i->gagal) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
