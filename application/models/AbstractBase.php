<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 14:30
 */
abstract class AbstractBaseModel {
    /**
     * @var bool 是否初始化数据库
     */
    protected $init_db = true;

    /**
     * Common constructor.
     */
    private function __construct() {
        $this->_init_db();
    }

    /**
     * @return null|static
     */
    public static function getInstance() {
        static $_instance = NULL;
        if (empty($_instance)) {
            $_instance = new static();
        }
        return $_instance;
    }

    private function _init_db() {
        if ($this->init_db) {
            $driver = get_config('db.driver');
            $driver = 'Driver_' . ucfirst(strtolower($driver));
            if (get_config('db.slave')) {
                $driver .= 'Slave';
            }
            DB::init($driver, get_config('db'));
        }
    }

    /**
     * clone
     */
    private function __clone() {
    }
}