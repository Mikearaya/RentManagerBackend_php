<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MY_Model extends CI_Model {

    var $message;
    var $unknown;
    var $id;
    var $existing_data;
    var $error_no;
    var $branch_temp = array();
    var $count = 0;
    var $no_num_row = false;
    var $no_ip;
    var $log=true;
    var $my_log=true;
    var $x_log=true;
    var $escape;
    var $user_info;
    var $employee_id;
    var $updated=false;
    var $department;
    var $copy;
    var $report_to;
    var $userName;
    public function __construct()
    {
        $this->user_info = $this->session->userdata("user_info");
        $this->employee_id = $this->user_info->employee_id ?? null;
        $this->department = $this->user_info->department_id ?? null;
        $this->report_to = $this->user_info->report_to ?? null;
        $this->userName=$this->user_info->username?? null;

    }

    public function noError() {
        $this->error_no = $this->db->_error_number();
        if ($this->error_no != 0) {
            $this->message = $this->db->_error_message();
            return false;
        }
        return TRUE;
    }

    function join_table($join) {
        foreach ($join as $key => $value) {
            $join_table = $key;
            $condition = '';
            $type = '';
            foreach ($value as $vkey => $vvalue) {
                if (is_array($vvalue)) {
                    if (isset($vvalue['join'])) {
                        $type = $vvalue['join'];
                        continue;
                    }
                    if (isset($vvalue[1]))
                        $con = $vvalue[0] . '=' . $vvalue[1];
                    else
                        $con = $vvalue[0];
                    if ($condition == '') {
                        $condition = $con;
                    } else {
                        $condition .= ' and ' . $con;
                    }
                } else if (is_numeric($vkey)) {
                    if ($condition != '')
                        $condition .= ' and ' . $vvalue;
                    else
                        $condition = $vvalue;
                }else if ($vkey == 'join') {
                    $type = $vvalue;
                }
            }
            $this->db->join($join_table, $condition, $type, $this->escape);
        }
    }

    function group_bys($groups) {
        if (is_array($groups)) {
            foreach ($groups as $value) {
                $this->db->group_by($value);
            }
        } else {
            $this->db->group_by($groups);
        }
    }

    function havings($having) {
        foreach ($having as $hkey => $hvalue) {
            $this->db->having($hkey, $hvalue);
        }
    }

    function searchs($search) {
        $search_this = null;
        foreach ($search as $key => $value) {
            if ($key === 'Search') {
                $search_this = $value;
            } elseif ($value !== '') {
                $this->db->where($key, $value, !is_numeric($key));
            } else {
                $this->db->where($key, null, $this->escape);
            }
        }
        return $search_this;
    }

    function search_all($table, $all_table, $search_this) {
        $my_where = '';
        if (is_array($table)) {
            foreach ($all_table as $key => $value) {
                if (strpos($key, ' as ') !== false) {
                    continue;
                }
                $like_statement =$this->search_all($key, $all_table, $search_this);
                $my_where =($my_where != '')? ($my_where . ' or ' . $like_statement):$like_statement;
            }
        } else {
            $fields = $this->db->field_data($table);
            foreach ($fields as $field) {
                $not_text = (strtolower($field->type) != 'varchar' && strtolower($field->type) != 'text');
                $is_text = (strtolower($field->type) == 'varchar' || strtolower($field->type) == 'text');
                if (($not_text && is_numeric($search_this)) || $is_text) {
                    $like_statement = "{$table}.{$field->name}  LIKE '%{$search_this}%'";
                    $my_where = ($my_where != '') ? ($my_where . ' or ' . $like_statement) : $like_statement;
                }
            }
        }

        return $my_where;
    }

    /**
     * This Function Compiles an insert string and runs the query
     * @param Array $data an associative array of insert values
     * @param String $table the table to insert data into
     * @param Boolean $batch setting to the data provided is list of record
     * @return boolean
     */
    function insert($data, $table, $batch = FALSE) {
        $user_info = $this->session->userdata("user_info");
        $ip = get_ip_address();
        if ($batch == TRUE) {
            foreach ($data as $key => $value) {
                $value = (array) $value;
                if (config_item('enable_log') && $this->my_log) {
                    $value['modified_by'] = $user_info->user_name;
                    $value['ip'] = $ip;
                }
                $data[$key] = $value;
            }
            $this->db->insert_batch($table, $data);
        } else {
            $data = (object) $data;
            if (config_item('enable_log') && $this->my_log) {
                $data->modified_by = $user_info->user_name;
                $data->ip = $ip;
            }
            $this->db->insert($table, $data);
        }
        $error_no = $this->db->_error_number();
        $this->error_no = $error_no;
        if ($error_no != 0) {
            if ($error_no == 1062) {
                $this->message = 'This Record is Duplicated';
                if (!$batch) {
                    unset($data->modified_by);
                    unset($data->ip);
                    $this->existing_data = $this->select($table, (array) $data);
                }
            } else {
                $this->message = $this->db->_error_message();
            }
            return false;
        } else {
            $this->id = $this->db->insert_id();
            return TRUE;
        }
    }


    function authenticate_id($employee_id) {
        $select=array('employee_id',"'$employee_id' as user_name",'sex',"'Self' as group_name",
            'department_id',
            'department_name as department','is_department_head',"'Self Service' as group_type",'group_no',
            'location','position as job_title',FULLNAME . ' as full_name');
        $table=array(
            'from'=>'employee_data',
            'join'=>array(
                'position'=>array('join'=>'left','position_id = position.id'),
                'department'=>array('join'=>'left','department_id = department.id'),
            ),
            'select'=>$select,
            'where'=>array(
                'employee_id'=>$employee_id,
                'employee_data.status'=>'Active',
            )
        );
        $result=  $this->get_record($table);
        if($result)
        {
            return  (object) $result;
        }
        else
        {
            return false;
        }

    }

    /**
     * This function check if the user password and user name is valid
     * @param String $user_name
     * @param String $password
     * @return boolean
     */
    function authenticate($user_name, $password) {
        $select=array('employee_data.employee_id','group_id','user_name','sex','group_name','department_id',
            'department_name as department','is_department_head','group_type','group_no',
            'location','position.position as job_title','report_to','last_login_time',FULLNAME . ' as full_name');
        $table=array(
            'from'=>'users',
            'join'=>array(
                'employee_data'=>array('users.employee_id=employee_data.employee_id'),
                'position'=>array('join'=>'left','position_id = position.id'),
                'department'=>array('join'=>'left','department_id = department.id'),
                'user_group'=>array('join'=>'left','users.group_id = user_group.id')
            ),
            'select'=>$select,
            'where'=>array(
                'user_name'=>$user_name,
                'password'=>sha1($password),
                'employee_data.status'=>'Active',
                'users.status'=>'Active'
            )
        );
        $result=  $this->get_record($table);
        if($result)
        {
            return  (object) $result;
        }
        else
        {
            return false;
        }

    }

    /**
     * This Function Compiles a delete string and runs the query
     * @param String $table the table to delete from
     * @param mixed $where  the where clause
     * @return bool
     */
    function delete($table, $where = null) {
        if($where)
        {
            $this->db->where($where);
        }
        else{
            $this->db->where(1, 1, false);
        }
        $this->db->delete($table);
        $error_no = $this->db->_error_number();
        if ($error_no) {
            if ($error_no == 1451) {
                $message = ('Cannot Delete Element. it Have Record on it');
            } else {
                $message = ($this->db->_error_message());
            }
            $this->message = $message;
            $this->error_no = $error_no;
            return false;
        }
        else
        {
            if(config_item('enable_log') && $this->my_log & $where)
            {
                foreach ($where as $key=>$value)
                {
                    $log_data = array('deleted_by' => $this->userName);
                    $criteria = [
                        'deleted_by is null'=>null,
                        'unique_id' => $value,
                        'table' => $table];
                    $this->my_log=false;
                    $this->update($log_data, 'log', $criteria);
                }

            }
        }
        return true;
    }

    function updateThis($key,$value,$table,$where='',$escape=false)
    {
        $user_info = $this->session->userdata("user_info");

        if (config_item('enable_log') && $this->my_log) {
            $this->db->set('modified_by',$user_info->user_name);
            $this->db->set('ip',get_ip_address());
        }
        $this->db->set($key,$value,$escape);
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } else if ($where != '') {
            $this->db->where($where);
        }
        $this->db->update($table);
        if($this->db->affected_rows())
        {
            $this->updated=true;
        }
        $error_no = $this->db->_error_number();
        if ($error_no != 0) {
            if ($error_no == 1062) {
                $this->message = 'This Record IS Duplicated';
            } else {
                $this->message = $this->db->_error_message();
            }
            return false;
        } else {
            return TRUE;
        }
    }

    /**
     * This Function Compiles an update string and runs the query
     * @param  Mixed $data an associative array of update values
     * @param  string $table the table to retrieve the results from
     * @param string $where $where the where clause
     * @return bool
     */
    function update($data, $table, $where = '') {
        $user_info = $this->session->userdata("user_info");
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        } else if ($where != '') {
            $this->db->where($where);
        }
        $data = (object) $data;
        if (config_item('enable_log') && $this->my_log) {
            $data->modified_by = $user_info->user_name;
            $data->ip = get_ip_address();
        }
        $this->db->update($table, $data);
        $error_no = $this->db->_error_number();
        if ($error_no != 0) {
            if ($error_no == 1062) {
                $this->message = 'This Record IS Duplicated';
            } else {
                $this->message = $this->db->_error_message();
            }
            exits($this->message);
            return false;
        } else {
            return TRUE;
        }
    }

    /**
     * This Function Compiles the select statement based on the other functions called and runs the query
     * @param String $table The Table Name
     * @param Array $criteria the where clause
     * @return Array
     */
    function get_by_filed($table, $criteria) {
        $this->db->from($table);
        if (is_array($criteria)) {
            foreach ($criteria as $key => $value) {
                $this->db->where($key, $value);
            }
        }
        return $this->db->get()
            ->result();
    }

    function get_resource($table) {
        return $this->select($table, '', null, 0, null, null, true);
    }

    function get_object($table, $criteria=null)
    {
        return $this->get_record($table,$criteria,true);
    }

    /**
     * @param $table
     * @param null $criteria
     * @param bool $object
     * @return array|null
     */
    function get_record($table, $criteria = null,$object=false) {
        $result = $this->select($table, $criteria,1);
        if (count($result['result']) > 0) {
            if($object)
            {
                return $result['result'][0];
            }
            else
            {
                return (array) $result['result'][0];
            }
        } else {
            return null;
        }
    }

    function _after_copy($data)
    {
        $action=isset($data['action'])?$data['action']:'';
        $where=$data['where'];
        $updates=$data['updates'];
        $update_table=$data['update_table'];
        if($action=='Delete')
        {
            $this->delete($update_table,$where);
        }
        else
        {
            $this->update($updates, $update_table,$where);
        }

    }

    function allow($table,$data,$selected)
    {
        $this->db->where_in('id',$selected);
        return $this->update($data,$table);
    }


    /**
     * This function is used to approve requests
     *if reject is true change the allowed column status to Not allowed
     * else copy selected elements data from leave_request table to leave_grant table.
     *
     * @param $data array The required data to approve the request
     * @param int $reject is the flag if the request is to reject the request (optional)
     * @return array return the message
     */
    function _copy_data($data, $reject = 0)
    {
        if(!isset($data['result']))
        {
            return false;
        }
        $result=$data['result'];
        $insert_table=$data['insert_table'];
        $selected_element=$data['selected_element'];
        $batch=$data['batch'];
        if ($reject) {
            $this->_after_copy($data);
        } else {
            if ($result) {
                $in=$this->insert($result, $insert_table, $batch);
                if($in || $this->error_no=1062)
                {
                    $this->_after_copy($data);
                }
            }
        }
        if ($this->db->affected_rows() == count($selected_element)) {
            $this->message = array('success' => 'Your operation has been successful.');
            $this->copy=true;
        } elseif ($this->db->affected_rows() < 1) {
            $this->message = array('danger' => 'No record has been changed. Record are duplicate');
            $this->copy=false;
            return $this->message;
        } else {
            $this->message = array('warning' => 'Some records has not been changed out of ' . count($selected_element));
            $this->copy=false;
        }
        $this->session->set_userdata('message_' . $this->table, $this->message);
        return $this->message;
    }

    function order_select($table,$order_by = null, $order = null)
    {
        return $this->select($table,'',null,0,$order_by,$order);
    }

    /**
     * This Function Compiles the select statement based on the other functions called and runs the query
     * <code>
     *  $table=array(
     *      'from'=>'lookup',
     *      'select'=>'distinct look_up_type',
     *      'where'=>array('id'=>23,'sex'=>'Male')
     *      'escape_select'=>true);
     * </code>
     * @param String $table
     * @param Mixed $search
     * @param Integer $limit
     * @param Integer $offset
     * @param String $order_by
     * @param String $order
     * @param Boolean $return_resource
     * @return Mixed return number of rows and result as array
     */
    function select($table, $search = '', $limit = null, $offset = 0, $order_by = null, $order = null, $return_resource = false) {

        $all_table = array();
        $this->escape = true;
        $select_filed = 'SQL_CALC_FOUND_ROWS *';
        $this->escape=false;
        if (is_array($table)) {
            //$this->escape = isset($table['escape_select']) ? false : true;
            if (isset($table['select'])) {
                if(is_array($table['select']))
                {
                    $table['select'][0]='SQL_CALC_FOUND_ROWS ' . $table['select'][0];
                }
                else
                {
                    $table['select']='SQL_CALC_FOUND_ROWS ' . $table['select'];
                }
                $select_filed = $table['select'];
                unset($table['select']);
            }
            if (isset($table['from'])) {
                $this->db->from($table['from']);
            }
            if (isset($table['order_by'])) {
                $order_by = $table['order_by'];
                $order = isset($table['order']) ? $table['order'] : 'asc';
            }
            if (isset($table['join'])) {
                $this->join_table($table['join']);
                $all_table = $table['join'];
                $all_table[$table['from']] = $table['from'];
            } else {
                $all_table[$table['from']] = $table['from'];
            }

            if (isset($table['group_by'])) {
                $this->group_bys($table['group_by']);
            }

            if (isset($table['having'])) {
                $this->havings($table['having']);
            }
        } else {
            $this->db->from($table);
        }

        if (is_array($table) && isset($table['where'])) {
            $additional = $table['where'];
            if (is_array($search)) {
                $search = array_merge($search, $additional);
            } else {
                if(empty($search))
                {
                    $search = $additional;
                }
            }
        }
        if (is_array($search)) {
            $search_this = $this->searchs($search);
        } else {
            $search_this = $search;
        }
        if ($search_this != '') {
            $my_where = $this->search_all($table, $all_table, $search_this);
            if ($my_where != '') {
                $this->db->where('(' . $my_where . ')');
            }
        }

        $this->db->select($select_filed, $this->escape);

        if ($limit != null) {
            $this->db->limit($limit, $offset);
        }
        if ($order_by != null) {
            if (is_array($order_by)) {
                foreach ($order_by as $my_order) {
                    $this->db->order_by($my_order, $order);
                }
            } else {
                $this->db->order_by($order_by, $order);
            }
        }
        $result = $this->db->get();

        if ($return_resource) {
            return $result;
        }
        $result=$result->result();
        $total_record=count($result);
        if (!$this->no_num_row && $limit) {
            $total_record = $this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
            $this->no_num_row = false;
        }
        return array(
            'total_row' => $total_record,
            'result' => $result ,
        );
    }
    function get_all_record($table,$criteria=null)
    {
        $data= $this->select($table,$criteria);
        if($data['total_row']>0)
        {
            return $data['result'];
        }
        return false;
    }

    function get_all($table,$criteria=null,$result=true)
    {
        $data= $this->select($table,$criteria);
        if($result)
        {
            return $data['result'];
        }
        return $data;
    }

    function get_tree_data($resource, $label_filed, $id_filed, $parent_filed) {
        $path = array();
        $tree_data = array();
        $this->unknown = array();
        $active_function = $this->db->dbdriver . '_fetch_object';
        while ($row = $active_function($resource)) {
            $cur = array('id' => (int) $row->$id_filed, 'label' => $row->$label_filed);
            $parent_value = $row->$parent_filed;
            $temp = $this->get_path($parent_value, $cur, $path);
            $path[$row->$id_filed] = $row->$parent_filed;
            $cur['inode'] = false;
            $cur['checkbox'] = true;
            $cur['radio'] = false;
            $cur = (object) $cur;
            $my_id = $row->$id_filed;
            if ($temp !== false) {
                $tree_data = $this->put_data($temp, $tree_data, $my_id, $cur);
            }
            unset($temp);
            unset($cur);
            unset($parent_value);
        }
        return manage_index($tree_data);
    }

    function get_path($parent_value, $cur, $path) {
        $temp = array();
        while ($parent_value != '') {
            $temp[] = $parent_value;
            if (array_key_exists($parent_value, $path)) {
                $parent_value = $path[$parent_value];
            } else {
                if (isset($this->unknown[$parent_value])) {
                    $this->unknown[$parent_value][] = $cur;
                } else {
                    $this->unknown[$parent_value] = array($cur);
                }
                return false;
            }
        }
        return $temp;
    }

    function put_data($temp, &$tree_data, $my_id, $cur) {


        if (count($temp) == 0) {
            $tree_data[$my_id] = $cur;
        } else {
            $position = $tree_data;
            for ($i = count($temp) - 1; $i >= 0; $i--) {
                if (is_object($position)) {
                    if (isset($position->branch)) {
                        $my_index = $this->branch_temp[$temp[$i]];
                        $position = &$position->branch[$my_index];
                    } else {
                        $position = &$position[$temp[$i]];
                    }
                } else {
                    $position = &$position[$temp[$i]];
                }
            }
            if (isset($position->branch)) {
                $position->inode = true;
                $position->branch[] = $cur;
                $this->branch_temp[$my_id] = count($position->branch) - 1;
                $this->count++;
            } else {
                $position->inode = true;
                $position->branch = array($cur);
                $this->branch_temp[$my_id] = count($position->branch) - 1;
                $this->count++;
            }
            if (array_key_exists($my_id, $this->unknown)) {
                $my_index = $this->branch_temp[$my_id];
                $position = &$position->branch[$my_index];
                $temp = array_merge(array($my_id), $temp);
                $position->inode = true;
                foreach ($this->unknown[$my_id] as $value) {
                    $tree_data = $this->put_data($temp, $tree_data, $value['id'], $value);
                }
            }
        }

        return $tree_data;
    }

    function get_data($resource, $key = 'employee_id', $array = false) {
        $new_data = array();
        $active_function = $this->db->dbdriver . '_fetch_object';
        $array_group = is_array($key);
        while ($row = $active_function($resource)) {
            $data = $row;
            if ($array) {
                $data = (array) $row;
            }

            if ($array_group) {
                $current_item = &$new_data;
                foreach ($key as $group_by) {
                    if (!isset($current_item[$row->$group_by])) {
                        $current_item[$row->$group_by] = array();
                    }
                    $current_item = &$current_item[$row->$group_by];
                }
                $current_item[] = $data;
            } else {
                if (isset($new_data[$row->$key])) {
                    $new_data[$row->$key][] = $data;
                } else {
                    $new_data[$row->$key] = array($data);
                }
            }
        }

        return $new_data;
    }

    function max_min_of_element($table, $max_or_min, $column) {
        if ($max_or_min == 'max') {
            $dd = $this->db->select_max($column)->get($table)->result();
        } else
            if ($max_or_min == 'max') {
                $dd = $this->db->select_min($column)->get($table)->result();
            }
        foreach ($dd as $dd2) {
            return $dd2->$column;
        }
    }

}