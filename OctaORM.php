<?php
class OctaORM{

    protected $redbean;
    protected $db_field;

    var $last_id;
    var $last_query;

    var $get;
    var $select;
    var $where;
    var $or_where;
    var $order_by;
    var $join;
    var $group_by;
    var $table;
    var $limit;
    var $offset;
    var $where_in;
    var $or_where_in;
    var $where_not_in;
    var $or_where_not_in;
    var $like;
    var $or_like;
    var $not_like;
    var $or_not_like;
    var $row;
    var $num_rows;
    var $result;

    public function __construct($db){
        include_once('RedBean.php');
        $this->redbean = new R();
        $this->db_field = $db;

        if(!$this->redbean->testConnection()){
            $conn = $this->initiate_database_connection($db);
            $this->redbean->setup(/** @scrutinizer ignore-type */ $conn);
            $this->redbean->useFeatureSet( 'novice/latest' );
        }
    }

    public function initiate_database_connection($db){
        $DB_HOST = $db['hostname'];
        $DB_USERNAME = $db['username'];
        $DB_PASSWORD = $db['password'];
        $DB_NAME = $db['database'];

        $DB_con = new PDO("mysql:host=$DB_HOST", $DB_USERNAME, $DB_PASSWORD);
        $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $DB_con->exec("CREATE DATABASE IF NOT EXISTS $DB_NAME;");

        $DB_con = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}",$DB_USERNAME,$DB_PASSWORD);
        $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $DB_con;
    }

    public function insert_id(){
        return $this->last_id;
    }

    public function last_query(){
        return $this->last_query;
    }

    public function insert($array,$table){
        $data = $this->redbean->dispense($table);

        if($array){
            foreach($array as $key=>$row){
                $data->$key = $row;
            }
        }

        $this->last_id = $this->redbean->store($data);
        return ($this->last_id) ? true : false;
    }

    public function insert_batch($array,$table){
        $data = array();

        if($array){
            foreach($array as $key_ib=>$row_ib){

                $data[$key_ib]=$this->redbean->dispense($table);
                $table_fields = [];
                $table_fields_val = [];

                if($row_ib){
                    foreach($row_ib as $row=>$val){
                        $table_fields[] = $row;
                        $table_fields_val[] = $val;
                    }

                    if($table_fields && $table_fields_val){
                        foreach($table_fields as $key=>$row){
                            $data[$key_ib]->$row = $table_fields_val[$key];
                        }

                    }else{
                        return false;
                    }
                }
            }
        }

        $result = $this->redbean->storeAll($data);
        return ($result) ? true : false;
    }

    public function update($table,$array,$id){
        $data = $this->redbean->load($table, $id);

        if($array){
            foreach($array as $key=>$row){
                $data->$key = $row;
            }
        }

        $this->redbean->store($data);
        return true;
    }

    public function update_batch($table,$array,$where){
        if($array){
            foreach($array as $key_ub=>$row_ub){
                $where_key = '';
                $table_fields = [];
                $table_fields_val = [];

                if($row_ub){
                    foreach($row_ub as $row=>$val){

                        if($row === $where){
                            $where_key = $val;
                        }

                        if($row !== $where){
                            $table_fields[] = $row;
                            $table_fields_val[] = $val;
                        }
                    }

                    $data = $this->redbean->load($table, $where_key);
                    if($table_fields && $table_fields_val){
                        foreach($table_fields as $key=>$row){
                            $data->$row = $table_fields_val[$key];
                        }

                    }else{
                        return false;
                    }

                    $this->redbean->store($data);
                }
            }
        }

        return true;
    }

    public function delete($table,$id){
        if($id){
            if(is_array($id)){
                foreach($id as $key=>$row){
                    $this->redbean->trash($table,$row);
                }

                return true;

            }else{
                $this->redbean->trash($table,$id);
                return true;
            }
        }
    }

    public function delete_all($table){
        $result = $this->redbean->wipe($table);
        return ($result) ? true : false;
    }

    public function select($data){
        $reset = [];
        if($data){
            foreach($data as $key=>$row){
                array_push($reset,$row);
            }
        }

        $this->select = $reset;
        return $this->select;
    }

    public function where($data=null,$match=null){
        $tmp_where = '';
        $arr_check = false;
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);

                if($data){
                    $arr_check = true;
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_where .= $key."='".$row."'";
                        }else{
                            $tmp_where .= $key."='".$row."' AND ";
                        }
                    }
                }
            }else{
                $arr_check = false;
                $tmp_where = "WHERE ".$data."='".$match."'";
            }
        }

        $this->where = ($arr_check) ? "WHERE ".$tmp_where : $tmp_where;
    }

    public function or_where($data=null,$match=null){
        $tmp_or_where = '';
        $arr_check = false;
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);
                $arr_check = true;

                if($data){
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_or_where .= $key."='".$row."'";
                        }else{
                            $tmp_or_where .= $key."='".$row."' AND ";
                        }
                    }
                }
            }else{
                $arr_check = false;
                $tmp_or_where = "OR ".$data."='".$match."'";
            }
        }

        $this->or_where = ($arr_check) ? "OR ".$tmp_or_where : $tmp_or_where;
    }

    public function where_in($field,$data){
        $where_in_fields = '';
        $last_key = end(array_keys($data));
        if($data){
            foreach($data as $key=>$row){
                if($key == $last_key){
                    $where_in_fields .= $row;
                }else{
                    $where_in_fields .= $row.",";
                }
            }
        }

        $this->where_in = 'WHERE '.$field.' IN ('.$where_in_fields.')';
    }

    public function or_where_in($field,$data){
        $where_in_fields = '';
        $last_key = end(array_keys($data));
        if($data){
            foreach($data as $key=>$row){
                if($key == $last_key){
                    $where_in_fields .= $row;
                }else{
                    $where_in_fields .= $row.",";
                }
            }
        }

        $this->or_where_in = 'OR '.$field.' IN ('.$where_in_fields.')';
    }

    public function where_not_in($field,$data){
        $where_in_fields = '';
        $last_key = end(array_keys($data));
        if($data){
            foreach($data as $key=>$row){
                if($key == $last_key){
                    $where_in_fields .= $row;
                }else{
                    $where_in_fields .= $row.",";
                }
            }
        }

        $this->where_not_in = 'WHERE '.$field.' NOT IN ('.$where_in_fields.')';
    }

    public function or_where_not_in($field,$data){
        $where_in_fields = '';
        $last_key = end(array_keys($data));
        if($data){
            foreach($data as $key=>$row){
                if($key == $last_key){
                    $where_in_fields .= $row;
                }else{
                    $where_in_fields .= $row.",";
                }
            }
        }

        $this->or_where_not_in = 'OR '.$field.' NOT IN ('.$where_in_fields.')';
    }

    public function like($data=null,$match=null){
        $tmp_like = '';
        $arr_check = false;
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);

                if($data){
                    $arr_check = true;
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_like .= $key." LIKE '%".$row."%'";
                        }else{
                            $tmp_like .= $key." LIKE '%".$row."%' AND ";
                        }
                    }
                }
            }else{
                $arr_check = false;
                $tmp_like = "WHERE ".$data." LIKE '%".$match."%'";
            }

        }

        $this->like = ($arr_check) ? $tmp_like : "WHERE ".$tmp_like;
    }

    public function or_like($data=null,$match=null){
        $tmp_or_like = '';
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);

                if($data){
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_or_like .= "OR ".$key." LIKE '%".$row."%'";
                        }else{
                            $tmp_or_like .= "OR ".$key." LIKE '%".$row."%' AND ";
                        }
                    }
                }
            }else{
                $tmp_or_like = "OR ".$data." LIKE '%".$match."%'";
            }
        }

        $this->or_like = $tmp_or_like;
    }

    public function not_like($data=null,$match=null){
        $tmp_like = '';
        $arr_check = false;
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);

                if($data){
                    $arr_check = true;
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_like .= $key." NOT LIKE '%".$row."%'";
                        }else{
                            $tmp_like .= $key." NOT LIKE '%".$row."%' AND ";
                        }
                    }
                }
            }else{
                $arr_check = false;
                $tmp_like = "WHERE ".$data." NOT LIKE '%".$match."%'";
            }

        }

        $this->like = ($arr_check) ? "WHERE ".$tmp_like : $tmp_like;
    }

    public function or_not_like($data=null,$match=null){
        $tmp_or_like = '';
        if($data){
            if(is_array($data)){
                end($data);
                $last_element = key($data);

                if($data){
                    foreach($data as $key=>$row){
                        if($key == $last_element){
                            $tmp_or_like .= "OR ".$key." NOT LIKE '%".$row."%'";
                        }else{
                            $tmp_or_like .= "OR ".$key." NOT LIKE '%".$row."%' AND ";
                        }
                    }
                }
            }else{
                $tmp_or_like = "OR ".$data." NOT LIKE '%".$match."%'";
            }
        }

        $this->or_like = $tmp_or_like;
    }

    public function order_by($field,$sort){
        $this->order_by = $field.' '.$sort;
    }

    public function join($table,$joint,$join_type="INNER"){
        $this->join .= ($this->join !== "" && $this->join !== null) ? ' '.$join_type.' JOIN '.$table.' ON '.$joint : $join_type.' JOIN '.$table.' ON '.$joint;
    }

    public function group_by($field){
        $this->group_by = 'GROUP BY '.$field;
    }

    public function get($table=null,$limit=null,$offset=null){
        $this->table = $table;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function clear_global_var(){
        $this->select = '';
        $this->where = '';
        $this->or_where = '';
        $this->order_by = '';
        $this->join = '';
        $this->group_by = '';
        $this->limit = '';
        $this->table = '';
        $this->offset = '';
        $this->where_in = '';
        $this->or_where_in = '';
        $this->where_not_in = '';
        $this->or_where_not_in = '';
        $this->like = '';
        $this->or_like = '';
        $this->not_like = '';
        $this->or_not_like = '';
        $this->row = '';
        $this->num_rows = '';
        $this->result = '';
    }

    public function row(){
        /*init var*/
        $result = [];
        $select = ($this->select) ? implode(',',$this->select) : '*';
        $order_by = ($this->order_by) ? 'ORDER BY '.$this->order_by : '';
        $join = ($this->join) ? $this->join : '';
        $group_by = ($this->group_by) ? $this->group_by : '';
        $limit = ($this->limit) ? 'LIMIT '.$this->limit : '';
        $offset = ($this->offset) ? 'OFFSET '.$this->offset : '';
        $table = ($this->table) ? $this->table : '';

        $where = ($this->where) ? $this->where : '';
        $or_where = ($this->or_where) ? $this->or_where : '';
        $where_in = ($this->where_in) ? $this->where_in : '';
        $or_where_in = ($this->or_where_in) ? $this->or_where_in : '';
        $where_not_in = ($this->where_not_in) ? $this->where_not_in : '';
        $or_where_not_in = ($this->or_where_not_in) ? $this->or_where_not_in : '';

        $like = ($this->like) ? $this->like : '';
        $or_like = ($this->or_like) ? $this->or_like : '';
        $not_like = ($this->not_like) ? $this->not_like : '';
        $or_not_like = ($this->or_not_like) ? $this->or_not_like : '';
        /*end init var*/

        $this->row = "SELECT {$select} FROM {$table} {$join} {$where} {$or_where} {$where_in} {$or_where_in} {$where_not_in} {$or_where_not_in} {$like} {$or_like} {$not_like} {$or_not_like} {$group_by} {$order_by} {$limit} {$offset}";
        $this->last_query = $this->row;
        $data = $this->redbean->getRow($this->row);
        if($data){
            foreach($data as $key=>$row){
                $result[$key] = $row;
            }
        }

        /*clear global variables*/
        $this->clear_global_var();
        /*end clearing global variables*/

        return $result;
    }

    public function num_rows(){
        /*init var*/
        $result = 0;#array();
        $select = ($this->select) ? implode(',',$this->select) : '*';
        $order_by = ($this->order_by) ? 'ORDER BY '.$this->order_by : '';
        $join = ($this->join) ? $this->join : '';
        $group_by = ($this->group_by) ? $this->group_by : '';
        $limit = ($this->limit) ? 'LIMIT '.$this->limit : '';
        $offset = ($this->offset) ? 'OFFSET '.$this->offset : '';
        $table = ($this->table) ? $this->table : '';

        $where = ($this->where) ? $this->where : '';
        $or_where = ($this->or_where) ? $this->or_where : '';
        $where_in = ($this->where_in) ? $this->where_in : '';
        $or_where_in = ($this->or_where_in) ? $this->or_where_in : '';
        $where_not_in = ($this->where_not_in) ? $this->where_not_in : '';
        $or_where_not_in = ($this->or_where_not_in) ? $this->or_where_not_in : '';

        $like = ($this->like) ? $this->like : '';
        $or_like = ($this->or_like) ? $this->or_like : '';
        $not_like = ($this->not_like) ? $this->not_like : '';
        $or_not_like = ($this->or_not_like) ? $this->or_not_like : '';
        /*end init var*/

        $this->num_rows = "SELECT {$select} FROM {$table} {$join} {$where} {$or_where} {$where_in} {$or_where_in} {$where_not_in} {$or_where_not_in} {$like} {$or_like} {$not_like} {$or_not_like} {$group_by} {$order_by} {$limit} {$offset}";
        $this->last_query = $this->num_rows;
        $data = $this->redbean->exec($this->num_rows);
        $result = $result + $data;

        /*clear global variables*/
        $this->clear_global_var();
        /*end clearing global variables*/

        return $result;
    }

    public function result(){
        /*init var*/
        $result = array();
        $select = ($this->select) ? implode(',',$this->select) : '*';
        $order_by = ($this->order_by) ? 'ORDER BY '.$this->order_by : '';
        $join = ($this->join) ? $this->join : '';
        $group_by = ($this->group_by) ? $this->group_by : '';
        $limit = ($this->limit) ? 'LIMIT '.$this->limit : '';
        $offset = ($this->offset) ? 'OFFSET '.$this->offset : '';
        $table = ($this->table) ? $this->table : '';

        $where = ($this->where) ? $this->where : '';
        $or_where = ($this->or_where) ? $this->or_where : '';
        $where_in = ($this->where_in) ? $this->where_in : '';
        $or_where_in = ($this->or_where_in) ? $this->or_where_in : '';
        $where_not_in = ($this->where_not_in) ? $this->where_not_in : '';
        $or_where_not_in = ($this->or_where_not_in) ? $this->or_where_not_in : '';

        $like = ($this->like) ? $this->like : '';
        $or_like = ($this->or_like) ? $this->or_like : '';
        $not_like = ($this->not_like) ? $this->not_like : '';
        $or_not_like = ($this->or_not_like) ? $this->or_not_like : '';
        /*end init var*/

        #$this->get($table,$limit,$offset);

        $this->result = "SELECT {$select} FROM {$table} {$join} {$where} {$or_where} {$where_in} {$or_where_in} {$where_not_in} {$or_where_not_in} {$like} {$or_like} {$not_like} {$or_not_like} {$group_by} {$order_by} {$limit} {$offset}";
        $this->last_query = $this->result;
        $data = $this->redbean->getAll($this->result);
        if($data){
            foreach($data as $key=>$row){
                $result[$key] = (object)$row;
            }
        }

        /*clear global variables*/
        $this->clear_global_var();
        /*end clearing global variables*/

        return $result;
    }

    /*Database*/
    public function wipe($beanType){
        $result = $this->redbean->wipe($beanType);
        return ($result) ? $result : false;
    }

    public function dispense($data){
        $result = $this->redbean->dispense($data);
        return ($result) ? true : false;
    }

    public function store($data){
        $result = $this->redbean->store($data);
        return ($result) ? true : false;
    }

    public function inspect($data){
        if($data){
            if(is_array($data)){

                $result = array();
                foreach($data as $key=>$row){
                    $result[$key]=$this->redbean->inspect($row);
                }

                return ($result) ? $result : false;

            }else{
                $this->redbean->inspect($data);
                return true;
            }
        }
    }

    public function get_all_tables(){
        $result = $this->redbean->inspect();
        return ($result) ? $result : false;
    }

    public function select_database($db_name){
        $result = $this->redbean->selectDatabase($db_name);
        return ($result) ? true : false;
    }

    public function set_database(){
        $this->redbean->selectDatabase('default');
        return true;
    }

    public function begin(){
        $result = $this->redbean->begin();
        return ($result) ? true : false;
    }

    public function commit(){
        $result = $this->redbean->commit();
        return ($result) ? true : false;
    }

    public function roll_back(){
        $result = $this->redbean->rollback();
        return ($result) ? true : false;
    }

    public function get_query_count(){
        $this->redbean->getQueryCount();
        return true;
    }

    public function get_logs(){
        $this->redbean->getLogs();
        return true;
    }

    /*querying*/
    public function exec($data){
        $this->redbean->exec($data);
        return true;
    }

    public function get_all($table){
        $result = $this->redbean->getAll($table);
        return ($result) ? $result : false;
    }

    public function get_row($data){
        $result = $this->redbean->getRow($data);
        return ($result) ? $result : false;
    }

    public function get_column($data){
        $result = $this->redbean->getCol($data);
        return ($result) ? $result : false;
    }

    public function get_cell($data){
        $result = $this->redbean->getCell($data);
        return ($result) ? $result : false;
    }

    public function get_assoc($data){
        $result = $this->redbean->getAssoc($data);
        return ($result) ? $result : false;
    }

    public function get_inserted_id(){
        $result = $this->redbean->getInsertID();
        return ($result) ? $result : false;
    }

    public function convert_to_beans($type,$rows,$metamask=null){
        $result = $this->redbean->convertToBeans( $type, $rows, $metamask );
        return ($result) ? $result : false;
    }

    /*Data Tools*/
    public function match_up($type, $sql, $bindings = array(), $onFoundDo = NULL, $onNotFoundDo = NULL, &$bean = NULL){
        $result = $this->redbean->matchUp($type, $sql, $bindings, $onFoundDo, $onNotFoundDo, $bean);
        return ($result) ? $result : false;
    }

    public function find_all($table){
        $this->redbean->findAll($table);
        return true;
    }

    public function find($type,$sql,$bindings){
        $result = $this->redbean->find($type,$sql,$bindings);
        return ($result) ? $result : false;
    }

    /*Fluid and Frozen*/
    public function freeze($data=null){
        if($data){
            $data = array();
            foreach($data as $key=>$row){
                $data[$key] = $row;
            }

            $this->redbean->freeze($data);
        }else{
            $this->redbean->freeze(TRUE);
        }
        return true;
    }

    /*Debugging*/
    /*value "true", "false"*/
    public function debug($tf = TRUE, $mode = 0){
        $this->redbean->debug($tf,$mode);
        return true;
    }

    public function dump($data){
        $this->redbean->dump($data);
        return true;
    }

    public function test_connection(){
        $this->redbean->testConnection();
        return true;
    }

    /*Aliases*/
    public function load($oodb, $types, $id){
        $this->redbean->load($oodb, $types, $id);
        return true;
    }

    /*Count*/
    public function count($type, $addSQL = '', $bindings = array()){
        $result = $this->redbean->count($type,$addSQL,$bindings);
        return ($result) ? $result : false;
    }

    /*Labels, Enums, Tags*/
    public function dispense_labels($type,$labels){
        $result = $this->redbean->dispenseLabels($type,$labels);
        return ($result) ? $result : false;
    }

    public function gather_labels($beans){
        $result = $this->redbean->gatherLabels($beans);
        return ($result) ? $result : false;
    }

    public function enum($enum){
        $result = $this->redbean->enum($enum);
        return ($result) ? $result : false;
    }

    public function tag($bean, $tagList){
        $result = $this->redbean->tag($bean, $tagList);
        return ($result) ? $result : false;
    }
}