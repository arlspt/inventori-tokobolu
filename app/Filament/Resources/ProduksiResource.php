<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduksiResource\Pages;
use App\Models\Produksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ActionGroup;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;
    protected static ?int $navigationSort = 2; // Urutan 2 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $pluralModelLabel = 'Produksi';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ===== SECTION DATA =====
                Section::make('Data Produksi')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('tanggal')
                            ->label('Tanggal Produksi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ]),

                // ===== SECTION DETAIL =====
                Section::make('Detail Produksi')
                    ->schema([
                        Repeater::make('produksiDetail')
                            ->relationship()
                            ->label('Daftar Produksi')
                            ->addActionLabel('Tambah Produk') // Ubah label tombol "Add Item" menjadi "Tambah Produk"
                            ->columns(3)
                            ->schema([
                                // PRODUK + TAMBAH LANGSUNG
                                Select::make('produk_id')
                                    ->label('Produk')
                                    ->placeholder('Pilih Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // default jumlah = 1
                                        if (!$get('jumlah_produksi')) {
                                            $set('jumlah_produksi', 1);
                                        }
                                    })
                                    // ->suffixAction(
                                    //     \Filament\Forms\Components\Actions\Action::make('resep')
                                    //         ->icon('heroicon-o-plus')
                                    //         ->tooltip('Tambah Produk')
                                    //         ->url('/admin/produks/create')
                                    // )
                                    ->suffixAction(
                                        Action::make('kelolaProduk')
                                            ->label('Kelola Produk')
                                            ->icon('heroicon-o-cog-6-tooth')
                                            ->modalHeading('Kelola Produk')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->form([

                                                // MODE
                                                Select::make('mode')
                                                    ->label('Aksi')
                                                    ->options([
                                                        'tambah' => 'Tambah Produk',
                                                        'edit' => 'Ubah Produk',
                                                        'hapus' => 'Hapus Produk',
                                                    ])
                                                    ->placeholder('Pilih Aksi')
                                                    ->native(false)
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set) {
                                                        // 🔥 RESET SEMUA FIELD
                                                        $set('produk_id', null);
                                                        $set('nama_produk', null);
                                                        $set('harga', null);
                                                        if ($state === 'tambah') {
                                                            $set('resep', [
                                                                ['bahan_baku_id' => null, 'jumlah' => null]
                                                            ]);
                                                        } else {
                                                            $set('resep', []);
                                                        }
                                                    }),

                                                // PILIH PRODUK (hanya untuk edit & hapus)
                                                Select::make('produk_id')
                                                    ->label('Pilih Produk')
                                                    ->options(\App\Models\Produk::pluck('nama_produk', 'id'))
                                                    ->searchable()
                                                    ->placeholder('Pilih produk yang mau diubah')
                                                    ->visible(fn($get) => in_array($get('mode'), ['edit', 'hapus']))
                                                    ->required(fn($get) => in_array($get('mode'), ['edit', 'hapus']))
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, $set, $get) {

                                                        if (!$state || $get('mode') !== 'edit') return;

                                                        $produk = \App\Models\Produk::with('resep')->find($state);
                                                        if (!$produk) return;

                                                        // isi field
                                                        $set('nama_produk', $produk->nama_produk);
                                                        $set('harga', $produk->harga);

                                                        // isi repeater resep
                                                        $set('resep', $produk->resep->map(fn($r) => [
                                                            'bahan_baku_id' => $r->bahan_baku_id,
                                                            'jumlah' => $r->jumlah,
                                                        ])->toArray());
                                                    }),

                                                // NAMA PRODUK
                                                TextInput::make('nama_produk')
                                                    ->label('Nama Produk')
                                                    ->visible(fn($get) => $get('mode') !== 'hapus')
                                                    ->visible(fn($get) => $get('mode') !== 'edit')
                                                    ->required(fn($get) => $get('mode') !== 'hapus')
                                                    ->afterStateHydrated(function ($set, $get) {
                                                        if ($get('produk_id')) {
                                                            $produk = \App\Models\Produk::find($get('produk_id'));
                                                            $set('nama_produk', $produk?->nama_produk);
                                                        }
                                                    }),

                                                // HARGA
                                                TextInput::make('harga')
                                                    ->numeric()
                                                    ->visible(fn($get) => $get('mode') !== 'hapus')
                                                    ->required(fn($get) => $get('mode') !== 'hapus')
                                                    ->afterStateHydrated(function ($set, $get) {
                                                        if ($get('produk_id')) {
                                                            $produk = \App\Models\Produk::find($get('produk_id'));
                                                            $set('harga', $produk?->harga);
                                                        }
                                                    }),

                                                // RESEP
                                                Repeater::make('resep')
                                                    ->visible(fn($get) => $get('mode') !== 'hapus')
                                                    ->required()
                                                    ->reorderable(false)
                                                    ->schema([
                                                        Select::make('bahan_baku_id')
                                                            ->label('Bahan Baku')
                                                            ->options(\App\Models\BahanBaku::pluck('nama_bahan', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->live()
                                                            ->placeholder('Pilih Bahan Baku')
                                                            ->native(false),

                                                        TextInput::make('jumlah')
                                                            ->numeric()
                                                            ->required()
                                                            ->reactive()
                                                            ->label(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                                                'ml' => 'Jumlah (ml)',
                                                                default => 'Jumlah (gram)',
                                                            })
                                                            ->suffix(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                                                'ml' => 'ml',
                                                                default => 'gr',
                                                            }),
                                                    ])
                                                    ->afterStateHydrated(function ($set, $get) {

                                                        if ($get('produk_id')) {
                                                            $produk = \App\Models\Produk::with('resep')->find($get('produk_id'));

                                                            $set('resep', $produk?->resep->map(fn($r) => [
                                                                'bahan_baku_id' => $r->bahan_baku_id,
                                                                'jumlah' => $r->jumlah,
                                                            ])->toArray());
                                                        }
                                                    })->columns(2),

                                            ])
                                            ->action(function ($data) {

                                                // 🔥 TAMBAH
                                                if ($data['mode'] === 'tambah') {

                                                    $produk = \App\Models\Produk::create([
                                                        'nama_produk' => $data['nama_produk'],
                                                        'harga' => $data['harga'],
                                                    ]);

                                                    foreach ($data['resep'] as $item) {
                                                        \App\Models\Resep::create([
                                                            'produk_id' => $produk->id,
                                                            'bahan_baku_id' => $item['bahan_baku_id'],
                                                            'jumlah' => $item['jumlah'],
                                                        ]);
                                                    }
                                                }

                                                // 🔥 EDIT
                                                if ($data['mode'] === 'edit') {

                                                    $produk = \App\Models\Produk::find($data['produk_id']);
                                                    if (!$produk) return;

                                                    $produk->update([
                                                        'nama_produk' => $data['nama_produk'],
                                                        'harga' => $data['harga'],
                                                    ]);

                                                    \App\Models\Resep::where('produk_id', $produk->id)->delete();

                                                    foreach ($data['resep'] as $item) {
                                                        \App\Models\Resep::create([
                                                            'produk_id' => $produk->id,
                                                            'bahan_baku_id' => $item['bahan_baku_id'],
                                                            'jumlah' => $item['jumlah'],
                                                        ]);
                                                    }
                                                }

                                                // 🔥 HAPUS
                                                if ($data['mode'] === 'hapus') {

                                                    $produk = \App\Models\Produk::find($data['produk_id']);
                                                    if (!$produk) return;

                                                    \App\Models\Resep::where('produk_id', $produk->id)->delete();
                                                    $produk->delete();
                                                }
                                            })
                                    ),

                                // 🔥 JUMLAH
                                TextInput::make('jumlah_produksi')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(),

                                // 🔥 GAGAL
                                TextInput::make('gagal')
                                    ->label('Produk Gagal')
                                    ->numeric()
                                    ->default(0),

                            ])
                            ->reorderable(false)
                            ->itemLabel(fn() => 'Produk'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // menonaktifkan klik pada baris untuk melihat detail
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Hari, Tanggal')
                    ->formatStateUsing(function ($state) {
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y');
                    }),

                TextColumn::make('user_id')
                    ->label('Dibuat Oleh')
                    ->getStateUsing(function ($record) {
                        return $record->user ? $record->user->name : '-';
                    }),

                TextColumn::make('produksiDetail')
                    ->label('Jumlah Produk')
                    ->getStateUsing(function ($record) {

                        return $record->produksiDetail->sum(function ($item) {
                            return ($item->jumlah_produksi ?? 0) - ($item->gagal ?? 0);
                        });
                    }),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->label('Dibuat Pada'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // ViewAction::make('view')
                //     ->modalHeading('Detail Produksi')
                //     ->label('Detail')
                //     ->color('info')
                //     ->hiddenLabel() // atau ->label('')
                //     ->infolist([
                //         RepeatableEntry::make('produksiDetail')
                //             ->label(fn($record) => Carbon::parse($record->tanggal)
                //                 ->locale('id')
                //                 ->translatedFormat('l, d F Y'))
                //             ->schema([
                //                 TextEntry::make('produk.nama_produk')
                //                     ->label('Produk'),
                //                 TextEntry::make('jumlah_produksi')
                //                     ->label('Jumlah'),
                //                 TextEntry::make('gagal')
                //                     ->label('Produk Gagal'),
                //             ])
                //             ->columns(3),
                //     ]),
                ActionGroup::make([
                    ViewAction::make('view')
                        ->modalHeading('Detail Produksi')
                        ->label('Detail')
                        ->color('info')
                        ->infolist([
                            RepeatableEntry::make('produksiDetail')
                                ->label(fn($record) => Carbon::parse($record->tanggal)
                                    ->locale('id')
                                    ->translatedFormat('l, d F Y'))
                                ->schema([
                                    TextEntry::make('produk.nama_produk')
                                        ->label('Produk'),
                                    TextEntry::make('jumlah_produksi')
                                        ->label('Jumlah'),
                                    TextEntry::make('gagal')
                                        ->label('Produk Gagal'),
                                ])
                                ->columns(3),
                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])->color('black'), //ubah warna burger menu aksi
            ])
            ->actionsColumnLabel('Aksi')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduksis::route('/'),
            'create' => Pages\CreateProduksi::route('/create'),
            'edit' => Pages\EditProduksi::route('/{record}/edit'),
        ];
    }

    public static function isNavigationItemActive(): bool
    {
        $routeName = request()->route()?->getName();

        return str_contains($routeName, 'produks') // route produksi
            || str_contains($routeName, 'produks'); // route produk (ganti sesuai nama)
    }
}
