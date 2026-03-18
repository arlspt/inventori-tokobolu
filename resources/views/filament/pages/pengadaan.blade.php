<x-filament-panels::page>

    <h2 class="text-xl font-bold mb-4">
        Form Pengadaan Bahan Baku
    </h2>

    {{-- FORM --}}
    {{ $this->form }}

    <br><br>

    <h2 class="text-xl font-bold mt-10 mb-4">
        Data Pengadaan
    </h2>

    {{-- TABLE MANUAL --}}
    <div class="overflow-x-auto">
        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Kode</th>
                    <th class="p-2 border">Supplier</th>
                    <th class="p-2 border">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getPengadaanData() as $item)
                    <tr>
                        <td class="p-2 border">{{ $item->kode_pengadaan }}</td>
                        <td class="p-2 border">{{ $item->supplier->nama_supplier }}</td>
                        <td class="p-2 border">{{ $item->tanggal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-filament-panels::page>
