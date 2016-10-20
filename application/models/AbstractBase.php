<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
 * Date: 2016/10/20 0020
 * Time: 14:30
 */
abstract class AbstractBaseModel {
    /**
     * Common constructor.
     */
    private function __construct() {
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

    /**
     * clone
     */
    private function __clone() {
    }
}