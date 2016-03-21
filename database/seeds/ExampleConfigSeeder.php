<?php

use Illuminate\Database\Seeder;
use Delatbabel\SiteConfig\Facades\SiteConfigSaver;

/**
 * Class ExampleConfigSeeder
 *
 * This is just some example configuraton which you can modify and use
 * in your application.  It demonstrates how to use the SiteConfigSaver
 * facade to populate the configs table so that the bootstrapper will
 * load the configuration.
 */
class ExampleConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Base configuration data
        // This configuration will be saved as "global" -- for all websites
        // and environments.
        SiteConfigSaver::set('site.sysadmin.page_title', 'System Administrator Dashboard');
        SiteConfigSaver::set('site.sysadmin.description', 'Check What Is Going On');
        SiteConfigSaver::set('site.footer', 'This is the page footer.');
        SiteConfigSaver::set('site.some_cool_guy', 'Del');
        SiteConfigSaver::set('site.some_url', 'https://github.com/delatbabel');

        // This config will only be used in the "test" environment
        SiteConfigSaver::set('site.page_header', 'Test Environment', 'test');

        // This config will only be used in the "production" environment
        SiteConfigSaver::set('site.page_header', 'Production Environment', 'production');

        // This config will only be used for website->id == 1
        SiteConfigSaver::set('site.page_header', 'Special Header for Website 1', null, 1);
    }
}
