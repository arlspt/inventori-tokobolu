<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pengadaan;
use App\Models\Produksi;
use App\Models\Distribusi;
use App\Models\Retur;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Reseller;
use App\Models\PengadaanDetail;
use App\Models\ProduksiDetail;
use App\Models\DistribusiDetail;
use App\Models\ReturDetail;

use App\Observers\ActivityLogObserver;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void {}

    public function boot(): void
    {
        Pengadaan::observe(ActivityLogObserver::class);

        Produksi::observe(ActivityLogObserver::class);

        Distribusi::observe(ActivityLogObserver::class);

        Retur::observe(ActivityLogObserver::class);

        User::observe(ActivityLogObserver::class);

        Supplier::observe(ActivityLogObserver::class);

        Reseller::observe(ActivityLogObserver::class);

        PengadaanDetail::observe(ActivityLogObserver::class);

        ProduksiDetail::observe(ActivityLogObserver::class);

        DistribusiDetail::observe(ActivityLogObserver::class);

        ReturDetail::observe(ActivityLogObserver::class);
    }
}
