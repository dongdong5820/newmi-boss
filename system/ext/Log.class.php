<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Bruce <xuehaitao@100msh.com>
 * @date		2014-7-2
 * @desc		操作日志管理
 */
class Log
{
	private $log_model;
    private $prefix = '';
    private $table = '';

    public function __construct($table)
    {
        $this->log_model = App::db()->LOG;
        $this->prefix = $this->log_model->pre;
        $this->table = $table;
    }

    /**
     * 获取操作日志列表
     * @param $filter array 过滤的条件
     * @param $start int 数据开始的位置
     * @param $numPerPage int 偏移量
     *
     * @return array
     */
    public function getList($filter,$start,$numPerPage) {
    	
    	$where = '1=1';

        if($filter['s_search_key']) {
            $where .= " AND log.log_title LIKE '%" . $filter['s_search_key'] . "%' OR log.oper_user_name LIKE '%" . $filter['s_search_key'] . "%' OR log.oper_user_realname LIKE '%" . $filter['s_search_key'] . "%'";
        }

        if($filter['log_cate_id']) {
            $where .= " AND log.log_cate_id =" . $filter['log_cate_id'];
        }

        if($filter['log_oper_type_id']) {
            $where .= " AND log.log_oper_type_id =" . $filter['log_oper_type_id'];
        }

        if($filter['oper_user_id']) {
            $where .= " AND log.oper_user_id =" . $filter['oper_user_id'];
        }

        if($filter['log_title']) {
            $where .= " AND log.log_title LIKE '%" . $filter['log_title'] . "%'";
        }

        if($filter['log_content']) {
            $where .= " AND log.log_content LIKE '%" . $filter['log_content'] . "%'";
        }

        if($filter['log_ip']) {
            $where .= " AND log.log_ip LIKE '%" . $filter['log_ip'] . "%'";
        }

        if($filter['log_time_start']) {
            $where .= " AND log.log_time >= " . strtotime($filter['log_time_start']);
        }

        if($filter['log_time_end']) {
            $where .= " AND log.log_time <= " . strtotime($filter['log_time_end']);
        }
    	
        $sql = "SELECT log.*,log_cate.cate_name,log_oper_type.type_name FROM " . $this->table . " log " .
                " LEFT JOIN " .$this->prefix . T_LOG_CATE . " log_cate ON log.log_cate_id = log_cate.log_cate_id".
                " LEFT JOIN " .$this->prefix . T_LOG_OPER_TYPE . " log_oper_type ON log.log_oper_type_id = log_oper_type.log_oper_type_id".
                " WHERE $where" . 
                " ORDER BY log_id DESC ".
                " LIMIT $start,$numPerPage";

        return $this->log_model->query($sql);
    }

    /**
     * 获取总数
     * @param $filter array 过滤的条件
     * @return int
     */
    public function getCount($filter) {
    	
    	$where = '1=1';
    	if($filter['s_search_key']) {
            $where .= " AND log.log_title LIKE '%" . $filter['s_search_key'] . "%' OR log.oper_user_name LIKE '%" . $filter['s_search_key'] . "%' OR log.oper_user_realname LIKE '%" . $filter['s_search_key'] . "%'";
        }
        if($filter['log_cate_id']) {
            $where .= " AND log.log_cate_id =" . $filter['log_cate_id'];
        }

        if($filter['log_oper_type_id']) {
            $where .= " AND log.log_oper_type_id =" . $filter['log_oper_type_id'];
        }

        if($filter['oper_user_id']) {
            $where .= " AND log.oper_user_id =" . $filter['oper_user_id'];
        }

        if($filter['oper_user_id']) {
            $where .= " AND log.oper_user_id =" . $filter['oper_user_id'];
        }

        if($filter['log_title']) {
            $where .= " AND log.log_title LIKE '%" . $filter['log_title'] . "%'";
        }

        if($filter['log_content']) {
            $where .= " AND log.log_content LIKE '%" . $filter['log_content'] . "%'";
        }

        if($filter['log_ip']) {
            $where .= " AND log.log_ip LIKE '%" . $filter['log_ip'] . "%'";
        }

        if($filter['log_time_start']) {
            $where .= " AND log.log_time >= " . strtotime($filter['log_time_start']);
        }

        if($filter['log_time_end']) {
            $where .= " AND log.log_time <= " . strtotime($filter['log_time_end']);
        }

    	$sql = "SELECT count(*) as total FROM " . $this->table . " log " .
                " LEFT JOIN " .$this->prefix . T_LOG_CATE . " log_cate ON log.log_cate_id = log_cate.log_cate_id".
                " LEFT JOIN " .$this->prefix . T_LOG_OPER_TYPE . " log_oper_type ON log.log_oper_type_id = log_oper_type.log_oper_type_id".
                " WHERE $where";
        $result = $this->log_model->query($sql);

        return $result[0]['total'];
    }

    /**
     * 获取一条操作日志
     * @param  int $log_id
     * @return array
     */
    public function getRow($log_id) {
    	$row = array();

        $sql = "SELECT log.*,log_cate.cate_name,log_oper_type.type_name FROM " . $this->table . " log " .
                " LEFT JOIN " .$this->prefix . T_LOG_CATE . " log_cate ON log.log_cate_id = log_cate.log_cate_id".
                " LEFT JOIN " .$this->prefix . T_LOG_OPER_TYPE . " log_oper_type ON log.log_oper_type_id = log_oper_type.log_oper_type_id".
                " WHERE log_id = " . $log_id;
        $result = $this->log_model->query($sql);

        if(!empty($result)) {
            $row = $result[0];
        }

        return $row;
    }

    /**
     * 获取需要操作的日志表，如果所给的表在数据库不存在就使用当前月的
     * @param   $table 指定的表
     * @return string
     */
    public function getLogTable($table) {
        $log_table = $table;
        $condition = array('partition_table'=>$table);

        $row = $this->log_model->table(T_LOG_TIME_PARTITION)->field('partition_table')->where($condition)->find();

        if(empty($row)) {
            $nowm=date("Ym",time());
            $log_table = $this->prefix . 'log_' . $nowm;
        }

        return $log_table;
    }
}