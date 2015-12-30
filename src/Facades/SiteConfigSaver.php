<?php
/**
 * SiteConfigSaver facade
 */
namespace Delatbabel\SiteConfig\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class SiteConfigSaver
 *
 * Facade class for accessing the site config saver.
 *
 * ### Example
 *
 * <code>
 * $config = SiteConfigSaver::get();
 * </code>
 *
 * @see  Delatbabel\SiteConfig\Repository\ConfigSaverRepository
 */
class SiteConfigSaver extends Facade
{
    /**
     * Get the registered component.
     *
     * @return object
     */
    protected static function getFacadeAccessor()
    {
        return 'siteconfig';
    }
}
