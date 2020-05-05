<?php

namespace PHPServerless;


//============================= START OF CLASS ==============================//
// CLASS: Registry                                                           //
//===========================================================================//
/**
 * The Registry class provides a safe implementation of global registry.
 * This acts like an alternative of the the global variable $GLOBALS, which
 * references all variables available in global scope, but can be already
 * being used by anothe library.
 * <code>
 * Registry::set('admin_email','admin@domain.com');
 * 
 * if (Registry::has('admin_email')) {
 *     echo Registry::get('admin_email');
 * }
 * 
 * Registry::remove('admin_email');
 * </code>
 */
class Registry {

    private static $registry = array();

//private final function __construct(){}

    public static function clear() {
        self::$registry = array();
    }

    public static function get(string $key, $default = null) {
        if (self::has($key)) {
            return self::$registry[$key];
        }
        return $default;
    }

    public static function has(string $key) {
        return (isset(self::$registry[$key])) ? true : false;
    }

    /**
     * Checks if a key equals the given value
     * @return boolean
     */
    public static function equals(string $key, $value) {
        $currentValue = self::get($key);
        return ($currentValue === $value) ? true : false;
    }

    /**
     * Checks if a key equals any the given values
     * @return boolean
     */
    public static function equalsAny(string $key, array $values) {
        $currentValue = self::get($key);
        foreach ($values as $value) {
            if ($currentValue === $value) {
                return true;
            }
        }
        return false;
    }

    public static function remove(string $key) {
        if (self::has($key) == true) {
            unset(self::$registry[$key]);
        }
    }

    public static function set(string $key, $value) {
        self::$registry[$key] = $value;
    }

    /**
     * Sets a key only if it does not exist
     * @return boolean
     */
    public static function setIfNotExists(string $key, $value) {
        if (self::has($key)) {
            return;
        }
        self::$registry[$key] = $value;
    }

    public static function fromArray(array $array) {
        self::$registry = $array;
    }

    public static function mergeArray(array $array) {
        self::$registry = array_merge(self::$registry, $array);
    }

    public static function toArray() {
        return self::$registry;
    }

}

//===========================================================================//
// CLASS: Registry                                                           //
//============================== END OF CLASS ===============================//