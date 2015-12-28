# site-config

A database backed config loader for Laravel with per-site configuration.

**NOT FINISHED YET.  DO NOT USE.**

## Features

* Adds a websites table to store data for your websites that
  may change on a per-website basis.
* Adds a configs table that stores configuration data for your system that may
  change dynamically, or on a per-website or per-environment basis.

## Installation

Add these lines to your composer.json file:

```
    "require": {
        "delatbabel/site-config": "~1.0"
    },
```

Once that is done, run the composer update command:

```
    composer update
```

### Register Service Provider

After composer update completes, add this line to your config/app.php file in the 'providers' array:

```
    Delatbabel\SiteConfig\SiteConfigServiceProvider::class,
```

### Boostrap the Config Loader

Modify each of app/Console/Kernel.php and app/Http/Kernel.php to include the following bootstrappers function:

```php
protected function bootstrappers()
{
    $bootstrappers = parent::bootstrappers();

    // Swap out the default Laravel LoadConfiguration class with our own.
    foreach ($bootstrappers as $key => $value) {
        if ($value == 'Illuminate\Foundation\Bootstrap\LoadConfiguration') {
            $bootstrappers[$key] = 'Delatbabel\SiteConfig\Bootstrap\LoadConfiguration';
        }
    }

    return $bootstrappers;
}
```

Note that Delatbabel\SiteConfig\Bootstrap\LoadConfiguration replaces the original
line Illuminate\Foundation\Bootstrap\LoadConfiguration.  You may of course
already have a bootstrappers function in your Kernel.php files with other
bootstrappers replaced, in which case you just need to modify it to include
the updated LoadConfiguration bootstrapper code.

### Incorporate and Run the Migrations

Finally, incorporate and run the migration scripts to create the database tables as follows:

```php
php artisan vendor:publish --tag=migrations --force
php artisan migrate
```


# Credits

Both of these packages were developed for Laravel 4 but provided some ideas:

* https://packagist.org/packages/jameswmcnab/config-db
* https://packagist.org/packages/hailwood/database-config-loader
