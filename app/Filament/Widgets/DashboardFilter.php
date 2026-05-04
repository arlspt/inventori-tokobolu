<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardFilter extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-filter';

    public $filter = 'minggu_ini';

    protected int | string | array $columnSpan = 'full';

    public function setFilter($value)
    {
        $this->filter = $value;

        // 🔥 broadcast ke widget lain
        $this->dispatch('filterUpdated', filter: $value);
    }
}
