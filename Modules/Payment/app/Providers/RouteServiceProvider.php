<?php

namespace Modules\Payment\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Payment';

    public function boot(): void
    {
        $this->registerApiRoutes();
    }

    protected function registerApiRoutes(): void
    {
        $routesFile = module_path($this->moduleName, 'routes/api.php');
        if (file_exists($routesFile)) {
            Route::middleware('api')
                ->prefix('api')
                ->group(function () use ($routesFile) {
                    require $routesFile;
                });
        }
    }
}
