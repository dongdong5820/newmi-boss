<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		<chenzhandong@100msh.com>
 * @date		2014-10-11
 * @desc		栏目管理
 */
class mshArticleCityMod extends commonMod{
	private $territories=array(11,12,31,50,81,91);
	
	public function __construct(){
		parent::__construct();
		$this->erp_model=APP::db()->ERP;
	}
	
	function index(){
		$this->authMod();//权限认证
		$page=isset($_GET['page'])&&!empty($_GET['page'])?intval($_GET['page']):1;//当前页
		$numPerPage=10;//每页显示数量
		$start=($page-1)*$numPerPage;
		
		$where='1';
		//关键词
		$s_search_key='';
		if(isset($_GET['s_search_key'])&&!empty($_GET['s_search_key'])){
			$s_search_key=in($_GET['s_search_key']);
			$where.=" AND INSTR(acName,'{$s_search_key}')";
		}
		$s_status='';
		if(isset($_GET['s_status'])){
			$s_status=intval($_GET['s_status']);
			$where.=" AND acStatus=".$s_status;
		}
		
		$list=$this->erp_model->table(T_ARTICLE_CITIES)->where($where)->limit("{$start},{$numPerPage}")->select();
		
		if($list){
			$scids='';
			foreach($list as $item){
				$scids.=$item['sc_id'].',';
			}
			$scids=substr($scids,0,-1);
			$stateCity_dao=new StateCity();
			$cities=array();
			$data=$stateCity_dao->getCityListByScIds($scids);
			foreach($data as $dt){
				$cities[$dt['sc_id']]=$dt['sc_name'];
			}
			
			$statuses=array(0=>'无效',1=>'有效');
			foreach($list as $key=>$item){
				$list[$key]['status']=$statuses[$item['acStatus']];
				$list[$key]['city']=$cities[$item['sc_id']];
			}
		}
		
		$list_count=$this->erp_model->table(T_ARTICLE_CITIES)->where($where)->count();
		$page_string=$this->page('mshArticleCity/index',$list_count,$numPerPage,5,4);
		
		$this->assign("s_search_key",$s_search_key);
		$this->assign("s_status",$s_status);
		
		$this->assign("list",$list);
		$this->assign("page_string",$page_string);
		$this->display();
	}
	
	function add(){
		$stateCity_dao = new StateCity();

		if(isset($_POST)&&!empty($_POST)){
			$scpid=isset($_POST['sc_pid'])?intval($_POST['sc_pid']):'';
			$scid=isset($_POST['sc_id'])?intval($_POST['sc_id']):'';
			//$cname=isset($_POST['cname'])?in($_POST['cname']):'';
			$isCaptal=isset($_POST['isCaptal'])?intval($_POST['isCaptal']):'';
			$status=isset($_POST['status'])?intval($_POST['status']):'';
			if(empty($scpid)){
				echo json_encode(array("status"=>"error","msg"=>"请选择省份！"));exit;
			}
			
			if(in_array($scpid,$this->territories)){ // 是直辖市
				$scid=$scpid;
				$scpid=0;
			}
			$row = $stateCity_dao->getScnameByScid($scid);
			if(empty($scid) || empty($row)){
				echo json_encode(array("status"=>"error","msg"=>"请选择城市！"));exit;
			}elseif($this->erp_model->table(T_ARTICLE_CITIES)->where("sc_id=".$scid)->count()){
				echo json_encode(array("status"=>"error","msg"=>"该城市已存在！"));exit;
			}
			$cname = str_replace('市','',$row['sc_name']);
			/*
			if(empty($cname)){
				echo json_encode(array("status"=>"error","msg"=>"请填写城市名！"));exit;
			}elseif(strpos($cname,'市')){
				echo json_encode(array("status"=>"error","msg"=>"城市名不要包含‘市’，否则无法请求数据！"));exit;
			}
			*/
		    
			$ins_data=array(
				'sc_pid'=>$scpid,
				'sc_id'=>$scid,
				'acName'=>$cname,
				'acIsCapital'=>$isCaptal,
				'acStatus'=>$status
			);
			$ins_res=$this->erp_model->table(T_ARTICLE_CITIES)->data($ins_data)->insert();
			if($ins_res){
				echo json_encode(array("status"=>"success","msg"=>"添加成功！","closePopup"=>1,"isRefresh"=>1));exit;
			}else{
				echo json_encode(array("status"=>"error","msg"=>"添加失败！"));exit;
			}
		}
		$sc_list=$stateCity_dao->getScListByScpid(0);
		$this->assign("sc_list",$sc_list);
		$this->display();
	}
	
