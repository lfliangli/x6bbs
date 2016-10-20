<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/10/20 0020
 * Time: 18:34
 */
class Driver_Mysql {
    protected $tablepre;
    protected $version = '';
    protected $drivertype = 'mysql';
    protected $querynum = 0;
    protected $slaveid = 0;
    protected $curlink;
    protected $link;
    protected $config = array();
    protected $sqldebug = array();

    function db_mysql($config) {
        if (!empty($config)) {
            $this->set_config($config);
        }
    }

    function set_config($config) {
        $this->config = $config;
        $this->tablepre = $config->tablepre;
    }

    function connect() {
        if (empty($this->config)) {
            $this->halt('config_db_not_found');
        }

        $this->curlink = $this->link = $this->_dbconnect(
            $this->config->hostname,
            $this->config->username,
            $this->config->password,
            $this->config->charset,
            $this->config->database,
            $this->config->pconnect
        );
    }

    function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $halt = true) {
        if(!function_exists('mysql_connect')){
            throw new Exception('mysql_connect(): The mysql extension is removed, use mysqli or PDO instead ');
        }

        if ($pconnect) {
            $link = @mysql_pconnect($dbhost, $dbuser, $dbpw, MYSQL_CLIENT_COMPRESS);
        } else {
            $link = @mysql_connect($dbhost, $dbuser, $dbpw, 1, MYSQL_CLIENT_COMPRESS);
        }
        if (!$link) {
            $halt && $this->halt('notconnect', $this->errno());
        } else {
            $this->curlink = $link;
            if ($this->version() > '4.1') {
                $dbcharset = $dbcharset ? $dbcharset : $this->config->charset;
                $serverset = $dbcharset ? 'character_set_connection=' . $dbcharset . ', character_set_results=' . $dbcharset . ', character_set_client=binary' : '';
                $serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',') . 'sql_mode=\'\'') : '';
                $serverset && mysql_query("SET $serverset", $link);
            }
            $dbname && @mysql_select_db($dbname, $link);
        }
        return $link;
    }

    function table_name($tablename) {
        $this->curlink = $this->link;
        return $this->tablepre . $tablename;
    }

    function select_db($dbname) {
        return mysql_select_db($dbname, $this->curlink);
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        if ($result_type == 'MYSQL_ASSOC') $result_type = MYSQL_ASSOC;
        return mysql_fetch_array($query, $result_type);
    }

    function fetch_first($sql) {
        return $this->fetch_array($this->query($sql));
    }

    function result_first($sql) {
        return $this->result($this->query($sql), 0);
    }

    public function query($sql, $silent = false, $unbuffered = false) {
        if (get_config('debug')) {
            $starttime = microtime(true);
        }

        if ('UNBUFFERED' === $silent) {
            $silent = false;
            $unbuffered = true;
        } elseif ('SILENT' === $silent) {
            $silent = true;
            $unbuffered = false;
        }

        $func = $unbuffered ? 'mysql_unbuffered_query' : 'mysql_query';

        if (!($query = $func($sql, $this->curlink))) {
            if (in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
                $this->connect();
                return $this->query($sql, 'RETRY' . $silent);
            }
            if (!$silent) {
                $this->halt($this->error(), $this->errno(), $sql);
            }
        }

        if (get_config('debug')) {
            $this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
        }

        $this->querynum++;
        return $query;
    }

    function affected_rows() {
        return mysql_affected_rows($this->curlink);
    }

    function error() {
        return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
    }

    function errno() {
        return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
    }

    function result($query, $row = 0) {
        $query = @mysql_result($query, $row);
        return $query;
    }

    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insert_id() {
        return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    function fetch_fields($query) {
        return mysql_fetch_field($query);
    }

    function version() {
        if (empty($this->version)) {
            $this->version = mysql_get_server_info($this->curlink);
        }
        return $this->version;
    }

    function escape_string($str) {
        return mysql_escape_string($str);
    }

    function close() {
        return mysql_close($this->curlink);
    }

    function halt($message = '', $code = 0, $sql = '') {
        throw new DbException($message, $code, $sql);
    }

}