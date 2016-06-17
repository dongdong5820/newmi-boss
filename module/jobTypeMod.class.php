<?php
/**
 * @copyright	©2015-2016 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		steve <steve@100msh.com>
 * @date		2015年7月23日
 * @desc		职位类别
 */
class jobTypeMod extends commonMod{
	public function __construct(  ){
		parent::__construct();
		$this->jobType_dao=new jobType();
	}
	public function index(){
		$page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;//当前页
		$s_job_type_name     = isset($_GET['s_job_type_name']) ? in($_GET['s_job_type_name']) : '';
		$where='1';
		if(!empty($s_job_type_name)){
			$where.=" and job_type_name like '%$s_job_type_name%'";
		}
		$filter = array(
			's_job_type_name'  => $s_job_type_name,
		);
		$numPerPage = 10;//每页显示数量
		$start      = ($page - 1) * $numPerPage;
		$jobType_list  = $this->jobType_dao->getList($where,$start,$numPerPage);
		$jobType_count = $this->jobType_dao->getCount($where);
		$page_string   = $this->page("jobType/index", $jobType_count,$numPerPage,5,4);
		$this->assign("jobType_list", $jobType_list);
		$this->assign("page_string", $page_string);
		$this->assign("filter", $filter);
		$this->display();
	}
	public function add(){
		if(isset($_POST)&&!empty($_POST)){
			$job_type_name=isset($_POST['job_type_name'])?in($_POST['job_type_name']):'';
			$job_type_order=isset($_POST['job_type_order'])?in($_POST['job_type_order']):'';
			if(empty($job_type_name)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入分类名称！" ));exit;}
			preg_match_all("/./us", $job_type_name, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'分类名称长度过长，长度请少于30个字！'));exit;
			}
			$check_name=$this->jobType_dao->checkTypeName($job_type_name);
			if($check_name==1){echo json_encode(array('status' =>'error' ,'msg'=>"分类名称已存在，请更换！" ));exit;}
			if(empty($job_type_order)&&$job_type_order!='0'){echo json_encode(array('status' =>'error' ,'msg'=>"请输入显示排序！" ));exit;}
			if(!is_numeric($job_type_order)||strpos($job_type_order,".")!==false||$job_type_order=='0'||strpos($job_type_order,"-")!==false){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序必须为正整数！" ));exit;}
			$check_order=$this->jobType_dao->checkTypeOrder($job_type_order);
			if($check_order==1){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序已存在，请更换！" ));exit;}
			$ins_data=array(
				"job_type_name"=>$job_type_name,
				"job_type_order"=>$job_type_order,
				"job_type_crtby"=>U::getUserId(),
				"job_type_crttime"=>date('Y-m-d H:i:s'),
			);
			$ins_result=$this->jobType_dao->add($ins_data);
			if($ins_result){
			    //日志的管理
			    $log_content="";
			    $log_content[] = "job_type_name:" . $job_type_name;
			    $log_content[] = "job_type_order:" . $job_type_order;
			    $log_content[] = "job_type_crtby:" . $ins_data['job_type_crtby'];
			    $log_content[] = "job_type_crttime:" . $ins_data['job_type_crttime'];
			    $log_content = implode(" , ", $log_content);
			    $operationLogDAO_obj=operationLogDAO::getInstance();
			    $operationLogDAO_obj->create_log('百米官网职位管理--添加职位分类',$log_content,'LC003',"LOT001");
				$out_data=array('status'=>"success",'msg'=>'分类添加成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				$out_data=array('status'=>"error",'msg'=>'分类添加失败！');
			}
			echo json_encode($out_data);exit;
		}
		$this->display();
	}
	public function edit(){
		if(isset($_POST)&&!empty($_POST)){
			$job_type_id=isset($_POST['job_type_id'])?intval($_POST['job_type_id']):'';
			if(empty($job_type_id)){
				$out_data=array('status'=>"error",'msg'=>'空参数错误！');
			}
			$job_type_name=isset($_POST['job_type_name'])?in($_POST['job_type_name']):'';
			$job_type_order=isset($_POST['job_type_order'])?in($_POST['job_type_order']):'';
			if(empty($job_type_name)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入分类名称！" ));exit;}
			preg_match_all("/./us", $job_type_name, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'分类名称长度过长，长度请少于30个字！'));exit;
			}
			if(empty($job_type_order)&&$job_type_order!='0'){echo json_encode(array('status' =>'error' ,'msg'=>"请输入显示排序！" ));exit;}
			$check_name=$this->jobType_dao->checkTypeName($job_type_name,$job_type_id);
			if($check_name==1){echo json_encode(array('status' =>'error' ,'msg'=>"分类名称已存在，请更换！" ));exit;}
			if(!is_numeric($job_type_order)||strpos($job_type_order,".")!==false||$job_type_order=='0'||strpos($job_type_order,"-")!==false){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序必须为正整数！" ));exit;}
			$check_order=$this->jobType_dao->checkTypeOrder($job_type_order,$job_type_id);
			if($check_order==1){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序已存在，请更换！" ));exit;}
			$ins_data=array(
				"job_type_name"=>$job_type_name,
				"job_type_order"=>$job_type_order,
				"job_type_upby"=>U::getUserId(),
				"job_type_uptime"=>date('Y-m-d H:i:s'),
			);
			$ins_result=$this->jobType_dao->edit($job_type_id,$ins_data);
			if($ins_result){
			    //日志的管理
			    $log_content="";
			    $log_content[] = "job_type_name:" . $job_type_name;
			    $log_content[] = "job_type_order:" . $job_type_order;
			    $log_content[] = "job_type_upby:" . $ins_data['job_type_upby'];
			    $log_content[] = "job_type_uptime:" . $ins_data['job_type_uptime'];
			    $log_content = implode(" , ", $log_content);
			    $operationLogDAO_obj=operationLogDAO::getInstance();
			    $operationLogDAO_obj->create_log('百米官网职位管理--修改职位分类',$log_content,'LC003',"LOT002");
				$out_data=array('status'=>"success",'msg'=>'分类修改成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				$out_data=array('status'=>"error",'msg'=>'分类修改失败！');
			}
			echo json_encode($out_data);exit;
		}
		$job_type_id=isset($_GET['job_type_id'])?intval($_GET['job_type_id']):'';
		if(empty($job_type_id)){
			$this->ajax_error("空参数错误!");
		}else{
			$info=$this->jobType_dao->getInfo($job_type_id);
			if($info){
				$this->assign('info',$info);
			}else{
				$this->ajax_error("查无此项!");
			}
		}
		$this->display();
	}
	public function del(){
		$all_ids=isset($_GET['kid'])?in($_GET['kid']):'';
		if(empty($all_ids)){
			echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
		}else{
			$del_result=$this->jobType_dao->del($all_ids);
			switch ($del_result) {
				case 1:
				    //日志的管理
				    $log_content="";
				    $log_content[] = "job_type_id:" . $all_ids;
				    $log_content = implode(" , ", $log_content);
				    $operationLogDAO_obj=operationLogDAO::getInstance();
				    $operationLogDAO_obj->create_log('百米官网职位管理--删除职位分类',$log_content,'LC003',"LOT003");
					echo json_encode(array("status"=>"success","msg"=>"删除成功！","isRefresh"=>1));exit;
					break;
// 				case 2:
// 					echo json_encode(array("status"=>"success","msg"=>"所选的分类部分已经关联职位无法删除，未关联的删除成功！","isRefresh"=>1));exit;
// 					break;
				case -1:
					echo json_encode(array("status"=>"error","msg"=>"删除失败！"));exit;
					break;
				case -2:
					echo json_encode(array("status"=>"error","msg"=>"分类下存在职位，无法删除！"));exit;
					break;
				default:
					echo json_encode(array("status"=>"error","msg"=>"删除失败！"));exit;
					break;
			}
		}
	}
}