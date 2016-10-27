<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 14:34
 */
class Table_CommonSyscache extends X6bbs_Table {
    private $_is_file_cache;
    
    public function __construct() {

        $this->_table = 'common_syscache';
        $this->_pk = 'cname';
        $this->_pre_cache_key = '';
        $this->_is_file_cache = get_global('config/cache/type') == 'file';
        $this->_allowmem = memory('check');

        parent::__construct();
    }

    public function fetch($cache_name, $force_from_db = false) {
        $data = $this->fetch_all(array($cache_name));
        return isset($data[$cache_name]) ? $data[$cache_name] : false;
    }
    public function fetch_all($cache_names, $force_from_db = false) {

        $data = array();
        $cache_names = is_array($cache_names) ? $cache_names : array($cache_names);
        if($this->_allowmem && !$force_from_db) {
            $data = memory('get', $cache_names);
            $new_array = $data !== false ? array_diff($cache_names, array_keys($data)) : $cache_names;
            if(empty($new_array)) {
                return $data;
            } else {
                $cache_names = $new_array;
            }
        }

        if($this->_is_file_cache && !$force_from_db) {
            $lost_caches = array();
            foreach($cache_names as $cache_name) {
                if(!@include_once(APPLICATION_PATH .'/public/data/cache/cache_'.$cache_name.'.php')) {
                    $lost_caches[] = $cache_name;
                } elseif($this->_allowmem) {
                    memory('set', $cache_name, $data[$cache_name]);
                }
            }
            if(!$lost_caches) {
                return $data;
            }
            $cache_names = $lost_caches;
            unset($lost_caches);
        }

        $query = DB::query('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('cname', $cache_names));
        while($sys_cache = DB::fetch($query)) {
            $data[$sys_cache['cname']] = $sys_cache['ctype'] ? unserialize($sys_cache['data']) : $sys_cache['data'];
            $this->_allowmem && (memory('set', $sys_cache['cname'], $data[$sys_cache['cname']]));
            if($this->_is_file_cache) {
                $cache_data = '$data[\''.$sys_cache['cname'].'\'] = '.var_export($data[$sys_cache['cname']], true).";\n\n";
                if(($fp = @fopen(APPLICATION_PATH.'/public/data/cache/cache_'.$sys_cache['cname'].'.php', 'wb'))) {
                    fwrite($fp, "<?php\n//X6bbs! cache file, DO NOT modify me!\n//Identify: ".md5($sys_cache['cname'].$cache_data.get_global('config/security/authkey'))."\n\n$cache_data?>");
                    fclose($fp);
                }
            }
        }

        foreach($cache_names as $name) {
            if($data[$name] === null) {
                $data[$name] = null;
                $this->_allowmem && (memory('set', $name, array()));
            }
        }

        return $data;
    }
}