<?php

use Delatbabel\SiteConfig\Models\Config as ConfigModel;
use Delatbabel\SiteConfig\Models\Website;
use Illuminate\Database\Seeder;

class TestConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a couple of websites
        $prod = Website::create([
            'name'          => 'Main Production Website',
            'http_host'     => 'www.mysite.com',
            'environment'   => 'production',
        ]);
        $test = Website::create([
            'name'          => 'Main Test Website',
            'http_host'     => 'test.mysite.com',
            'environment'   => 'test',
        ]);
        $dev = Website::create([
            'name'          => 'Development Website',
            'http_host'     => 'localhost',
            'environment'   => 'test',
        ]);

        // Create some config entries
        ConfigModel::create([
            'website_id'    => $prod->id,
            'environment'   => 'production',
            'group'         => 'config',
            'key'           => 'fruit',
            'value'         => 'apple-prod-www',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'website_id'    => $test->id,
            'environment'   => 'test',
            'group'         => 'config',
            'key'           => 'fruit',
            'value'         => 'banana-test-test',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'group'         => 'config',
            'key'           => 'fruit',
            'value'         => 'orange-default-default',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'environment'   => 'test',
            'group'         => 'config',
            'key'           => 'fruit',
            'value'         => 'peach-test-default',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'environment'   => 'production',
            'group'         => 'config',
            'key'           => 'fruit',
            'value'         => 'plum-production-default',
            'type'          => 'string',
        ]);

        ConfigModel::create([
            'group'         => 'config',
            'key'           => 'animal',
            'value'         => 'elephant-default-default',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'group'         => 'config',
            'key'           => 'bird',
            'value'         => 'canary-default-default',
            'type'          => 'string',
        ]);
        ConfigModel::create([
            'group'         => 'config',
            'key'           => 'raptor',
            'value'         => 'falcon-default-default',
            'type'          => 'string',
        ]);
    }
}
