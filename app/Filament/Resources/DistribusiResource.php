<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistribusiResource\Pages;
// use App\Filament\Resources\DistribusiResource\RelationManagers;
use App\Models\Distribusi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;

class DistribusiResource extends Resource
{
    protected static ?string $model = Distribusi::class;
    protected static ?int $navigationSort = 3; // Urutan 3 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';
    protected static ?string $navigationLabel = 'Distribusi';
    protected static ?string $pluralModelLabel = 'Distribusi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION DATA UTAMA
                Section::make('Data Distribusi')
                    ->columns(2)
                    ->schema([

                        Grid::make(1)
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Hidden::make('user_id')
                                    ->default(fn() => Auth::id()),

                                Select::make('reseller_id')
                                    ->label('Reseller')
                                    ->relationship('reseller', 'nama_reseller')
                                    ->searchable()
                                    ->placeholder('Pilih Reseller')
                                    ->preload()
                                    ->reactive()
                                    ->createOptionForm([
                                        TextInput::make('nama_reseller')
                                            ->label('Nama Reseller')
                                            ->required(),

                                        TextInput::make('alamat')
                                            ->label('Alamat')
                                            ->required(),

                                        TextInput::make('no_telp')
                                            ->label('No. Telepon')
                                            ->tel()
                                            ->required(),
                                    ])
                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->label('Tambah Reseller')
                                            ->modalHeading('Tambah Reseller')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    })
                                    ->requiredWithout('tujuan_lain')
                                    ->dehydrated(),
                                TextInput::make('tujuan_lain')
                                    ->label('Tujuan Lain')
                                    ->placeholder('Isi jika bukan reseller')
                                    ->requiredWithout('reseller_id')
                                    ->visible(fn($get) => !$get('reseller_id'))
                                    ->dehydrated(),
                            ])
                            ->columnSpan(1),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Masukkan keterangan tambahan (opsional)')
                            ->rows(8)
                            ->columnSpan(1),
                    ]),

                // SECTION DETAIL (INI REPEATER)
                Section::make('Detail Produk')
                    ->schema([

                        Repeater::make('detail') // nama relasi
                            ->relationship()
                            ->columns(2)
                            ->schema([

                                Select::make('produk_id')
                                    ->label('Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->placeholder('Pilih Produk')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // set default jumlah = 1
                                        $set('jumlah', 1);

                                        // ambil harga dari produk
                                        $produk = \App\Models\Produk::find($state);
                                        if ($produk) {
                                            $set('harga', $produk->harga);
                                            $set('subtotal', $produk->harga * 1);
                                        }
                                    }),
                                TextInput::make('harga')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated(), // tetap disimpan ke DB
                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $harga = $get('harga') ?? 0;
                                        $set('subtotal', $harga * $state);
                                    }),
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated()
                            ])
                            ->addAction(
                                fn($action) =>
                                $action
                                    ->label('Tambah Produk')
                                    ->icon('heroicon-m-plus')
                            )
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->with(['detail.produk', 'reseller', 'user'])
            )

            ->recordUrl(null) // penting
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

                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->getStateUsing(
                        fn($record) =>
                        $record->reseller
                            ? $record->reseller->nama_reseller
                            : $record->tujuan_lain
                    ),

                TextColumn::make('detail_count')
                    ->counts('detail')
                    ->label('Jumlah Item'),

                TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(
                        fn($record) =>
                        $record->detail->sum('subtotal')
                    )
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format($state, 0, ',', '.')
                    ),
            ])

            ->actions([
                // DROPDOWN MENU
                ActionGroup::make([
                    // DETAIL (dipanggil saat klik row & dropdown)
                    ViewAction::make('view')
                        ->label('Detail')
                        ->modalHeading('Detail Distribusi')
                        ->color('info')
                        ->modalFooterActions([
                            Action::make('retur')
                                ->label(
                                    fn($record) =>
                                    \App\Models\Retur::where('distribusi_id', $record->id)
                                        ->whereNull('deleted_at')
                                        ->exists()
                                        ? 'Ubah Retur'
                                        : 'Buat Retur'
                                )
                                ->icon('heroicon-o-arrow-uturn-left')
                                ->color('warning')
                                ->action(function ($record) {
                                    $retur = \App\Models\Retur::where('distribusi_id', $record->id)
                                        ->whereNull('deleted_at')
                                        ->first();
                                    // kalau sudah ada -> edit
                                    if ($retur) {
                                        return redirect()->to(
                                            route('filament.admin.resources.returs.edit', [
                                                'record' => $retur->id
                                            ])
                                        );
                                    }
                                    // kalau belum ada -> create
                                    return redirect()->to(
                                        route('filament.admin.resources.returs.create', [
                                            'distribusi_id' => $record->id
                                        ])
                                    );
                                })
                        ])
                        ->infolist([

                            // SECTION DATA DISTRIBUSI
                            InfoSection::make('Data Distribusi')
                                ->schema([
                                    TextEntry::make('tanggal')
                                        ->label('Tanggal')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            Carbon::parse($state)
                                                ->locale('id')
                                                ->translatedFormat('l, d F Y')
                                        ),

                                    TextEntry::make('tujuan')
                                        ->label('Tujuan')
                                        ->getStateUsing(
                                            fn($record) =>
                                            $record->reseller
                                                ? $record->reseller->nama_reseller
                                                : $record->tujuan_lain
                                        ),

                                    TextEntry::make('keterangan')
                                        ->label('Keterangan')
                                        ->placeholder('-'),
                                ])
                                ->columns(3),

                            // SECTION DETAIL PRODUK
                            InfoSection::make('Detail Produk')
                                ->schema([
                                    RepeatableEntry::make('detail')
                                        ->schema([
                                            TextEntry::make('produk.nama_produk')
                                                ->label('Produk'),

                                            TextEntry::make('jumlah')
                                                ->label('Jumlah'),

                                            TextEntry::make('harga')
                                                ->label('Harga')
                                                ->formatStateUsing(
                                                    fn($state) =>
                                                    'Rp ' . number_format($state, 0, ',', '.')
                                                ),

                                            TextEntry::make('subtotal')
                                                ->label('Subtotal')
                                                ->formatStateUsing(
                                                    fn($state) =>
                                                    'Rp ' . number_format($state, 0, ',', '.')
                                                ),
                                        ])
                                        ->columns(4),
                                ]),

                            // SECTION TOTAL
                            InfoSection::make('Total')
                                ->schema([
                                    TextEntry::make('total')
                                        ->label('Total Keseluruhan')
                                        ->getStateUsing(
                                            fn($record) =>
                                            'Rp ' . number_format(
                                                $record->detail->sum('subtotal'),
                                                0,
                                                ',',
                                                '.'
                                            )
                                        )
                                        ->weight('bold'),
                                ]),
                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                    Action::make('retur')
                        ->label(
                            fn($record) =>
                            \App\Models\Retur::where('distribusi_id', $record->id)
                                ->whereNull('deleted_at')
                                ->exists()
                                ? 'Ubah Retur'
                                : 'Buat Retur'
                        )->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->url(function ($record) {

                            $retur = \App\Models\Retur::where('distribusi_id', $record->id)
                                ->whereNull('deleted_at')
                                ->first();

                            // kalau sudah ada -> edit
                            if ($retur) {
                                return route('filament.admin.resources.returs.edit', [
                                    'record' => $retur->id
                                ]);
                            }

                            // kalau belum ada -> create
                            return route('filament.admin.resources.returs.create', [
                                'distribusi_id' => $record->id
                            ]);
                        })
                ])->color('black'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistribusis::route('/'),
            'create' => Pages\CreateDistribusi::route('/create'),
            'edit' => Pages\EditDistribusi::route('/{record}/edit'),
        ];
    }
}
