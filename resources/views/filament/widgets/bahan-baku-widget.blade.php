<x-filament-widgets::widget>
    <x-filament::section>

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Stok Bahan Baku
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Saat ini</p>
            </div>

            {{-- DROPDOWN HISTORY --}}
            <div class="flex items-center gap-2">
                <select
    wire:model.live="bulanHistory"
    class="text-xs border border-gray-200 rounded-lg pl-3 pr-10 py-2 bg-white dark:bg-gray-900 dark:border-emerald-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:outline-none min-w-[220px]"
>
                @foreach ($this->getBulanOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            </div>
        </div>

        {{-- TABEL RINGKAS (5 baris teratas) --}}
        <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-700">
            <table class="w-full text-xs">
                <thead>
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                        <th class="text-left px-3 py-2 text-gray-500 dark:text-gray-200 font-semibold">Bahan</th>
                        <th class="text-right px-3 py-2 text-gray-500 dark:text-gray-200 font-semibold">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">
                    @forelse ($this->getStokHistory()->take(5) as $bahan)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200 flex items-center gap-1.5">
                                @if ($bahan['low'])
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-400 flex-shrink-0"></span>
                                @else
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-400 flex-shrink-0"></span>
                                @endif
                                {{ $bahan['nama'] }}
                            </td>
                            <td class="px-3 py-2 text-right font-medium
                                {{ $bahan['low'] ? 'text-red-500' : 'text-gray-800 dark:text-gray-200' }}">
                                {{ $bahan['stok_label'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-4 text-center text-gray-400 text-xs">
                                Belum ada data bahan baku
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOMBOL LIHAT SEMUA --}}
        @if ($this->getStokHistory()->count() > 5)
            <button
                wire:click="openModal"
                class="mt-3 w-full text-xs text-amber-600 hover:text-amber-700 font-medium py-1.5 rounded-lg border border-amber-200 hover:border-amber-300 hover:bg-amber-50 transition-all dark:hover:bg-white/5"
            >
                Lihat Semua ({{ $this->getStokHistory()->count() }} bahan)
            </button>
        @endif

    </x-filament::section>

    {{-- MODAL POPUP DETAIL LENGKAP --}}
@if ($showModal)
    {{-- BACKDROP --}}
    <div
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
        wire:click="closeModal"
    ></div>

    {{-- MODAL CONTENT --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
        <div class="
            pointer-events-auto
            w-full
            max-w-2xl
            max-h-[85vh]
            flex flex-col
            rounded-lg
            shadow-2xl
            bg-white
            dark:bg-gray-900
            border border-gray-200
            dark:border-gray-700
        ">
            {{-- MODAL HEADER --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Stok Bahan Baku — Lengkap
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ collect($this->getBulanOptions())->get($bulanHistory, 'Bulan ini') }}
                    </p>
                </div>
                <button
                    wire:click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- MODAL BODY --}}
            <div class="overflow-y-auto flex-1 p-6">
                <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left px-4 py-2.5 text-gray-500 dark:text-gray-200 font-semibold">Bahan Baku</th>
                                <th class="text-right px-4 py-2.5 text-gray-500 dark:text-gray-200 font-semibold">Stok</th>
                                <th class="text-center px-4 py-2.5 text-gray-500 dark:text-gray-200 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($this->getStokHistory() as $bahan)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-200">
                                        {{ $bahan['nama'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold
                                        {{ $bahan['low'] ? 'text-red-500' : 'text-gray-800 dark:text-gray-200' }}">
                                        {{ $bahan['stok_label'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($bahan['low'])
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                                Menipis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end bg-gray-50/50 dark:bg-gray-800/50 rounded-b-2xl">
                <button
                    wire:click="closeModal"
                    class="text-xs px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-200 rounded-lg transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif

</x-filament-widgets::widget>
