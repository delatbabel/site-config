<?php
/**
 * Class ConfigSaverRepository
 */

namespace Delatbabel\SiteConfig\Repository;

use Delatbabel\SiteConfig\Models\Config as ConfigModel;

/**
 * Class ConfigSaverRepository
 *
 * This contains all of the functionality to save configuration
 * values to the database..
 */
class ConfigSaverRepository
{
    /** @var  ConfigLoaderRepository */
    protected $configLoader;

    /**
     * Set a given configuration value and store it in the database.
     *
     * @param  string      $key
     * @param  mixed       $value
     * @param  null|string $environment
     * @param  null|integer $website_id
     *
     * @return void
     */
    public function set($key, $value, $environment = null, $website_id = null)
    {
        // Bootstrap the ConfigLoaderRepository class
        $siteConfigRepository = new ConfigLoaderRepository();

        // Parse the key here into group.key.part components.
        //
        // Any time a . is present in the key we are going to assume the first section
        // is the group.  If there is no group present then we assume that the group
        // is "config".

        $explodedOnGroup = explode('.', $key);
        if (count($explodedOnGroup) > 1) {
            $group = array_shift($explodedOnGroup);
            $item  = implode('.', $explodedOnGroup);
        } else {
            $group = 'config';
            $item  = $key;
        }

        // Now we have the group / item as separate values, we can store these
        ConfigModel::set($item, $value, $group, $environment, $website_id);

        // Flush the cache
        $siteConfigRepository->forgetConfig();

        // Reload the config
        $siteConfigRepository->loadConfiguration();

        // Fetch the configuration that has already been loaded.
        $config = config();

        // Store it into the running config
        $siteConfigRepository->setRunningConfiguration($config);
    }
}
