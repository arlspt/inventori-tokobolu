<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengadaanResource\Pages;
use App\Models\Pengadaan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Carbon\Carbon;

class PengadaanResource extends Resource
{
    protected static ?string $model = Pengadaan::class;
    protected static ?int $navigationSort = 1; // Urutan 1 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Pengadaan Bahan Baku';
    protected static ?string $modelLabel = 'Pengadaan';
    protected static ?string $pluralModelLabel = 'Pengadaan Bahan Baku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Data Pengadaan')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Supplier')
                            ->required()

                            // FORM TAMBAH SUPPLIER
                            ->createOptionForm([
                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->required(),

                                TextInput::make('telepon')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required(),

                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->required(),
                            ])

                            // CUSTOM BUTTON
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label('Tambah Supplier')
                                    ->modalHeading('Tambah Supplier Baru')
                                    ->modalSubmitActionLabel('Simpan')
                                    ->modalCancelActionLabel('Batal');
                            }),

                    ]),
                Section::make('Detail Bahan')
                    ->schema([
                        Repeater::make('pengadaanDetail')
                            ->relationship()
                            ->columns(4)
                            ->deleteAction(
                                fn($action) => $action
                                    ->label('Hapus')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')

                                    ->before(function ($record) {

                                        if (!$record) return;

                                        $bahan = \App\Models\BahanBaku::find($record->bahan_baku_id);

                                        if ($bahan) {
                                            $bahan->decrement('stok', $record->jumlah);
                                        }
                                    })
                            )
                            ->schema([
                                Select::make('bahan_baku_id')
                                    ->label('Bahan Baku')
                                    ->placeholder('Pilih Bahan Baku')
                                    ->relationship('bahanBaku', 'nama_bahan')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->createOptionForm([
                                        TextInput::make('nama_bahan')
                                            ->label('Nama bahan')
                                            ->required(),
                                        Select::make('satuan')
                                            ->label('Satuan')
                                            ->placeholder('Pilih Satuan')
                                            ->options([
                                                'gram' => 'Gram (Padat)',
                                                'ml' => 'Mililiter (Cair)',
                                            ])
                                            ->required(),

                                        TextInput::make('stok')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated()
                                            ->hidden(),
                                    ])
                                    ->createOptionAction(function ($action) { //Mengubah button simpan
                                        return $action
                                            ->label('Tambah Bahan')
                                            ->modalHeading('Tambah Bahan Baku')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    })
                                    ->required(),

                                TextInput::make('jumlah')
                                    ->label(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                        'ml' => 'Jumlah / ml',
                                        default => 'Jumlah / gram',
                                    })
                                    ->suffix(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                        'ml' => 'L',
                                        default => 'Kg',
                                    })
                                    ->numeric()
                                    ->step(0.1) // biar bisa 0.8
                                    ->required()
                                    ->live() // WAJIB
                                    // 🔥 SAAT LOAD (Edit/View)
                                    ->afterStateHydrated(function ($state, $set) {
                                        if ($state !== null) {
                                            $set('jumlah', $state / 1000);
                                        }
                                    })
                                    // 🔥 SAAT SIMPAN
                                    ->dehydrateStateUsing(fn($state) => (int) round($state * 1000))

                                    ->afterStateUpdated(function ($get, $set) {
                                        $harga     = (float) str_replace(['.', ','], ['', '.'], $get('harga') ?? 0);
                                        $jumlahKg  = (float) ($get('jumlah') ?? 0);
                                        $jumlahGram = $jumlahKg * 1000;
                                        $set('subtotal', $harga * $jumlahGram);
                                    }),


                                TextInput::make('harga')
                                    ->label(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                        'ml' => 'Harga / ml',
                                        default => 'Harga / gram',
                                    })
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($get, $set) {
                                        $harga      = (float) str_replace(['.', ','], ['', '.'], $get('harga') ?? 0);
                                        $jumlahKg   = (float) ($get('jumlah') ?? 0);
                                        $jumlahGram = $jumlahKg * 1000;
                                        $set('subtotal', $harga * $jumlahGram);
                                    })
                                    ->formatStateUsing(
                                        fn($state) =>
                                        number_format($state, 0, ',', '.')
                                    ),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->dehydrated() // tetap disimpan ke DB
                                    ->prefix('Rp.')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        number_format($state, 0, ',', '.')
                                    )
                                    ->afterStateUpdated(function ($get, $set) {
                                        // ✅ bersihkan format angka sebelum kalkulasi
                                        $harga      = (float) str_replace(['.', ','], ['', '.'], $get('harga') ?? 0);
                                        $jumlahKg   = (float) ($get('jumlah') ?? 0);
                                        $jumlahGram = $jumlahKg * 1000;
                                        $set('subtotal', $harga * $jumlahGram);
                                    }),

                            ])
                            ->addActionLabel('Tambah Pengadaan Detail')
                            ->addAction(
                                fn($action) => $action
                                    ->label('Pengadaan Detail')
                                    ->icon('heroicon-m-plus') // menambahkan icon di button
                            )
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(['pengadaanDetail.bahanBaku', 'supplier', 'user'])
                    ->orderBy('tanggal', 'desc')        // terbaru di atas
                    ->orderBy('created_at', 'desc')     // tiebreaker
            )
            ->recordUrl(null)
            ->recordAction('view') // klik row -> modal

            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                    ),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier'),

                TextColumn::make('jumlah_item')
                    ->label('Jumlah Bahan')
                    ->getStateUsing(fn($record) => $record->pengadaanDetail->count()),

                TextColumn::make('total_harga')
                    ->label('Total')
                    ->getStateUsing(
                        fn($record) =>
                        'Rp. ' . number_format($record->pengadaanDetail->sum('subtotal'), 0, ',', '.')
                    ),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh'),
            ])

            ->actions([

                // DETAIL (dipanggil saat klik row)


                // DROPDOWN ACTION
                ActionGroup::make([
                    ViewAction::make('view')
                        ->modalHeading('Detail Pengadaan')
                        ->label('Detail')
                        ->color('info')
                        ->infolist([
                            // SECTION INFORMASI
                            InfoSection::make('Informasi Pengadaan')
                                ->schema([
                                    TextEntry::make('tanggal')
                                        ->label('Tanggal')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            Carbon::parse($state)
                                                ->locale('id')
                                                ->translatedFormat('l, d F Y')
                                        ),

                                    TextEntry::make('supplier.nama_supplier')
                                        ->label('Supplier'),

                                    TextEntry::make('user.name')
                                        ->label('Dibuat Oleh'),
                                ])
                                ->columns(3),

                            // SECTION DETAIL BAHAN
                            InfoSection::make('Detail Bahan')
                                ->schema([
                                    \Filament\Infolists\Components\View::make('infolists.components.pengadaan-detail-table')
                                        ->viewData(fn($record) => ['detail' => $record->pengadaanDetail->load('bahanBaku')])
                                ]),

                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Hapus Pengadaan Bahan Baku')
                        ->modalDescription('Tindakan ini akan mengurangi stok bahan baku yang dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->color('black'),
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
            'index' => Pages\ListPengadaans::route('/'),
            'create' => Pages\CreatePengadaan::route('/create'),
            'edit' => Pages\EditPengadaan::route('/{record}/edit'),
        ];
    }
}
