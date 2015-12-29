<?php
/**
 * Class SiteConfigRepository
 */

namespace Delatbabel\SiteConfig\Repository;

use Delatbabel\SiteConfig\Models\Config as ConfigModel;
use Delatbabel\SiteConfig\Models\Website as WebsiteModel;
use Illuminate\Support\Facades\Cache;

/**
 * Class SiteConfigRepository
 */
class SiteConfigRepository
{
    /** @var  string */
    protected $environment;

    /** @var  integer */
    protected $website_id;

    /**
     * Public accessor for environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Public accessor for website ID
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->website_id;
    }

    /**
     * Fetch all configuration groups.
     *
     * @return array
     */
    public function fetchAllGroups()
    {
        $groups = ConfigModel::fetchAllGroups();
        return $groups;
    }

    /**
     * Loads the internal website_id and environment
     *
     * @return SiteConfigRepository
     */
    public function loadEnvironment()
    {
        // Fetch the current application environment.
        $this->environment = app()->environment();
        $this->website_id = null;

        // Fetch the current web site data and check to see if it has an
        // alternative environment.
        $website_data = WebsiteModel::currentWebsiteData();
        if (! empty($website_data)) {
            if (! empty($website_data['environment'])) {
                $this->environment = $website_data['environment'];
            }
            // We also want the website ID
            $this->website_id = $website_data['id'];
        }

        return $this;
    }

    /**
     * Load the database backed configuration.
     *
     * This function does the work of loading the entire site configuration
     * from the database and returns it as a group => [key=>value] set.  The
     * configuration is retrieved from cache if it already exists there.
     *
     * @return array
     */
    public function loadConfiguration()
    {
        /** @var SiteConfigRepository $repository */
        $repository = $this->loadEnvironment();

        $cache_key = 'site-config.' . $repository->getEnvironment() . '.' . $repository->getWebsiteId();
        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        $config = array();

        // Fetch all groups, and fetch the config for each group based on the
        // environment and website ID of the current site.
        foreach ($repository->fetchAllGroups() as $group) {
            $groupConfig = ConfigModel::fetchSettings(
                $repository->getEnvironment(),
                $repository->getWebsiteId(),
                $group
            );
            $config[$group] = $groupConfig;
        }

        Cache::put($cache_key, $config, 60);
        return $config;
    }
}
