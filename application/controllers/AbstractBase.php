<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
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

    public function render($tpl, array $parameters = null) {
        $this->_init_style();
        return parent::render($tpl, $parameters);
    }

    public function display($tpl, array $parameters = null) {
        $this->_init_style();
        parent::display($tpl, $parameters);
    }

    public function _init_style() {
        global $_G;
        static $_init_style = false;
        
        if($_init_style === false) {
            $style_id = 1;
            $cache_name = 'style_' . $style_id;
            load_cache($cache_name);
            if (isset($_G['style'][$cache_name])) {
                $_G['style'] = $_G['style'][$cache_name];
            }

            define('IMGDIR', $_G['style']['imgdir']);
            define('STYLEID', $_G['style']['styleid']);
            define('VERHASH', $_G['style']['verhash']);
            define('TPLDIR', $_G['style']['tpldir']);
            define('TEMPLATEID', $_G['style']['templateid']);

            $this->setViewpath(APPLICATION_PATH.'/application/views/' . TPLDIR);
            $_init_style = true;
        }
    }

    public function _init(){}
}