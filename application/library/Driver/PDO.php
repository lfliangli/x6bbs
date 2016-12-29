<?php

/**
 * Created by PhpStorm.
 * User: lfliang
 * Date: 2016/12/29
 * Time: 12:12
 */
class Driver_PDO {
    var $tablepre;
    var $version = '';
    var $drivertype = 'PDO';
    var $querynum = 0;
    var $affected_rows = 0;
    var $slaveid = 0;
    /**
     * @var PDO
     */
    var $curlink;
    var $link = array();
    var $config = array();
    var $sqldebug = array();
    var $map = array();

    function db_pdo($config = array()) {
        if(!empty($config)) {
            $this->set_config($config);
        }
    }

    function set_config($config) {
        $this->config = &$config;
        $this->tablepre = $config['1']['tablepre'];
        if(!empty($this->config['map'])) {
            $this->map = $this->config['map'];
            for($i = 1; $i <= 100; $i++) {
                if(isset($this->map['forum_thread'])) {
                    $this->map['forum_thread_'.$i] = $this->map['forum_thread'];
                }
                if(isset($this->map['forum_post'])) {
                    $this->map['forum_post_'.$i] = $this->map['forum_post'];
                }
                if(isset($this->map['forum_attachment']) && $i <= 10) {
                    $this->map['forum_attachment_'.($i-1)] = $this->map['forum_attachment'];
                }
            }
            if(isset($this->map['common_member'])) {
                $this->map['common_member_archive'] =
                $this->map['common_member_count'] = $this->map['common_member_count_archive'] =
                $this->map['common_member_status'] = $this->map['common_member_status_archive'] =
                $this->map['common_member_profile'] = $this->map['common_member_profile_archive'] =
                $this->map['common_member_field_forum'] = $this->map['common_member_field_forum_archive'] =
                $this->map['common_member_field_home'] = $this->map['common_member_field_home_archive'] =
                $this->map['common_member_validate'] = $this->map['common_member_verify'] =
                $this->map['common_member_verify_info'] = $this->map['common_member'];
            }
        }
    }

    function connect($serverid = 1) {
        $config_1 = $this->config[$serverid];
        if(empty($this->config) || empty($config_1)) {
            $this->halt('config_db_not_found');
        }

        $this->link[$serverid] = $this->_dbconnect(
            $this->config[$serverid]['dbhost'],
            $this->config[$serverid]['dbuser'],
            $this->config[$serverid]['dbpw'],
            $this->config[$serverid]['dbcharset'],
            $this->config[$serverid]['dbname']
        );
        $this->curlink = $this->link[$serverid];

    }

    function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $halt = true) {
        try {
            $dsn = 'mysql:host=' . $dbhost . ';dbname=' . $dbname . ';charset=' . $dbcharset;
            $this->curlink = new PDO($dsn, $dbuser, $dbpw);
        } catch (PDOException $e) {
            $halt && $this->halt('notconnect', $this->errno());
        }

        return $this->curlink;
    }

    function table_name($tablename) {
        if(!empty($this->map) && !empty($this->map[$tablename])) {
            $id = $this->map[$tablename];
            if(!$this->link[$id]) {
                $this->connect($id);
            }
            $this->curlink = $this->link[$id];
        } else {
            $this->curlink = $this->link[1];
        }
        return $this->tablepre.$tablename;
    }

    function select_db() {
        return true;
    }

    /**
     * @param $query PDOStatement
     * @param int $result_type
     * @return null|array
     */
    function fetch_array($query, $result_type = PDO::FETCH_ASSOC) {
        if($result_type == 'MYSQL_ASSOC') $result_type = PDO::FETCH_ASSOC;
        return $query ? $query->fetchAll($result_type) : null;
    }

    function fetch_first($sql) {
        $query = $this->query($sql);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    function result_first($sql) {
        return $this->result($this->query($sql), 0);
    }

    public function query($sql, $silent = false, $unbuffered = false) {
        $starttime = null;

        if(get_config('debug')) {
            $starttime = microtime(true);
        }

        if('UNBUFFERED' === $silent) {
            $silent = false;
            $unbuffered = true;
        } elseif('SILENT' === $silent) {
            $silent = true;
            $unbuffered = false;
        }

        $resultmode = $unbuffered ? PDO::ATTR_DEFAULT_FETCH_MODE : PDO::MYSQL_ATTR_USE_BUFFERED_QUERY;

        $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
        if ($cmd === 'UPDATE' || $cmd === 'DELETE' || $cmd === 'INSERT') {
            $this->affected_rows = $query = $this->curlink->exec($sql);
        } else {
            if (!($query = $this->curlink->query($sql, $resultmode))) {
                if (in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
                    $this->connect();

                    return $this->curlink->query($sql);
                }
                if (!$silent) {
                    $this->halt($this->error(), $this->errno(), $sql);
                }
            }
        }

        if(get_config('debug')) {
            $this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
        }

        $this->querynum++;
        return $query;
    }

    function affected_rows() {
        return $this->affected_rows;
    }

    function error() {
        return $this->curlink->errorInfo();
    }

    function errno() {
        return $this->curlink->errorCode();
    }

    /**
     * @param $query PDOStatement
     * @param int $row
     * @return null
     */
    function result($query, $row = 0) {
        if(!$query || $query->rowCount() == 0) {
            return null;
        }

        return $query->fetchColumn($row);
    }

    /**
     * @param $query PDOStatement
     * @return int
     */
    function num_rows($query) {
        $query = $query ? $query->rowCount() : 0;
        return $query;
    }

    /**
     * @param $query PDOStatement
     * @return null
     */
    function num_fields($query) {
        return $query ? $query->columnCount() : null;
    }

    /**
     * @param $query PDOStatement
     * @return bool
     */
    function free_result($query) {
        $query && $query = null;
        return $query ? true : false;
    }

    function insert_id() {
        return ($id = $this->curlink->lastInsertId()) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     * @param $query PDOStatement
     * @return null
     */
    function fetch_row($query) {
        $query = $query ? $query->fetch(PDO::FETCH_NUM) : null;
        return $query;
    }

    /**
     * @param $query PDOStatement
     * @return null
     */
    function fetch_fields($query) {
        $fields = array();
        if ($query) {
            for ($i = 0; $i < $query->columnCount(); $i ++) {
                $fields[] = $query->getColumnMeta($i);
            }
        }

        return $fields ? $fields : null;
    }

    function version() {
        if(empty($this->version)) {
            $this->version = $this->curlink->getAttribute(PDO::ATTR_SERVER_VERSION);
        }
        return $this->version;
    }

    function escape_string($str) {
        return substr($this->curlink->quote($str), 1, -1);
    }

    function close() {
        return $this->curlink = null;
    }

    function halt($message = '', $code = 0, $sql = '') {
        throw new DbException($message, $code, $sql);
    }
}