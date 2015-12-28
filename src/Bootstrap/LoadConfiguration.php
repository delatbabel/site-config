<?php
/**
 * Load Configuration Class
 *
 * @author Del
 */
namespace Delatbabel\SiteConfig\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfiguration;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Delatbabel\SiteConfig\Repository\SiteConfigRepository;

/**
 * Load Configuration Class
 *
 * Provides an alternative to the standard laravel configuration load boostrapper.
 *
 * You will need to make the following changes:
 *
 * * Modify each of app/Console/Kernel.php and app/Http/Kernel.php to include
 *   the following bootstrappers function:
 *
 * <code>
 * protected function bootstrappers()
 * {
 *     $bootstrappers = parent::bootstrappers();
 *     // Swap out the default Laravel LoadConfiguration class with our own.
 *     foreach ($bootstrappers as $key => $value) {
 *         if ($value == 'Illuminate\Foundation\Bootstrap\LoadConfiguration') {
 *             $bootstrappers[$key] = 'Delatbabel\SiteConfig\Bootstrap\LoadConfiguration';
 *         }
 *     }
 *     return $bootstrappers;
 * }
 * </code>
 *
 * Note that Delatbabel\SiteConfig\Bootstrap\LoadConfiguration replaces the original
 * line Illuminate\Foundation\Bootstrap\LoadConfiguration.  You may of course
 * already have a bootstrappers function in your Kernel.php files with other
 * bootstrappers replaced, in which case you just need to modify it to include
 * the updated LoadConfiguration bootstrapper code.
 */
class LoadConfiguration extends BaseLoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        // Call the parent bootstrapper to load the base configuration files.
        parent::bootstrap($app);

        // Fetch the configuration that was just loaded.
        $config = config();

        // Bootstrap the Repository class
        $siteRepository = new SiteConfigRepository();

        // Load the current configuration from the database and add it in to
        // the configuration loaded from files.
        $this->loadConfigurationDatabase($app, $config, $siteRepository);
    }

    /**
     * Load the configuration items from the database.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @param  SiteConfigRepository $siteConfigRepository
     * @return void
     */
    protected function loadConfigurationDatabase(Application $app, RepositoryContract $repository, SiteConfigRepository $siteConfigRepository)
    {
        foreach ($siteConfigRepository->loadConfiguration() as $group => $groupConfig) {
            $repository->set($group, $groupConfig);
        }
    }
}
