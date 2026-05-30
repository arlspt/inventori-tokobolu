<x-filament-widgets::widget>
    <x-filament::section>

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Rekap Bahan Baku Bulanan
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Penggunaan, pengadaan, dan total harga
                </p>
            </div>

            {{-- DROPDOWN BULAN --}}
            <select
                wire:model.live="bulanRecap"
                class="text-xs border border-gray-200 rounded-md px-2 py-1.5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 focus:ring-1 focus:ring-amber-400 focus:outline-none"
            >
                @foreach ($this->getBulanOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @php $recap = $this->getRecapData(); @endphp

        @if ($recap->isEmpty())
            <div class="text-center py-8 text-gray-400 text-xs">
                Tidak ada data penggunaan atau pengadaan di bulan ini
            </div>
        @else
            {{-- TABEL RECAP --}}
            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-700 mb-4">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="text-left px-4 py-2.5 text-gray-500 dark:text-gray-400 font-semibold">Bahan Baku</th>
                            <th class="text-right px-4 py-2.5 text-gray-500 dark:text-gray-400 font-semibold">Digunakan</th>
                            <th class="text-right px-4 py-2.5 text-gray-500 dark:text-gray-400 font-semibold">Masuk</th>
                            <th class="text-right px-4 py-2.5 text-gray-500 dark:text-gray-400 font-semibold">Tersedia</th>
                            <th class="text-right px-4 py-2.5 text-gray-500 dark:text-gray-400 font-semibold">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recap as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                                    {{ $item['nama'] }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-red-500 font-medium">
                                    {{ $item['penggunaan_label'] }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-green-600 dark:text-green-400 font-medium">
                                    @if ($item['pengadaan_raw'] > 0)
                                        +{{ $item['pengadaan_label'] }}
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300 font-semibold">
                                    {{ $item['stok_tersedia_label'] }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-800 dark:text-gray-200">
                                    Rp {{ number_format($item['total_harga'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- FOOTER: KETERANGAN + TOTAL --}}
            <div class="flex items-end justify-between">

                {{-- LEGENDA --}}
                <div class="flex gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-400"></span>
                        Digunakan produksi
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-400"></span>
                        Pengadaan masuk
                    </span>
                </div>

                {{-- TOTAL HARGA --}}
                <div class="text-right">
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">
                        Total Harga Penggunaan
                    </div>
                    <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        Rp {{ number_format($this->getTotalHarga(), 0, ',', '.') }}
                    </div>
                </div>

            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
