<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 14:34
 */
class Table_CommonSyscache extends X6bbs_Table {
    public function __construct() {

        $this->_table = 'common_syscache';
        $this->_pk = 'cname';

        parent::__construct();
    }

}