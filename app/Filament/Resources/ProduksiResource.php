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
use Filament\Tables\Actions\ActionGroup;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use App\Models\BeritaAcara;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;
    protected static ?int $navigationSort = 2; // Urutan 2 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $pluralModelLabel = 'Produksi';

    // Cek akses navigasi (apakah modul muncul di sidebar)
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */

        $user = Auth::user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        // karyawan selalu bisa lihat (navigasi tetap muncul)
        return $user->hasRole('karyawan');
    }

    // Karyawan tidak bisa create kalau modul tidak diizinkan
    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        return $user->dapatAksesModul('produksi');
    }

    // Karyawan tidak pernah bisa edit
    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasRole('admin');
    }

    // Karyawan tidak pernah bisa delete
    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasRole('admin');
    }

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
                                            ->modalSubmitActionLabel('Simpan')
                                            ->requiresConfirmation()
                                            ->modalHeading('Simpan Perubahan Varian?')
                                            ->modalDescription('Jika resep diubah, sistem akan membuat versi resep baru. Resep lama tetap tersimpan dan tidak dapat digunakan untuk produksi baru.')
                                            ->modalSubmitActionLabel('Ya, Simpan')
                                            ->modalCancelActionLabel('Batal')
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
                                                            ->placeholder('per 1 Varian')
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

                                                // ✅ dropdown pilih versi histori — hanya muncul saat mode edit & produk sudah dipilih
                                                Select::make('versi_histori')
                                                    ->label('Lihat Histori Resep (opsional)')
                                                    ->placeholder('Pilih versi untuk melihat detail resep')
                                                    ->visible(fn($get) => $get('mode') === 'edit' && filled($get('produk_id')))
                                                    ->options(function ($get) {
                                                        $produkId = $get('produk_id');
                                                        if (!$produkId) return [];

                                                        return \App\Models\Resep::where('produk_id', $produkId)
                                                            ->select('versi', 'aktif', 'berlaku_dari')
                                                            ->distinct()
                                                            ->orderBy('versi', 'desc')
                                                            ->get()
                                                            ->mapWithKeys(function ($r) {
                                                                $tgl   = $r->berlaku_dari
                                                                    ? \Carbon\Carbon::parse($r->berlaku_dari)
                                                                    ->locale('id')->translatedFormat('d M Y')
                                                                    : '-';
                                                                $label = "Versi {$r->versi} — {$tgl}";
                                                                $label .= $r->aktif ? ' (Aktif)' : ' (Tidak Aktif)';
                                                                return [$r->versi => $label];
                                                            })
                                                            ->toArray();
                                                    })
                                                    ->native(false)
                                                    ->live()
                                                    ->dehydrated(false), // ✅ tidak disimpan ke DB

                                                // ✅ detail bahan resep versi yang dipilih
                                                \Filament\Forms\Components\Placeholder::make('detail_versi_histori')
                                                    ->label('Detail Resep')
                                                    ->visible(
                                                        fn($get) =>
                                                        $get('mode') === 'edit' &&
                                                            filled($get('produk_id')) &&
                                                            filled($get('versi_histori'))
                                                    )
                                                    ->content(function ($get) {
                                                        $produkId = $get('produk_id');
                                                        $versi    = $get('versi_histori');
                                                        if (!$produkId || !$versi) return '-';

                                                        $resep = \App\Models\Resep::where('produk_id', $produkId)
                                                            ->where('versi', $versi)
                                                            ->with('bahanBaku')
                                                            ->get();

                                                        if ($resep->isEmpty()) return 'Tidak ada data.';

                                                        return $resep->map(function ($r) {
                                                            $nama   = $r->bahanBaku->nama_bahan ?? '-';
                                                            $satuan = $r->bahanBaku->satuan ?? 'gram';
                                                            $jumlah = in_array($satuan, ['gram', 'ml'])
                                                                ? number_format($r->jumlah / 1000, 2) . ($satuan === 'gram' ? ' Kg' : ' L')
                                                                : $r->jumlah . ' ' . $satuan;
                                                            return "• {$nama}: {$jumlah}";
                                                        })->join("\n");
                                                    }),

                                                // tambahkan di akhir form kelolaProduk, sebelum tutup schema
                                                \Filament\Forms\Components\Checkbox::make('konfirmasi_versi')
                                                    ->label('Saya memahami bahwa perubahan resep akan membuat versi baru. Resep lama tetap tersimpan.')
                                                    ->visible(fn($get) => $get('mode') === 'edit')
                                                    ->required(fn($get) => $get('mode') === 'edit')
                                                    ->accepted(fn($get) => $get('mode') === 'edit'),
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

                                                // 🔥 EDIT — dengan versioning
                                                if ($data['mode'] === 'edit') {
                                                    $produk = \App\Models\Produk::find($data['produk_id']);
                                                    if (!$produk) return;

                                                    // update nama dan harga produk
                                                    $produk->update([
                                                        'nama_produk' => $data['nama_produk'] ?? $produk->nama_produk,
                                                        'harga'       => $data['harga'] ?? $produk->harga,
                                                    ]);

                                                    // ✅ cek apakah resep berubah
                                                    $resepLama = \App\Models\Resep::where('produk_id', $produk->id)
                                                        ->where('aktif', true)
                                                        ->get();

                                                    $resepBaru = collect($data['resep'] ?? []);

                                                    $resepBerubah = $resepLama->count() !== $resepBaru->count() ||
                                                        $resepBaru->some(function ($item) use ($resepLama) {
                                                            $match = $resepLama->firstWhere('bahan_baku_id', $item['bahan_baku_id']);
                                                            return !$match || $match->jumlah != $item['jumlah'];
                                                        });

                                                    if ($resepBerubah) {
                                                        // ✅ nonaktifkan resep lama
                                                        \App\Models\Resep::where('produk_id', $produk->id)
                                                            ->where('aktif', true)
                                                            ->update(['aktif' => false]);

                                                        // ✅ buat versi baru
                                                        $versiTerbaru = \App\Models\Resep::where('produk_id', $produk->id)
                                                            ->max('versi') + 1;

                                                        foreach ($data['resep'] ?? [] as $item) {
                                                            \App\Models\Resep::create([
                                                                'produk_id'    => $produk->id,
                                                                'bahan_baku_id' => $item['bahan_baku_id'],
                                                                'jumlah'       => $item['jumlah'],
                                                                'versi'        => $versiTerbaru,
                                                                'berlaku_dari' => now()->toDateString(),
                                                                'aktif'        => true,
                                                            ]);
                                                        }

                                                        \Filament\Notifications\Notification::make()
                                                            ->title("Resep diperbarui ke Versi $versiTerbaru")
                                                            ->body('Resep versi sebelumnya tetap tersimpan di histori.')
                                                            ->success()
                                                            ->send();
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
                                \Filament\Forms\Components\Placeholder::make('histori_resep')
                                    ->label('Histori Versi Resep')
                                    ->visible(fn($get) => $get('mode') === 'edit' && filled($get('produk_id')))
                                    ->content(function ($get) {
                                        $produkId = $get('produk_id');
                                        if (!$produkId) return '-';

                                        $histori = \App\Models\Resep::where('produk_id', $produkId)
                                            ->where('aktif', false)
                                            ->select('versi', 'berlaku_dari')
                                            ->distinct()
                                            ->orderBy('versi', 'desc')
                                            ->get();

                                        if ($histori->isEmpty()) return 'Belum ada histori versi sebelumnya.';

                                        return $histori->map(function ($r) {
                                            $tgl = $r->berlaku_dari
                                                ? \Carbon\Carbon::parse($r->berlaku_dari)->locale('id')->translatedFormat('d M Y')
                                                : '-';
                                            return "Versi {$r->versi} — berlaku sejak {$tgl}";
                                        })->join(' | ');
                                    }),

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
            ->searchPlaceholder('Cari Nama Pembuat...')
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
                    })
                    ->formatStateUsing(function ($state, $record) {

                        $role = $record->user?->roles->first()?->name;

                        $roleLabel = match ($role) {
                            'admin' => 'Admin',
                            'karyawan' => 'Karyawan',
                            default => ucfirst($role ?? '-'),
                        };

                        return $state . ' (' . $roleLabel . ')';
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%");
                        });
                    }),

                TextColumn::make('produksiDetail')
                    ->label('Jumlah Varian')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {

                        return $record->produksiDetail->sum(function ($item) {
                            return ($item->jumlah_produksi ?? 0) - ($item->gagal ?? 0);
                        });
                    }),
                TextColumn::make('expired_at')
                    ->label('Expired')
                    ->getStateUsing(function ($record) {
                        // ✅ ambil expired_at paling awal dari semua detail
                        $earliest = $record->produksiDetail
                            ->whereNotNull('expired_at')
                            ->sortBy('expired_at')
                            ->first();
                        return $earliest?->expired_at;
                    })
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        return Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d M Y');
                    }),
            ])
            ->recordClasses(function ($record) {
                $earliest = $record->produksiDetail
                    ->whereNotNull('expired_at')
                    ->sortBy('expired_at')
                    ->first();

                if (!$earliest) return null;

                $isExpired = Carbon::parse($earliest->expired_at)->isPast();

                return $isExpired ? 'bg-red-50 dark:bg-red-900/20' : null;
            })
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
                        Select::make('bulan')
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
            ->headerActions([
                \Filament\Tables\Actions\Action::make('berita_acara')
                    ->label('Berita Acara')
                    ->icon('heroicon-o-document-text')
                    ->color('success') // ✅ hijau sesuai primary
                    ->form([
                        \Filament\Forms\Components\Select::make('topik')
                            ->label('Topik')
                            ->options(['hasil_produksi' => 'Hasil Produksi'])
                            ->default('hasil_produksi')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        \Filament\Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'hilang'     => 'Hilang',
                                'rusak'      => 'Rusak',
                                'cacat'      => 'Cacat',
                                'tumpah'     => 'Tumpah',
                                'kadaluarsa' => 'Kadaluarsa',
                                'lainnya'    => 'Lainnya',
                            ])
                            ->native(false)
                            ->required(),

                        \Filament\Forms\Components\Select::make('nama_item')
                            ->label('Produk')
                            ->options(\App\Models\Produk::orderBy('nama_produk')->pluck('nama_produk', 'nama_produk'))
                            ->searchable()
                            ->required(),

                        // ✅ GRID — satuan hidden, jumlah dengan postfix pcs
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('satuan')
                                    ->label('Satuan')
                                    ->options(['pcs' => 'Pcs'])
                                    ->default('pcs')
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden() // ✅ disembunyikan
                                    ->required(),

                                \Filament\Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah Terdampak')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->postfix('pcs') // ✅ fixed pcs
                                    ->columnSpan(2), // ✅ full width karena satuan hidden
                            ]),

                        \Filament\Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Jelaskan kondisi aktual yang terjadi')
                            ->rows(3),
                    ])
                    ->action(function (array $data) {
                        \App\Models\BeritaAcara::create([
                            'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                            'modul'      => 'produksi',
                            'topik'      => 'hasil_produksi',
                            'kategori'   => $data['kategori'],
                            'nama_item'  => $data['nama_item'],
                            'jumlah'     => $data['jumlah'],
                            'satuan'     => 'pcs',
                            'keterangan' => $data['keterangan'] ?? null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Berita acara berhasil dicatat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Tambah Berita Acara — Hasil Produksi')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),
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
