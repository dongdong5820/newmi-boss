<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Bruce <xuehaitao@100msh.com>
 * @date		2014-7-2
 * @desc		日志分类管理
 */
class LogcateMod extends commonMod
{
	private $logcate_dao;

    public function __construct()
    {
        parent::__construct();
        $this->logcate_dao = new LogCate();
    }

    public function index() {
        $page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;//当前页
    	$s_search_key     = isset($_GET['s_search_key']) ? in($_GET['s_search_key']) : '';
        $cate_name        = isset($_GET['cate_name']) ? in($_GET['cate_name']) : '';
        $cate_no          = isset($_GET['cate_no']) ? in($_GET['cate_no']) : '';

        $filter = array(
            's_search_key'  => $s_search_key,
            'cate_name'     => $cate_name,
            'cate_no'       => $cate_no,
        );

    	$numPerPage = 10;//每页显示数量
		$start      = ($page - 1) * $numPerPage;

		$logcate_list  = $this->logcate_dao->getList($filter,$start,$numPerPage);
		$logcate_count = $this->logcate_dao->getCount($filter);
		$page_string   = $this->page("logcate/index", $logcate_count,$numPerPage,5,4);

		$this->assign("logcate_list", $logcate_list);
		$this->assign("page_string", $page_string);
		$this->assign("filter", $filter);

        $this->display();
    }

    /**
     * @author	bruce
     * @desc	添加日志分类
     */
    public function add() {
    	if($_POST) {
    		$cate_name = trim($_POST['cate_name']);
    		if(empty($cate_name)) {
    			echo json_encode(array("status"=>"error","msg"=>"分类名称不能为空！"));	exit;
    		} else {
    			
    			if($this->logcate_dao->checkCateNameExist($cate_name)){
    				echo json_encode(array("status"=>"error","msg"=>"分类名称已经存在！"));	exit;
    			}
    		}

    		$cate_no = trim($_POST['cate_no']);
    		if(empty($cate_no)){
    			echo json_encode(array("status"=>"error","msg"=>"分类编号不能为空！"));	exit;
    		} else {
    			if($this->logcate_dao->checkCateNoExist($cate_no)){
    				echo json_encode(array("status"=>"error","msg"=>"分类编号已经存在！"));	exit;
    			}
    		}
    		
    		$cate_desc = $_POST['cate_desc'];
    		
    		$data=array(
    			"cate_name" =>$cate_name,
    			"cate_no"=>$cate_no,
    			"cate_desc"=>$cate_desc,
    		);

    		$res=$this->logcate_dao->insert($data);
    		if($res){
    			echo json_encode(array("status"=>"success","msg"=>"添加日志分类成功","isRefresh"=>1,"closePopup"=>1));	exit;
    		}else{
    			echo json_encode(array("status"=>"error","msg"=>"添加日志分类失败！"));	exit;
    		}
    	}

    	$this->display();
    }

    /**
     * @author	bruce
     * @desc	编辑日志分类
     */
    public function edit() {
    	$log_cate_id = (int)$_GET['log_cate_id'];
    	$row = $this->logcate_dao->getRow($log_cate_id);

    	if($_POST) {
    		$cate_name = $_POST['cate_name'];
    		if(empty($cate_name)) {
    			echo json_encode(array("status"=>"error","msg"=>"分类名称不能为空！"));	exit;
    		} else {
    			if($this->logcate_dao->checkCateNameExist($cate_name) && $cate_name != $row['cate_name']){
    				echo json_encode(array("status"=>"error","msg"=>"分类名称已经存在！"));	exit;
    			}
    		}
    		
    		$cate_desc = $_POST['cate_desc'];
    		
    		$data=array(
    			"cate_name" =>$cate_name,
    			"cate_desc"=>$cate_desc,
    		);

    		$res=$this->logcate_dao->update($log_cate_id,$data);
    		if($res){
    			echo json_encode(array("status"=>"success","msg"=>"编辑日志分类成功","isRefresh"=>1,"closePopup"=>1));	exit;
    		}else{
    			echo json_encode(array("status"=>"error","msg"=>"编辑日志分类失败！"));	exit;
    		}
    	}

    	if(empty($row)) {
    		echo json_encode(array("status"=>"error","msg"=>"日志分类不存在","isRefresh"=>1,"closePopup"=>1));	exit;
    	}

    	$this->assign("log_cate_id", $log_cate_id);
    	$this->assign("cate_name", $row['cate_name']);
    	$this->assign("cate_no", $row['cate_no']);
    	$this->assign("cate_desc", $row['cate_desc']);

    	$this->display();
    }

    /**
     * @author	bruce
     * @desc	日志分类信息
     */
    public function infos(){
    	$log_cate_id=$_GET['log_cate_id'];

    	$row = $this->logcate_dao->getRow($log_cate_id);

    	if(empty($row)){
    		$this->ajax_error("日志分类不存在!");
    	}
    	$this->assign("row", $row);
    	$this->display();
    }

    /**
     * @author	bruce
     * @desc	删除日志分类
     */
    public function del() {
    	$kid = isset($_GET['kid']) ? in($_GET['kid']) : '';
    	if($kid == '') {
    		echo json_encode(array("status"=>"error","msg"=>"请选择日志分类","isRefresh"=>1,"closePopup"=>1)); exit;
    	}

    	$del_count  = $this->logcate_dao->delete($kid);
        $kid_count  = count(explode(',',$kid));
        $cant_count =  $kid_count - $del_count;
    	if($del_count) {
    		echo json_encode(array("status"=>"success","msg"=>"总共删除了${del_count}个日志分类，其中${cant_count}个分类已经有日记记录","isRefresh"=>1,"closePopup"=>1)); exit;
    	} else {
    		echo json_encode(array("status"=>"error","msg"=>"删除失败,${cant_count}个分类已经有日记记录","isRefresh"=>1,"closePopup"=>1)); exit;
    	}
    	
    }
}