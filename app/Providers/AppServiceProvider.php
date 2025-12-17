<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Interfaces\PriceTableRepositoryInterface;
use App\Repositories\PriceTableRepository;
use App\Interfaces\DistributionCenterRepositoryInterface;
use App\Repositories\DistributionCenterRepository;
use App\Interfaces\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Interfaces\ShipmentRepositoryInterface;
use App\Repositories\ShipmentRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PriceTableRepositoryInterface::class, PriceTableRepository::class);
        $this->app->bind(DistributionCenterRepositoryInterface::class, DistributionCenterRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ShipmentRepositoryInterface::class, ShipmentRepository::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('pt_BR');
        date_default_timezone_set(config('app.timezone'));
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}