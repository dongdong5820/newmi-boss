<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Bruce <xuehaitao@100msh.com>
 * @date		2014-7-2
 * @desc		日志分区表管理
 */
class LogTimePartition
{
	private $log_model;
    private $prefix = '';

    public function __construct()
    {
        $this->log_model = App::db()->LOG;
        $this->prefix = $this->log_model->pre;
    }

    public function getAll() {
        return $this->log_model->table(T_LOG_TIME_PARTITION)->field('partition_title,partition_table')->select();
    }

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