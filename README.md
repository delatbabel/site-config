# site-config

A database backed config loader for Laravel with per-site configuration.

**THIS IS IN EARLY DEVELOPMENT STAGE, USE AT YOUR OWN RISK** -- most functions are now
working and tested, although the phpUnit test cases won't run in the module.  I have some
codeception tests embedded in a sample application, yet to be published.

## Features

* Adds a websites table to store data for your websites that
  may change on a per-website basis.
* Adds a configs table that stores configuration data for your system that may
  change dynamically, or on a per-website or per-environment basis.
* Contains a bootstrapper that loads the database stored configuration into your
  website configuration, merging it with that loaded from the normal Laravel config
  files.  So you can access stored config like normal Laravel config, e.g.

```php
    // Access all configuration variables including Laravel + database stored configuration
    $config = config()->all();

    // Fetch a config variable by path
    $my_fruit = config('config.fruit');
```

* Database backed configuration is cached for up to 60 minutes to reduce the total amount
  of hits on your database.

## Installation

Add the package using composer from the command line:

```
    composer require delatbabel\site-config
```

Alternatively, pull the package in manually by adding these lines to your composer.json file:

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

### Add the Facade to the Aliases

In your config/app.php add this line to the aliases array:

```
    'SiteConfigSaver'   => Delatbabel\SiteConfig\Facades\SiteConfigSaver::class,
```

### Boostrap the Config Loader

Modify each of app/Console/Kernel.php and app/Http/Kernel.php to include the following bootstrappers function:

```php
protected function bootstrappers()
{
    $bootstrappers = parent::bootstrappers();

    // Add the SiteConfig bootstrapper to the end.
    $bootstrappers[] = 'Delatbabel\SiteConfig\Bootstrap\LoadConfiguration';

    return $bootstrappers;
}
```

Note that Delatbabel\SiteConfig\Bootstrap\LoadConfiguration is placed after
the normal bootstrappers. You may of course already have a bootstrappers
function in your Kernel.php files with other bootstrappers replaced, in
which case you just need to modify it to include the updated LoadConfiguration
bootstrapper code.

### Incorporate and Run the Migrations

Finally, incorporate and run the migration scripts to create the database tables as follows:

```php
php artisan vendor:publish --tag=migrations --force
php artisan migrate
```

# TODO

* More testing, bug fixing.  I have tried to create a test suite using orchestra/testbench
  but it does not appear to work.
* Better methods and a facade for saving the configuration to the database.
* Maybe a set of admin controllers/methods for updating the configuration.

# Architecture

This section explains the architecture of the package and the decisions that I made while
coding.

## Design Goals

Having seen a few packages that provided database backed configuration for Laravel 4 I wanted
something similar for Laravel 5 (I had previously worked on a similar system for Laravel 3).
I also wanted the configuration to be stored in a single database, but to be variable on a per-
website and per-environment basis.

I also wanted to have the configuration integrated with the base Laravel configuraton.  The other
packages that I have seen had their own Facades, so that accessing configuration worked like this:

```php
    $var1 = config('mycode.mykey');  // Or use the facade Config::get('mycode.mykey');
    $var2 = DbConfig::get('mycode.mykey');
```

I wanted the two to be integrated so that I could use Config::get() regardless of where the
configuration data came from.  That means that I have to load all of the config data in the
bootstrapper.

## Bootstrapping

Laravel includes a class called Illuminate\Foundation\Http\Kernel which handles bootstrapping
the application in Http mode, and a similar class for bootstrapping in Console mode.  These two
classes are normally extended in an application in the App\Http\Kernel and App\Console\Kernel
classes respectively.

Each of these classes loads a bunch of core classes that need bootstrapping, including the
Laravel logger.

Each of these classes contains a $bootstrappers array which contains the list of classes to be
bootstrapped, and a bootstrappers() function which returns that array content. I was originally
over-riding the $bootstrappers array but found that it varied between different patch releases
of Laravel, so instead I have extended the bootstrappers() function (or at least provided documentation
on how to extend it) so that it returns a modified version of the $bootstrappers array.

I initially tried to over-ride the Laravel provided LoadConfiguration bootstrapper, but in the
Laravel bootstrap order that occurs before the database and the facades are available.  So in
the end I had to create a new LoadConfiguration bootstrapper and add it to the end of the $bootstrappers
array so that it ran only after the database was available.

## Repository

The Repository class, ConfigLoaderRepository, contains enough information to be able to load the
**current** configuration from the database, including knowing about the current website and
environment.  The detecton of the environment and website, as well as the actual machinery of
loading configuration for a generic website and environment, is left to the Model classes.
The ConfigLoaderRepository class also handles all of the caching.

The ConfigSaverRepository class contains enough information to know which configuration values
to store when saving configuration data to the database, and to reload it after it has been
saved.

This provides a distinction between the business logic (deciding on what is to be done, and asking
for it to be done) from the database model (loading or saving the data without making logic
decisions).

## Models

The model classes follow the standard Laravel paradigms but I have added a few extra functions
to pull configuration data as required.

Thanks to the folks on StackOverflow DBA group who helped sort out the issues that I was having
with the query in fetchSettings, and also to hailwood whose logic I pulled for the Models\Config::set()
function.  As mentioned above there should be some further logic to assist in saving the config
data, as well as some test cases to be developed.

# Credits

Both of these packages were developed for Laravel 4 but provided some ideas:

* https://packagist.org/packages/jameswmcnab/config-db
* https://packagist.org/packages/hailwood/database-config-loader
