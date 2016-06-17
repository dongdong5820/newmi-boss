<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		jiang <jiang@100msh.com>
 * @date		2014-10-11
 * @desc		职位管理
 */
class jobMod extends commonMod{
	private $jobtype_dao;
	private $job_dao;
	public function __construct(  ){
		parent::__construct();
		$this->job_dao=new job();
	}
	public function index(){
		$page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;
		$s_job_name     = isset($_GET['s_job_name']) ? in($_GET['s_job_name']) : '';
		$s_job_type_id     = isset($_GET['s_job_type_id']) ? in($_GET['s_job_type_id']) : '';
		$s_job_hot     = isset($_GET['s_job_hot']) ? in($_GET['s_job_hot']) : '';
		$s_job_status     = isset($_GET['s_job_status']) ? in($_GET['s_job_status']) : '';
		$where='1';
		if($s_job_name!=''){
			$where.=" and j.job_name like '%$s_job_name%'";
		}
		if($s_job_type_id!=''){
			$where.=" and j.job_type_id = $s_job_type_id";
		}
		if($s_job_hot!=''){
			$where.=" and j.job_hot = $s_job_hot";
		}
		if($s_job_status!=''){
			$where.=" and j.job_status = $s_job_status";
		}
		
		
		$filter = array(
			's_job_name'  => $s_job_name,
			's_job_type_id'     => $s_job_type_id,
			's_job_hot'     => $s_job_hot,
			's_job_status'     => $s_job_status,
				
		);
		$numPerPage = 10;//每页显示数量
		$start      = ($page - 1) * $numPerPage;
		$job_list  = $this->job_dao->getList($where,$start,$numPerPage);
		$job_count = $this->job_dao->getCount($where);
		$page_string   = $this->page("job/index", $job_count,$numPerPage,5,4);
		$type_list=$this->job_dao->getJobTypeList();
		$this->assign('type_list',$type_list);
		$this->assign("job_list", $job_list);
		$this->assign("page_string", $page_string);
		$this->assign("filter", $filter);
		$this->assign("job_url", App::$config['job_site_url']);
		$this->display();
	}
	public function add(){
		if(isset($_POST)&&!empty($_POST)){
			$job_name=isset($_POST['job_name'])?in($_POST['job_name']):'';
			$job_type_id=isset($_POST['job_type_id'])?in($_POST['job_type_id']):'';
			$job_desc=isset($_POST['job_desc'])?in($_POST['job_desc']):'';
			$job_email=isset($_POST['job_email'])?in($_POST['job_email']):'';
			$job_hot=isset($_POST['job_hot'])?intval($_POST['job_hot']):'0';
			$job_order=isset($_POST['job_order'])?in($_POST['job_order']):'';
			$job_status=isset($_POST['job_status'])?intval($_POST['job_status']):'0';
			if(empty($job_name)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入职位标题！" ));exit;}
			preg_match_all("/./us", $job_name, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'职位标题长度过长，长度请少于30个字！'));exit;
			}
			$check_name=$this->job_dao->checkjobName($job_name,$job_type_id);
			if($check_name==1){echo json_encode(array('status' =>'error' ,'msg'=>"职位标题已存在，请更换！" ));exit;}
			if(empty($job_type_id)){echo json_encode(array('status' =>'error' ,'msg'=>"请选择职位分类！" ));exit;}
			if(empty($job_desc)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入职位描述！" ));exit;}
			if(empty($job_email)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入投递邮箱！" ));exit;}
			if(!is_email($job_email)){
				echo json_encode(array('status'=>"error",'msg'=>'职位投递邮箱格式不正确！'));exit;
			}
			if(empty($job_order)&&$job_order!='0'){echo json_encode(array('status' =>'error' ,'msg'=>"请输入显示排序！" ));exit;}
			if(!is_numeric($job_order)||strpos($job_order,".")!==false||$job_order=='0'||strpos($job_order,"-")!==false){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序必须为正整数！" ));exit;}
			$check_order=$this->job_dao->checkjobOrder($job_order,$job_type_id);
			if($check_order==1){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序已存在，请更换！" ));exit;}
			$ins_data=array(
				"job_name"=>$job_name,
				"job_type_id"=>$job_type_id,
				"job_desc"=>$job_desc,
				"job_email"=>$job_email,
				"job_hot"=>$job_hot,
				"job_order"=>$job_order,
				"job_status"=>$job_status,
				"job_crttime"=>date('Y-m-d H:i:s'),
				"job_crtby"=>U::getUserId(),
			);
			$ins_result=$this->job_dao->add($ins_data);
			if($ins_result){
				//日志的管理
				$log_content="";
				$log_content[] = "job_name:" . $job_name;
				$log_content[] = "job_type_id:" . $job_type_id;
				$log_content[] = "job_desc:" . $job_desc;
				$log_content[] = "job_email:" . $job_email;
				$log_content[] = "job_hot:" . $job_hot;
				$log_content[] = "job_order:" . $job_order;
				$log_content[] = "job_status:" . $job_status;
				$log_content[] = "job_crttime:" . $ins_data['job_crttime'];
				$log_content[] = "job_crtby:" . $ins_data['job_crtby'];
				$log_content = implode(" , ", $log_content);
				$operationLogDAO_obj=operationLogDAO::getInstance();
				$operationLogDAO_obj->create_log('百米官网职位管理--添加职位',$log_content,'LC003',"LOT001");
				$out_data=array('status'=>"success",'msg'=>'职位添加成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				$out_data=array('status'=>"error",'msg'=>'职位添加失败！');
			}
			echo json_encode($out_data);exit;
		}
		$type_list=$this->job_dao->getJobTypeList();
		$KindEditor_obj=new KindEditor();
		$editor=$KindEditor_obj->create_editor("job_desc",null,260,"editor");
		$this->assign('editor',$editor);
		$this->assign('type_list',$type_list);
		
		$this->display();
	}
	public function edit(){
		if(isset($_POST)&&!empty($_POST)){			
			$job_id=isset($_POST['job_id'])?intval($_POST['job_id']):'';
			if(empty($job_id)){echo json_encode(array('status' =>'error' ,'msg'=>"空参数错误" ));exit;}
			$job_name=isset($_POST['job_name'])?in($_POST['job_name']):'';
			$job_type_id=isset($_POST['job_type_id'])?in($_POST['job_type_id']):'';
			$job_desc=isset($_POST['job_desc'])?in($_POST['job_desc']):'';
			$job_email=isset($_POST['job_email'])?in($_POST['job_email']):'';
			$job_hot=isset($_POST['job_hot'])?intval($_POST['job_hot']):'0';
			$job_order=isset($_POST['job_order'])?in($_POST['job_order']):'';
			$job_status=isset($_POST['job_status'])?intval($_POST['job_status']):'0';
			if(empty($job_name)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入职位标题！" ));exit;}
			preg_match_all("/./us", $job_name, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'职位标题长度过长，长度请少于30个字！'));exit;
			}
			$check_name=$this->job_dao->checkjobName($job_name,$job_type_id,$job_id);
			if($check_name==1){echo json_encode(array('status' =>'error' ,'msg'=>"职位标题已存在，请更换！" ));exit;}
			if(empty($job_type_id)){echo json_encode(array('status' =>'error' ,'msg'=>"请选择职位分类！" ));exit;}
			if(empty($job_desc)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入职位描述！" ));exit;}
			if(empty($job_email)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入投递邮箱！" ));exit;}
			if(!is_email($job_email)){
				echo json_encode(array('status'=>"error",'msg'=>'职位投递邮箱格式不正确！'));exit;
			}
			if(empty($job_order)&&$job_order!='0'){echo json_encode(array('status' =>'error' ,'msg'=>"请输入显示排序！" ));exit;}
			if(!is_numeric($job_order)||strpos($job_order,".")!==false||$job_order=='0'||strpos($job_order,"-")!==false){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序必须为正整数！" ));exit;}
			$check_order=$this->job_dao->checkjobOrder($job_order,$job_type_id,$job_id);
			if($check_order==1){echo json_encode(array('status' =>'error' ,'msg'=>"显示排序已存在，请更换！" ));exit;}
			$ins_data=array(
					"job_name"=>$job_name,
					"job_type_id"=>$job_type_id,
					"job_desc"=>$job_desc,
					"job_email"=>$job_email,
					"job_hot"=>$job_hot,
					"job_order"=>$job_order,
					"job_status"=>$job_status,
					"job_uptime"=>date('Y-m-d H:i:s'),
					"job_upby"=>U::getUserId(),
			);
			$ins_result=$this->job_dao->edit($ins_data,$job_id);
			if($ins_result==1){
				//日志的管理
				$log_content="";
				$log_content[] = "job_name:" . $job_name;
				$log_content[] = "job_type_id:" . $job_type_id;
				$log_content[] = "job_desc:" . $job_desc;
				$log_content[] = "job_email:" . $job_email;
				$log_content[] = "job_hot:" . $job_hot;
				$log_content[] = "job_order:" . $job_order;
				$log_content[] = "job_status:" . $job_status;
				$log_content[] = "job_uptime:" . $ins_data['job_uptime'];
				$log_content[] = "job_upby:" . $ins_data['job_upby'];
				$log_content = implode(" , ", $log_content);
				$operationLogDAO_obj=operationLogDAO::getInstance();
				$operationLogDAO_obj->create_log('百米官网职位管理--修改职位',$log_content,'LC003',"LOT001");
				$out_data=array('status'=>"success",'msg'=>'职位修改成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				$out_data=array('status'=>"error",'msg'=>'职位修改失败！');
			}
			echo json_encode($out_data);exit;
		}
		
		$job_id=$_GET['job_id']?intval($_GET['job_id']):'';
		if(empty($job_id)){$this->ajax_error("空参数错误!");}
		$info=$this->job_dao->getJobInfo($job_id);
		if(empty($info)){$this->ajax_error("查无此条!");}
		$type_list=$this->job_dao->getJobTypeList();
		$this->assign('type_list',$type_list);
		$this->assign('info',$info);
		$KindEditor_obj=new KindEditor();
		$editor=$KindEditor_obj->create_editor("job_desc",null,260,"editor");
		$this->assign('editor',$editor);
		$this->assign("job_url", App::$config['job_site_url']);
		$this->display();
	}
	public function del(){
		$ids_str=isset($_GET['kid'])?in($_GET['kid']):'';
		if(empty($ids_str)){
			echo json_encode(array('status'=>"error",'msg'=>'空参数错误！',));exit;
		}
		$ids_str=explode(',',$ids_str);
		$suc=$err=0;
		foreach($ids_str as $id){
			if($result=$this->job_dao->del($id)){
				if($result){
					$suc=1;	
				}else{
					$err=1;
				}				
			}else{
				$err=1;
			}
		}
		if($suc&&$err){
			echo json_encode(array('status'=>"error",'msg'=>'职位有部分未删除！',));exit;
		}elseif($suc){
			echo json_encode(array('status'=>"success",'msg'=>'职位删除成功！',"isRefresh"=>1,"closePopup"=>1));exit;
		}else{
			echo json_encode(array('status'=>"error",'msg'=>'职位删除失败！',));exit;
		}
	}
	public function detail(){
		$job_id=isset($_GET['job_id'])?intval($_GET['job_id']):'';
		if(empty($job_id)){
			$this->ajax_error("参数有问题,请检查!");
		}
		$info=$this->job_dao->getJobInfo($job_id);
		if(empty($info)){$this->ajax_error("没有查到此条数据,请检查!");}
		$this->admusers_dao = new Admusers();
		$sys_list = $this->admusers_dao->getUserList($info['job_crtby']);
		$info['job_crtby_name']=isset($sys_list[$info['job_crtby']]) ? $sys_list[$info['job_crtby']]['username'] : '';
		$this->assign("info", $info);
		$this->display();
	}
	public function infos(){
		$job_id=isset($_GET['job_id'])?intval($_GET['job_id']):'';
		if(empty($job_id)){$this->ajax_error("详细信息参数有问题");}
		$info=$this->job_dao->getJobInfo($job_id);
		if(empty($info)){$this->ajax_error("没有查到此条数据,请检查!");}
		$this->admusers_dao = new Admusers();
		$sys_list = $this->admusers_dao->getUserList($info['job_crtby']);
		$info['job_crtby_name']=isset($sys_list[$info['job_crtby']]) ? $sys_list[$info['job_crtby']]['username'] : '';
		$this->assign("info", $info);
		$this->display();
	}
}