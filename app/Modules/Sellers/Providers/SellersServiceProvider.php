<?php

namespace App\Modules\Sellers\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;

class SellersServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(module_path('Sellers', 'Database/Migrations'));
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
                module_path('Sellers', 'Config/config.php') => config_path('sellers.php'),
            ],
            'config'
        );
        $this->mergeConfigFrom(
            module_path('Sellers', 'Config/config.php'),
            'sellers'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews() {
        $viewPath = resource_path('views/modules/sellers');

        $sourcePath = module_path('Sellers', 'Resources/views');

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
                        return $path . '/modules/sellers';
                    },
                    \Config::get('view.paths')
                ),
                [$sourcePath]
            ),
            'sellers'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations() {
        $langPath = resource_path('lang/modules/sellers');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'sellers');
        } else {
            $this->loadTranslationsFrom(module_path('Sellers', 'Resources/lang'), 'sellers');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories() {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('Sellers', 'Database/factories'));
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
