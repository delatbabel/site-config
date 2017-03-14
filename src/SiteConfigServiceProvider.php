<?php
/**
 * SiteConfig Service Provider
 */

namespace Delatbabel\SiteConfig;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

/**
 * SiteConfig Service Provider
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 * Your own application, as well as all of Laravel's core services are bootstrapped
 * via service providers.
 *
 * ### Functionality
 *
 * * Adds a websites table to store data for your websites that
 *   may change on a per-website basis.
 * * Adds a configs table that stores configuration data for your system that may
 *   change dynamically, or on a per-website or per-environment basis.
 * * Contains a bootstrapper that loads the database stored configuration into your
 *   website configuration, merging it with that loaded from the normal Laravel config
 *   files.  So you can access stored config like normal Laravel config, e.g.
 *
 * <code>
 * // Access all configuration variables including Laravel + database stored configuration
 * $config = config()->all();
 *
 * // Fetch a config variable by path
 * $my_fruit = config('config.fruit');
 * </code>
 *
 * * Database backed configuration is cached for up to 60 minutes to reduce the total amount
 *   of hits on your database.
 *
 * @see  Illuminate\Support\ServiceProvider
 * @link http://laravel.com/docs/5.1/providers
 */
class SiteConfigServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * This method is called after all other service providers have
     * been registered, meaning you have access to all other services
     * that have been registered by the framework.
     *
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        if (method_exists('Illuminate\\Support\\ServiceProvider', 'boot')) {
            parent::boot($events);
        }

        // Publish the database migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => $this->app->databasePath() . '/migrations'
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../config' => config_path()
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * Within the register method, you should only bind things into the
     * service container. You should never attempt to register any event
     * listeners, routes, or any other piece of functionality within the
     * register method.
     *
     * @return void
     */
    public function register()
    {
        App::bind('siteconfig', function () {
            return new \Delatbabel\SiteConfig\Repository\ConfigSaverRepository();
        });
    }
}
