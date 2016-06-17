<?php
/**
 * @copyright   ©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link        http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author      Bruce <xuehaitao@100msh.com>
 * @date        2014-7-2
 * @desc        日志分类管理
 */
class LogMod extends commonMod
{
    private $log_dao;

    public function __construct()
    {
        parent::__construct();
        $table = isset($_GET['partition_table']) ? $_GET['partition_table'] : '';
        $this->log_dao = new Log($this->getLogTable($table));
    }

    public function index() {
        $page             = isset($_GET['page'])&&!empty($_GET['page']) ? in($_GET['page']) : 1;//当前页
        $s_search_key     = isset($_GET['s_search_key']) ? in($_GET['s_search_key']) : '';
        $partition_table  = isset($_GET['partition_table']) ? in($_GET['partition_table']) : '';
        $log_cate_id      = isset($_GET['log_cate_id']) ? in($_GET['log_cate_id']) : '';
        $log_oper_type_id = isset($_GET['log_oper_type_id']) ? in($_GET['log_oper_type_id']) : '';
        $oper_user_id     = isset($_GET['oper_user_id']) ? in($_GET['oper_user_id']) : '';
        $log_title        = isset($_GET['log_title']) ? in($_GET['log_title']) : '';
        $log_content      = isset($_GET['log_content']) ? in($_GET['log_content']) : '';
        $log_ip           = isset($_GET['log_ip']) ? in($_GET['log_ip']) : '';
        $log_time_start   = isset($_GET['log_time_start']) ? in($_GET['log_time_start']) : '';
        $log_time_end     = isset($_GET['log_time_end']) ? in($_GET['log_time_end']) : '';

        $filter = array(
            's_search_key'     => $s_search_key,
            'partition_table'  => $partition_table,
            'log_cate_id'      => $log_cate_id,
            'log_oper_type_id' => $log_oper_type_id,
            'oper_user_id'     => $oper_user_id,
            'log_title'        => $log_title,
            'log_content'      => $log_content,
            'log_ip'           => $log_ip,
            'log_time_start'   => $log_time_start,
            'log_time_end'     => $log_time_end,
        );
        
        $numPerPage = 10;//每页显示数量
        $start = ($page - 1) * $numPerPage;

        $log_list    = $this->log_dao->getList($filter,$start,$numPerPage);
        foreach($log_list as &$row) {
            $row['log_time'] = date('Y-m-d H:i:s',$row['log_time']);
            $row['log_content'] = msubstr($row['log_content'],0,50);
        }
        $log_count   = $this->log_dao->getCount($filter);
        $page_string = $this->page("log/index", $log_count,$numPerPage,5,4);

        $this->assign("log_list", $log_list);
        $this->assign("page_string", $page_string);

        $log_time_partition_dao = new LogTimePartition();
        $log_time_partition_list = $log_time_partition_dao->getAll();

        $log_oper_type_dao = new LogOperType();
        $log_oper_type_list = $log_oper_type_dao->getAll();

        $log_cate_dao  = new LogCate();
        $log_cate_list = $log_cate_dao->getAll();

       /* $adm_users_dao  = new Admusers();
        $adm_users_list = $adm_users_dao->getAll();*/

        $this->assign("log_time_partition_list", $log_time_partition_list);
        $this->assign("log_oper_type_list", $log_oper_type_list);
        $this->assign("log_cate_list", $log_cate_list);
//       $this->assign("adm_users_list", $adm_users_list);
        $this->assign("filter", $filter);

        $this->display();
    }

    /**
     * @author  bruce
     * @desc    日志信息
     */
    public function infos(){
        $log_id=$_GET['log_id'];
        $row = $this->log_dao->getRow($log_id);
        if(empty($row)){
        	$this->ajax_error("日志不存在!");
        }
        $row['log_time'] = date('Y-m-d H:i:s',$row['log_time']);
        $this->assign("row", $row);
        $this->display();
    }

    private function getLogTable($table='') {
        $model = new LogTimePartition();

        return $model->getLogTable($table);
    }
}