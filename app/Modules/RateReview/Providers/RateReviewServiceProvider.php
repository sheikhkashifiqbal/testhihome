<?php

namespace App\Modules\RateReview\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class RateReviewServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('RateReview', 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('RateReview', 'Config/config.php') => config_path('ratereview.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('RateReview', 'Config/config.php'), 'ratereview'
        );
        $this->mergeConfigFrom(
            module_path('RateReview', 'Config/constants.php'), 'rating.contants'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/ratereview');

        $sourcePath = module_path('RateReview', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/ratereview';
        }, \Config::get('view.paths')), [$sourcePath]), 'ratereview');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/ratereview');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'ratereview');
        } else {
            $this->loadTranslationsFrom(module_path('RateReview', 'Resources/lang'), 'ratereview');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('RateReview', 'Database/factories'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
