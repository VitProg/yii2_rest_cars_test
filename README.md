Yii2 Cars Rest And Parser Test
================================

DIRECTORY STRUCTURE
-------------------

      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      modules/            contains modules
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources

REQUIREMENTS
------------

* PHP >= 5.4.0.
* Redis DB


INSTALLATION
------------

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this application template using the following command:

~~~
php composer global require "fxp/composer-asset-plugin:~1.1.0"
git clone https://github.com/VitProg/yii2_rest_cars_test
cd yii2_rest_cars_test
composer install
~~~

Directories permissions:
~~~
chmod 777 ./runtime/ -R
chmod 777 ./web/assets/ -R
chmod 755 yii
~~~

## Nginx

Config server `root` to `project/web` directory


CONFIGURATION
-------------

### Database

Create files `config/redis_local.php` and `config/test_redis.local.php` (for unit tests) with real data, for example:

```php
return [
    'class' => 'yii\redis\Connection',
    'hostname' => 'localhost',
    'port' => 6379,
    'database' => 2,
//    'username' => 'root', // uncomment if needed
//    'password' => '',     // uncomment if needed
];
```

#

### Parser

Create file `config/host.local.php` with url to this REST Api, for example: 

```php
return 'http://car.rest';
```

#### Commands for run parsing:

```
./yii parse {{parser_name}}
./yii parse {{parser_name}} {{max_pages}}
```

`{{parser_name}}` is parser name. For example - `am_ru`

`{{max_pages}}` maximum parsing pages. Optional. 


##### For example:
```
./yii parse am_ru 10
```
_* Parsing am.ru, maximum 10 pages_


#

### Tests

Command for run unit tests:
```
./vendor/bin/codecept run
```

#
*Author - [vitprog@gmail.com](mailto:vitprog@gmail.com)*