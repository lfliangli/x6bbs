<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends AbstractBaseController {
    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/app/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction() {
        $query_string = $this->getRequest()->getBaseUri();
        if(!empty($query_string) && is_numeric($query_string)) {
            $default = '/forum';
        } else {
            $cache_domain = APPLICATION_PATH . '/data/sysdata/cache_domain.php';
            if (file_exists($cache_domain)) {
                include_once $cache_domain;
                if (empty($domain['defaultindex'])) {
                    $default = '/forum';
                } else {
                    $default = $domain['defaultindex'];
                }
            } else {
                $default = '/forum';
            }
        }

        $this->redirect($default);
    }
}
