<?php

namespace Tests;

\Sinevia\Registry::set("ENVIRONMENT", 'testing');
loadEnvConf(\Sinevia\Registry::get("ENVIRONMENT"));
eloquent(); // Reload DB

abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected $db = null;

    function setUp(): void
    {
        parent::setUp();
        /* Example database setup */
        \Sinevia\Registry::set("DB_TYPE", "sqlite");
        \Sinevia\Registry::set("DB_HOST", ":memory:");
        \Sinevia\Registry::set("DB_NAME", "");
        \Sinevia\Registry::set("DB_USER", "test");
        \Sinevia\Registry::set("DB_PASS", "");
        $this->db = \db(false);
        eloquent(false); // refresh

        /* Example migrations */
        \Sinevia\Migrate::setDirectoryMigration(\Sinevia\Registry::get('DIR_MIGRATIONS'));
        \Sinevia\Migrate::setDatabase($this->db);
        \Sinevia\Migrate::$verbose = false;
        \Sinevia\Migrate::up();
    }

    protected function get($path, $data = [])
    {
        $query = parse_url($path, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $vars);
            $data = array_merge($vars, $data);
        }
        $_REQUEST = $data;
        $_SERVER['HTTP_HOST'] = 'NONE';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $path;
        return main();
    }

    protected function post($path, $data = [])
    {
        $_REQUEST = $data;
        $_SERVER['HTTP_HOST'] = 'NONE';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = $path;
        return main();
    }
}