<x-filament-widgets::widget>

<x-filament::section>

<div class="flex gap-2">

    {{-- HARIAN --}}
    <x-filament::button
        color="{{ $filterMode === 'harian'
            ? 'primary'
            : 'gray' }}"
        wire:click="setMode('harian')"
    >
        Hari Ini
    </x-filament::button>


    {{-- MINGGUAN --}}
    <x-filament::dropdown>

        <x-slot name="trigger">

            <x-filament::button
                color="{{ $filterMode === 'mingguan'
                    ? 'primary'
                    : 'gray' }}"
            >
                Minggu
                {{ $minggu }}
            </x-filament::button>

        </x-slot>

        <div class="p-3 w-48">

            <div class="text-sm mb-2">
                Pilih Minggu
            </div>

            <select
                wire:model.live="minggu"
                class="w-full rounded-lg"
            >
                @foreach([1,2,3,4] as $m)
                    <option value="{{ $m }}">
                        Minggu {{ $m }}
                    </option>
                @endforeach
            </select>

            <x-filament::button
                size="sm"
                class="mt-3 w-full"
                wire:click="applyMinggu"
            >
                Terapkan
            </x-filament::button>

        </div>

    </x-filament::dropdown>


    {{-- BULANAN --}}
    <x-filament::dropdown>

        <x-slot name="trigger">

            <x-filament::button
                color="{{ $filterMode === 'bulanan'
                    ? 'primary'
                    : 'gray' }}"
            >
            {{-- Bulanan --}}
                {{ \Carbon\Carbon::create()
                    ->month($bulan)
                    ->locale('id')
                    ->translatedFormat('F') }}
            </x-filament::button>

        </x-slot>

        <div class="p-3 w-56">

            <div class="text-sm mb-2">
                Pilih Bulan
            </div>

            <select
                wire:model.live="bulan"
                class="w-full rounded-lg"
            >
                @foreach(range(1,12) as $b)

                    <option value="{{ $b }}">
                        {{
                            \Carbon\Carbon::create()
                                ->month($b)
                                ->locale('id')
                                ->translatedFormat('F')
                        }}

                    </option>

                @endforeach

            </select>

            <x-filament::button
                size="sm"
                class="mt-3 w-full"
                wire:click="applyBulan"
            >
                Terapkan
            </x-filament::button>

        </div>

    </x-filament::dropdown>

</div>

</x-filament::section>

</x-filament-widgets::widget>
