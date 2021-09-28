<?php

namespace App\Modules\Orders\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class OrdersServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot() {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('Orders', 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig() {
        $this->publishes(
            [
                module_path('Orders', 'Config/config.php') => config_path('orders.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            module_path('Orders', 'Config/config.php'),
            'orders'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews() {
        $viewPath = resource_path('views/modules/orders');

        $sourcePath = module_path('Orders', 'Resources/views');

        $this->publishes(
            [
                $sourcePath => $viewPath
            ],
            'views'
        );

        $this->loadViewsFrom(
            array_merge(
                array_map(
                    function ($path) {
                        return $path . '/modules/orders';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'orders'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations() {
        $langPath = resource_path('lang/modules/orders');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'orders');
        } else {
            $this->loadTranslationsFrom(module_path('Orders', 'Resources/lang'), 'orders');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories() {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('Orders', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [];
    }
}
