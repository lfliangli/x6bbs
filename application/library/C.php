<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 16:06
 */
class C {
    private static $_tables;
    private static $_memory;

    /**
     * @param $name
     * @return X6php_Table
     */
    public static function t($name) {
        $name = preg_replace_callback('/_([A-Za-z])/',function($m){ return strtoupper($m[1]);}, '_'.$name);
        $name = 'Table_' . $name;
        return self::_make_obj($name);
    }

    public static function m($name) {
        $name = ucfirst($name) . 'Model';
        return self::_make_obj($name);
    }

    public static function p($name) {
        $name = ucfirst($name) . 'Plugin';
        return self::_make_obj($name);
    }

    protected static function _make_obj($name) {
        if(!isset(self::$_tables[$name])) {
            self::$_tables[$name] = new $name();
        }
        
        return self::$_tables[$name];
    }

    public static function memory() {
        if(!self::$_memory) {
            self::$_memory = new X6php_Memory();
            self::$_memory->init(get_config('memory'));
        }
        return self::$_memory;
    }
}