	function edit(){
		if(isset($_POST)&&!empty($_POST)){
			$acid=isset($_POST['acid'])?intval($_POST['acid']):'';
			$isCaptal=isset($_POST['isCaptal'])?intval($_POST['isCaptal']):'';
			//$cname=isset($_POST['cname'])?in($_POST['cname']):'';
			if(empty($acid)){
				echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
			}
			$status=isset($_POST['status'])?intval($_POST['status']):'';
			/**
			if(empty($cname)){
				echo json_encode(array("status"=>"error","msg"=>"请填写城市名！"));exit;
			}elseif(strpos($cname,'市')){
				echo json_encode(array("status"=>"error","msg"=>"城市名不要包含‘市’，否则无法请求数据！"));exit;
			}
			**/
			$ins_data=array(
				'acIsCapital'=>$isCaptal,
				'acStatus'=>$status
			);
			$ins_res=$this->erp_model->table(T_ARTICLE_CITIES)->data($ins_data)->where("ac_id=".$acid)->update();
			if($ins_res){
				echo json_encode(array("status"=>"success","msg"=>"修改成功！","closePopup"=>1,"isRefresh"=>1));exit;
			}else{
				echo json_encode(array("status"=>"error","msg"=>"修改失败！"));exit;
			}
		}
		$acid=isset($_GET['acid'])?intval($_GET['acid']):'';
		if(empty($acid)){
			$this->ajax_error("空参数错误!");
		}else{
			$info=$this->erp_model->table(T_ARTICLE_CITIES)->where("ac_id=".$acid)->find();
			$stateCity_dao=new StateCity();
			$info['city']=$stateCity_dao->getCityNameById($info['sc_id']);
			
			if($info){
				$this->assign('info',$info);
			}else{
				$this->ajax_error("查无此项!");
			}
		}
		$this->display();
	}
	
	function del(){
		$ids_str=isset($_GET['kid'])?in($_GET['kid']):'';
		if(empty($ids_str)){
			echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
		}else{
			/*** 检查是否有要删除城市的新闻数据 start ***/
			$msg='';
			if($result=$this->erp_model->table(T_ARTICLE_CITIES)->field('sc_id,ac_id')->where("ac_id IN({$ids_str})")->select()){
				$data=array();
				foreach($result as $rs){
					$data[$rs['sc_id']]=$rs['ac_id'];
				}
				$scids=implode(',',array_keys($data));
				if($result=$this->erp_model->table(T_ARTICLES)->field('sc_id')->where("sc_id IN({$scids})")->group('sc_id')->select()){
					$msg='，但有部分栏目有新闻数据，无法删除';
					foreach($result as $rs){
						unset($data[$rs['sc_id']]);
					}
				}
				$ids_str=implode(',',$data);
			}
			/*** 检查是否有要删除城市的新闻数据 end ***/
			if($ids_str){
				$this->erp_model->table(T_ARTICLE_CITIES)->where("ac_id IN({$ids_str})")->delete();
			}else{
				echo json_encode(array("status"=>"error","msg"=>"删除失败，删除项已有新闻数据！"));exit;
			}
			echo json_encode(array("status"=>"success","msg"=>"删除成功{$msg}！","isRefresh"=>1,"closePopup"=>1));exit;
		}
	}

	/**
	 * @author	Daniol
	 * @desc	ajax获取地区列表
	 */
	public function ajax_select_statecity(){
		$scpid=intval($_GET['sc_pid']);
		if(empty($scpid)){
			$this->ajax_error("空参数错误！");
		}
		
		$isTerritory=0;
		if(in_array($scpid,$this->territories)){
			$isTerritory=1;
		}
		$stateCity_dao=new StateCity();
		$sc_list=$stateCity_dao->getScListByScpid($scpid);
		
		$this->assign('sc_list',$sc_list);
		$this->assign('isTerritory',$isTerritory);
		$this->display();
	}
}