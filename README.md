# site-config

A database backed config loader for Laravel with per-site configuration.

**NOT FINISHED YET.  DO NOT USE.**

## Features

* Adds a a websites table to store configuration data for your websites that
  may change on a per-whitelabel basis.

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
