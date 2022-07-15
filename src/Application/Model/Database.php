<?php

declare(strict_types=1);

namespace App\Application\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database {
 
    function __construct() {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $_ENV['HOST_DB'],
            'database' => $_ENV['DATABASE'],
            'username' => $_ENV['USER_DB'],
            'password' => $_ENV['PASS_DB'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        // Setup the Eloquent ORM… 
        $capsule->bootEloquent();
    }
}