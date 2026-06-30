<div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <th class="text-left px-4 py-2">Varian</th>
                <th class="text-center px-4 py-2">Jumlah Produksi</th>
                <th class="text-center px-4 py-2">Gagal</th>
                <th class="text-center px-4 py-2">Berhasil</th>
                <th class="text-center px-4 py-2">Expired</th> {{-- ✅ tambahkan --}}
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @foreach ($detail as $item)
                @php
                    $isExpired = $item->expired_at && \Carbon\Carbon::parse($item->expired_at)->isPast();
                @endphp
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
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
                    {{-- ✅ kolom expired --}}
                    <td class="text-center px-4 py-2">
                        @if ($item->expired_at)
                            <span style="{{ $isExpired ? 'color:#ef4444; font-weight:600;' : 'color:#6b7280;' }}">
                                {{ \Carbon\Carbon::parse($item->expired_at)->locale('id')->translatedFormat('d M Y') }}
                            </span>
                            @if ($isExpired)
                                <span style="display:inline-block; margin-left:4px; padding:1px 6px; border-radius:999px; font-size:10px; font-weight:600; background:#fee2e2; color:#dc2626;">
                                    Expired
                                </span>
                            @endif
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t border-gray-200 dark:border-gray-700">
            <tr class="bg-gray-50 dark:bg-gray-800 font-semibold">
                <td class="text-right px-4 py-2">Total</td>
                <td class="text-center px-4 py-2">{{ $detail->sum('jumlah_produksi') }}</td>
                <td class="text-center px-4 py-2 text-red-500">
                    {{ $detail->sum('gagal') > 0 ? $detail->sum('gagal') : '—' }}
                </td>
                <td class="text-center px-4 py-2 text-green-600">
                    {{ $detail->sum(fn($i) => $i->jumlah_produksi - $i->gagal) }}
                </td>
                <td class="text-center px-4 py-2 text-gray-400 text-xs">—</td>
            </tr>
        </tfoot>
    </table>
</div>
