<x-filament-widgets::widget>
    <x-filament::section>
    <div class="flex gap-2">

        <x-filament::button
            color="{{ $filter === 'hari_ini' ? 'primary' : 'gray' }}"
            wire:click="setFilter('hari_ini')"
        >
            Hari Ini
        </x-filament::button>

        <x-filament::button
            color="{{ $filter === 'minggu_ini' ? 'primary' : 'gray' }}"
            wire:click="setFilter('minggu_ini')"
        >
            Mingguan
        </x-filament::button>

        <x-filament::button
            color="{{ $filter === 'bulan_ini' ? 'primary' : 'gray' }}"
            wire:click="setFilter('bulan_ini')"
        >
            Bulanan
        </x-filament::button>

    </div>
</x-filament::section>
</x-filament-widgets::widget>
