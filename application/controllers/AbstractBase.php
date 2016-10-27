<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 10:11
 */
abstract class AbstractBaseController extends Yaf_Controller_Abstract {
    /**
     * init默认执行
     */
    public function init() {
        defined('CURMODULE') || define('CURMODULE', $this->getRequest()->action);

        $this->_init();
    }

    public function assign($name, $value) {
        $this->getView()->assign($name, $value);
    }

    public function render($tpl, array $parameters = null) {
        $this->_init_style($tpl);
        return parent::render($tpl, $parameters);
    }

    public function display($tpl, array $parameters = null) {
        $this->_init_style($tpl);
        parent::display($tpl, $parameters);
    }

    public function _init_style($tpl) {
        if($this->_name == 'Error' && $tpl == 'error'){
            $this->setViewpath(APPLICATION_VIEWS);
            return true;
        }
        
        $style_id = 1;
        $cache_name = 'style_' . $style_id;
        load_cache($cache_name);
        $_g_cache = get_global('cache/'. $cache_name);
        if (isset($_g_cache)) {
            set_global('style', $_g_cache);
        }

        $style = get_global('style');
        $tpl_dir = isset($style['tpldir']) ? str_replace('template/', '', $style['tpldir']) : 'default';
        $tpl_dir = '/'. trim($tpl_dir, '/');
        $tpl_dir = '/test';

        $file = APPLICATION_VIEWS . $tpl_dir . '/' . strtolower($this->_name) . '/' . $tpl . '.phtml';
        if(file_exists($file)) {
            set_global('script_path', APPLICATION_VIEWS . $tpl_dir);
            $this->setViewpath(APPLICATION_VIEWS . $tpl_dir);
        }else{
            set_global('script_path', APPLICATION_VIEWS . '/default');
            $this->setViewpath(APPLICATION_VIEWS . '/default');
        }
        
        return true;
    }

    public function _init(){}
}
