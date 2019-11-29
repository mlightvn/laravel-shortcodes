<?php

namespace NamTenTen\ShortCodes;

use NamTenTen\ShortCodes\Console\WidgetMakeCommand;
use NamTenTen\ShortCodes\Factories\AsyncWidgetFactory;
use NamTenTen\ShortCodes\Factories\WidgetFactory;
use NamTenTen\ShortCodes\Misc\LaravelApplicationWrapper;
use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'laravel-shortcodes'
        );

        $this->app->bind('arrilot.widget', function () {
            return new WidgetFactory(new LaravelApplicationWrapper());
        });

        $this->app->bind('arrilot.async-widget', function () {
            return new AsyncWidgetFactory(new LaravelApplicationWrapper());
        });

        $this->app->singleton('arrilot.widget-group-collection', function () {
            return new WidgetGroupCollection(new LaravelApplicationWrapper());
        });

        $this->app->singleton('arrilot.widget-namespaces', function () {
            return new NamespacesRepository();
        });

        $this->app->singleton('command.widget.make', function ($app) {
            return new WidgetMakeCommand($app['files']);
        });

        $this->commands('command.widget.make');

        $this->app->alias('arrilot.widget', 'NamTenTen\ShortCodes\Factories\WidgetFactory');
        $this->app->alias('arrilot.async-widget', 'NamTenTen\ShortCodes\Factories\AsyncWidgetFactory');
        $this->app->alias('arrilot.widget-group-collection', 'NamTenTen\ShortCodes\WidgetGroupCollection');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('laravel-shortcodes.php'),
        ]);

        $routeConfig = [
            'namespace'  => 'NamTenTen\ShortCodes\Controllers',
            'prefix'     => 'arrilot',
            'middleware' => $this->app['config']->get('laravel-shortcodes.route_middleware', []),
        ];

        if (!$this->app->routesAreCached()) {
            $this->app['router']->group($routeConfig, function ($router) {
                $router->get('load-widget', 'WidgetController@showWidget');
            });
        }

        Blade::directive('widget', function ($expression) {
            return "<?php echo app('arrilot.widget')->run($expression); ?>";
        });

        Blade::directive('asyncWidget', function ($expression) {
            return "<?php echo app('arrilot.async-widget')->run($expression); ?>";
        });

        Blade::directive('widgetGroup', function ($expression) {
            return "<?php echo app('arrilot.widget-group-collection')->group($expression)->display(); ?>";
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['arrilot.widget', 'arrilot.async-widget'];
    }
}
