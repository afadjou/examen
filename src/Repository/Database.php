<?php

namespace App\Repository;

use PDO;

class Database
{
    private static PDO $_instance;

    private function __construct(string $dns, string $user, string $passwd) {
        static::$_instance= new PDO($dns, $user, $passwd);
        static::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Crée une instance de la BDD.
     * @return PDO
     */
    public static function create(): PDO
    {
        if (empty(static::$_instance)) {
            new self($_ENV['DATABASE_URL'], $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWD']);
        }

        return static::$_instance;
    }
}