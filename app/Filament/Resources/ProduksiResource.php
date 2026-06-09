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
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;
    protected static ?int $navigationSort = 2; // Urutan 2 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-cube';
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
                            ->addActionLabel('Tambah Varian') // Ubah label tombol "Add Item" menjadi "Tambah Produk"
                            ->columns(3)
                            ->schema([
                                // PRODUK + TAMBAH LANGSUNG
                                Select::make('produk_id')
                                    ->label('Varian')
                                    ->placeholder('Pilih Varian')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disableOptionWhen(function ($value, $get) {
                                        $dipilih = collect($get('../../produksiDetail'))
                                            ->pluck('produk_id')
                                            ->filter()
                                            ->values()
                                            ->toArray();
                                        $currentProdukId = $get('produk_id');
                                        if ($value == $currentProdukId) return false; // row sendiri boleh
                                        return in_array($value, $dipilih);
                                    })
                                    ->afterStateUpdated(function ($state, $set, $livewire) {
                                        // kalau produk dikosongkan
                                        if (!$state) {
                                            // reset jumlah
                                            $set('jumlah_produksi', null);
                                            // 🔥 hapus error jumlah
                                            $livewire->resetErrorBag('data.produksiDetail.*.jumlah_produksi');
                                        }
                                    })
                                    ->suffixAction(
                                        Action::make('kelolaProduk')
                                            ->label('Kelola Varian')
                                            ->icon('heroicon-o-cog-6-tooth')
                                            ->modalHeading('Kelola Varian')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->form([
                                                // MODE
                                                Select::make('mode')
                                                    ->label('Aksi')
                                                    ->options([
                                                        'tambah' => 'Tambah Varian',
                                                        'edit' => 'Ubah Varian',
                                                        'hapus' => 'Hapus Varian',
                                                    ])
                                                    ->default('tambah')
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
                                                    ->label('Pilih Varian')
                                                    ->options(\App\Models\Produk::pluck('nama_produk', 'id'))
                                                    ->searchable()
                                                    ->placeholder('Pilih varian yang mau diubah')
                                                    ->visible(fn($get) => in_array($get('mode'), ['edit', 'hapus']))
                                                    ->required(fn($get) => in_array($get('mode'), ['edit', 'hapus']))
                                                    ->live()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, $set, $get) {

                                                        if (!$state || $get('mode') !== 'edit') return;

                                                        $produk = \App\Models\Produk::with('resep')->find($state);
                                                        if (!$produk) return;

                                                        // isi field
                                                        $set('nama_produk', $produk->nama_produk);
                                                        $set('harga', $produk->harga);

                                                        // isi resep
                                                        $set('resep', $produk->resep->map(fn($r) => [
                                                            'bahan_baku_id' => $r->bahan_baku_id,
                                                            'jumlah' => $r->jumlah,
                                                        ])->values()->toArray());
                                                    }),

                                                // NAMA PRODUK
                                                TextInput::make('nama_produk')
                                                    ->label('Nama Varian')
                                                    ->dehydrated(true)
                                                    ->visible(fn($get) => $get('mode') === 'tambah')
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
                                                    ->minItems(1)
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
                                                        'nama_produk' => $data['nama_produk'] ?? $produk->nama_produk,
                                                        'harga' => $data['harga'] ?? $produk->harga,
                                                    ]);
                                                    \App\Models\Resep::where('produk_id', $produk->id)->delete();
                                                    foreach ($data['resep'] ?? [] as $item) {
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $livewire) {
                                        if (!$get('produk_id')) {
                                            $livewire->resetErrorBag('data.produksiDetail.*.jumlah_produksi');
                                        }
                                    })
                                    ->rule(function ($get, $record) {
                                        return function ($attribute, $value, $fail) use ($get, $record) {

                                            // 🔥 kalau edit & jumlah tidak berubah → skip validasi
                                            if ($record && $record->jumlah_produksi == $value) {
                                                return;
                                            }

                                            $produk = \App\Models\Produk::with('resep')->find($get('produk_id'));

                                            if (!$produk) return;

                                            foreach ($produk->resep as $resep) {

                                                $bahan = \App\Models\BahanBaku::find($resep->bahan_baku_id);

                                                $total = $resep->jumlah * $value;

                                                if ($bahan->stok < $total) {
                                                    $fail("Stok tidak cukup: {$bahan->nama_bahan}");
                                                    return;
                                                }
                                            }
                                        };
                                    }),

                                // 🔥 GAGAL
                                TextInput::make('gagal')
                                    ->label('Varian Gagal')
                                    ->numeric()
                                    ->default(0)
                                    ->live() // ✅ tambahkan live
                                    ->afterStateUpdated(function ($state, $get, $set) {

                                        $jumlah = (int) $get('jumlah_produksi');
                                        $gagal  = (int) $state;

                                        if ($gagal > $jumlah) {
                                            $set('gagal', $jumlah); // ✅ auto-reset ke maksimal

                                            Notification::make()
                                                ->title('Varian gagal melebihi jumlah produksi')
                                                ->body("Maksimal varian gagal hanya $jumlah")
                                                ->warning()
                                                ->send();
                                        }
                                    })
                                    ->rule(function ($get) {
                                        return function ($attribute, $value, $fail) use ($get) {
                                            $jumlah = (int) $get('jumlah_produksi');
                                            if ((int) $value > $jumlah) {
                                                $fail("Varian gagal tidak boleh melebihi jumlah produksi ($jumlah)");
                                            }
                                        };
                                    }),

                            ])
                            ->reorderable(false)
                            ->itemLabel(fn() => 'Varian')
                            ->addActionLabel('Tambah Varian') // Ubah label tombol "Add Item" menjadi "Tambah Produk"
                            ->addAction(
                                fn($action) => $action
                                    ->label('Tambah Varian')
                                    ->icon('heroicon-m-plus') // menambahkan icon di button
                            ),
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
                    ->label('Jumlah Varian')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {

                        return $record->produksiDetail->sum(function ($item) {
                            return ($item->jumlah_produksi ?? 0) - ($item->gagal ?? 0);
                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Dibuat Oleh')
                    ->options(
                        \App\Models\User::all()->mapWithKeys(function ($user) {
                            $role = $user->getRoleNames()->first() ?? '-';
                            return [$user->id => $user->name . ' (' . ucfirst($role) . ')'];
                        })
                    )
                    ->placeholder('Semua User'),

                Tables\Filters\Filter::make('bulan')
                    ->form([
                        \Filament\Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options(function () {
                                return \App\Models\Produksi::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan_key')
                                    ->distinct()
                                    ->orderByRaw('bulan_key DESC')
                                    ->pluck('bulan_key')
                                    ->mapWithKeys(fn($b) => [
                                        $b => Carbon::createFromFormat('Y-m', $b)
                                            ->locale('id')
                                            ->translatedFormat('F Y')
                                    ])
                                    ->toArray();
                            })
                            ->placeholder('Semua Bulan'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!filled($data['bulan'])) return $query;
                        [$year, $month] = explode('-', $data['bulan']);
                        return $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
                    })
                    ->indicateUsing(function (array $data) {
                        if (!filled($data['bulan'])) return null;
                        return 'Bulan: ' . Carbon::createFromFormat('Y-m', $data['bulan'])
                            ->locale('id')->translatedFormat('F Y');
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make('view')
                        ->modalHeading('Detail Produksi')
                        ->label('Detail')
                        ->color('info')
                        ->infolist([
                            \Filament\Infolists\Components\Section::make(
                                fn($record) => Carbon::parse($record->tanggal)
                                    ->locale('id')
                                    ->translatedFormat('l, d F Y')
                            )
                                ->schema([
                                    \Filament\Infolists\Components\View::make('infolists.components.produksi-detail-table')
                                        ->viewData(fn($record) => ['detail' => $record->produksiDetail->load('produk')])
                                ]),
                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Hapus Produksi?')
                        ->modalDescription("Tindakan ini akan mengembalikan semua bahan baku yang digunakan dan mengurangi stok produk.")
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->before(function ($record) {
                            foreach ($record->produksiDetail as $detail) {
                                $produk = \App\Models\Produk::find($detail->produk_id);
                                $produk->refresh();
                                // 🔥 hitung stok setelah rollback produksi ini
                                $stokSetelahRollback = $produk->stok - $detail->jumlah_produksi;
                                if ($stokSetelahRollback < 0) {
                                    Notification::make()
                                        ->title('Gagal menghapus produksi')
                                        ->body('Sebagian stok sudah digunakan, produksi tidak bisa dihapus.')
                                        ->danger()
                                        ->send();
                                    throw ValidationException::withMessages([
                                        'delete' => 'Stok tidak cukup untuk rollback.'
                                    ]);
                                }
                            }
                        }),
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
