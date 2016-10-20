<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
 * Date: 2016/10/20 0020
 * Time: 10:11
 */
class SuperBaseController extends Yaf_Controller_Abstract {
    public $init_db = true;

    public function init() {
        defined('CURSCRIPT') || define('CURSCRIPT', 'forum');
        defined('CURMODULE') || define('CURMODULE', $this->getRequest()->action);
        $this->setViewpath(APPLICATION_PATH.'/application/views/default');

        global $_G;
        $this->_init_db();
        
        $this->_init();
    }

    private function _init_db() {
        if($this->init_db) {
            $driver = function_exists('mysql_connect') ? 'Db_MysqlDriver' : 'Db_MysqliDriver';
            if(get_config('db.slave')) {
                $driver = function_exists('mysql_connect') ? 'Db_MysqlSlaveDriver' : 'Db_MysqliSlaveDriver';
            }
            DB::init($driver, get_config('db'));
        }
    }

    public function _init(){}
}