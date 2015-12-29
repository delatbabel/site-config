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
 * values to the database.
 *
 * ### Examples
 *
 * Save a single default parameter with group.key
 *
 * <code>
 * $saver = new ConfigSaverRepository();
 * $saver->set('config.bird', 'eagle-changed-changed');
 * </code>
 *
 * If you omit the group, then "config" is assumed as the default.
 *
 * <code>
 * $saver = new ConfigSaverRepository();
 * $saver->set('bird', 'eagle-changed-changed');
 * </code>
 *
 * Saving an array of configuration values:
 *
 * <code>
 * $saver = new ConfigSaverRepository();
 * $saver->set('config.plant', [
 *     'vegetable' => 'potato',
 *     'tree'      => 'oak',
 *     'flower'    => 'rose',
 * ]);
 * </code>
 *
 * Saving an array of configuration values and then updating part of the array:
 *
 * <code>
 * $saver = new ConfigSaverRepository();
 * $saver->set('config.plant', [
 *     'vegetable' => 'potato',
 *     'tree'      => 'oak',
 *     'flower'    => 'rose',
 * ]);
 * $saver->set('config.plant.vegetable', 'turnip');
 * </code>
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

        // What type is the value we are setting?
        if (is_array($value)) {
            $type = 'array';
        } elseif (is_int($value)) {
            $type = 'integer';
        } else {
            $type = 'string';
        }

        // Now we have the group / item as separate values, we can store these
        ConfigModel::set($item, $value, $group, $environment, $website_id, $type);

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
