<?php
/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 15:55
 */

function set_global($key , $value, $group = null) {
    global $_G;
    $key = explode('/', $group === null ? $key : $group.'/'.$key);
    $p = &$_G;
    foreach ($key as $k) {
        if(!isset($p[$k]) || !is_array($p[$k])) {
            $p[$k] = array();
        }
        $p = &$p[$k];
    }
    $p = $value;
    return true;
}

function get_global($key, $group = null) {
    global $_G;
    $key = explode('/', $group === null ? $key : $group.'/'.$key);
    $v = &$_G;
    foreach ($key as $k) {
        if (!isset($v[$k])) {
            return null;
        }
        $v = &$v[$k];
    }
    return $v;
}

function get_config($key = ''){
    $key = trim($key);
    if($key) {
        return Yaf_Registry::get("config")->get($key);
    }else{
        return Yaf_Registry::get("config")->get();
    }
}

function memory($cmd, $key='', $value='', $ttl = 0, $prefix = '') {
    if($cmd == 'check') {
        return  C::memory()->enable ? C::memory()->type : '';
    } elseif(C::memory()->enable && in_array($cmd, array('set', 'get', 'rm', 'inc', 'dec'))) {
        if(get_config('debug')) {
            if(is_array($key)) {
                foreach($key as $k) {
                    C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '').$prefix.$k;
                }
            } else {
                C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '').$prefix.$key;
            }
        }
        switch ($cmd) {
            case 'set': return C::memory()->set($key, $value, $ttl, $prefix); break;
            case 'get': return C::memory()->get($key, $value); break;
            case 'rm': return C::memory()->rm($key, $value); break;
            case 'inc': return C::memory()->inc($key, $value ? $value : 1); break;
            case 'dec': return C::memory()->dec($key, $value ? $value : -1); break;
        }
    }
    return null;
}


function dintval($int, $allow_array = false) {
    $ret = intval($int);
    if($int == $ret || !$allow_array && is_array($int)) return $ret;
    if($allow_array && is_array($int)) {
        foreach($int as &$v) {
            $v = dintval($v, true);
        }
        return $int;
    } elseif($int <= 0xffffffff) {
        $l = strlen($int);
        $m = substr($int, 0, 1) == '-' ? 1 : 0;
        if(($l - $m) === strspn($int,'0987654321', $m)) {
            return $int;
        }
    }
    return $ret;
}

function load_cache($cache_names, $force = false) {
    global $_G;
    static $loaded_cache = array();
    $cache_names = is_array($cache_names) ? $cache_names : array($cache_names);
    $caches = array();
    foreach ($cache_names as $k) {
        if(!isset($loaded_cache[$k]) || $force) {
            $caches[] = $k;
            $loaded_cache[$k] = true;
        }
    }

    if(!empty($caches)) {
        $cache_data = C::t('common_syscache')->fetch_all($caches);
        foreach($cache_data as $cname => $data) {
            if($cname == 'setting') {
                $_G['setting'] = $data;
            } elseif($cname == 'usergroup_'.$_G['groupid']) {
                $_G['cache'][$cname] = $_G['group'] = $data;
            } elseif($cname == 'style_default') {
                $_G['cache'][$cname] = $_G['style'] = $data;
            } elseif($cname == 'grouplevels') {
                $_G['grouplevels'] = $data;
            } else {
                $_G['cache'][$cname] = $data;
            }
        }
    }
    return true;
}