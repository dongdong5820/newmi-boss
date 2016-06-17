<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		<chenzhandong@100msh.com>
 * @date		2014-10-11
 * @desc		栏目管理
 */
class mshArticleTypeMod extends commonMod{
	public $erp_model;
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
			//$where.=" AND (INSTR(atName,'{$s_search_key}') OR INSTR(atCode,'{$s_search_key}'))";
			$where.=" AND INSTR(atName,'{$s_search_key}')";
		}
		$s_status='';
		if(isset($_GET['s_status'])&&is_numeric($_GET['s_status'])){
			$s_status=in($_GET['s_status']);
			$where.=" AND atStatus=".$s_status;
		}
		
		$list=$this->erp_model->table(T_ARTICLE_TYPES)->where($where)->limit("{$start},{$numPerPage}")->select();
		
		$statuses=array(0=>'无效',1=>'有效');
		foreach($list as $key=>$item){
			$list[$key]['status']=$statuses[$item['atStatus']];
		}
		
		$list_count=$this->erp_model->table(T_ARTICLE_TYPES)->where($where)->count();
		$page_string=$this->page('mshArticleType',$list_count,$numPerPage,5,4);
		
		$this->assign("s_search_key",$s_search_key);
		$this->assign("s_status",$s_status);
		
		$this->assign("list",$list);
		$this->assign("page_string",$page_string);		
		$this->display();
	}
	
	function add(){
		if(isset($_POST)&&!empty($_POST)){
			$name=isset($_POST['name'])?in($_POST['name']):'';
			$code=isset($_POST['code'])?in($_POST['code']):'';
			$sort=isset($_POST['sort'])?in($_POST['sort']):'';
			$status=isset($_POST['status'])?intval($_POST['status']):'';
			$hotOffers = isset($_POST['hotOffers']) ? intval($_POST['hotOffers']) : 0;
			if($hotOffers==1){
				$count = $this->erp_model->table(T_ARTICLE_TYPES)->where("hotOffers=".$hotOffers."")->count();
				if($count>=5){
					echo json_encode(array("status"=>"error","msg"=>"添加失败，首页栏目推荐不能超过5个！"));exit;
				}
			}
			if(empty($name)){
				echo json_encode(array("status"=>"error","msg"=>"请填写栏目名称！"));exit;
			}
			if(empty($code)){
				echo json_encode(array("status"=>"error","msg"=>"请填写栏目编号！"));exit;
			}
			$ins_data=array(
				'atName'=>$name,
				'atCode'=>$code,
				'atSort'=>$sort,
				'atStatus'=>$status,
				'hotOffers'=>$hotOffers
			);
			$ins_res=$this->erp_model->table(T_ARTICLE_TYPES)->data($ins_data)->insert();
			if($ins_res){
				echo json_encode(array("status"=>"success","msg"=>"添加成功！","closePopup"=>1,"isRefresh"=>1));exit;
			}else{
				echo json_encode(array("status"=>"error","msg"=>"添加失败！"));exit;
			}
		}
		$this->display();
	}
	
	function edit(){
		if(isset($_POST)&&!empty($_POST)){
			$atid=isset($_POST['atid'])?intval($_POST['atid']):'';
			if(empty($atid)){
				echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
			}
			
			$name=isset($_POST['name'])?in($_POST['name']):'';
			$code=isset($_POST['code'])?in($_POST['code']):'';
			$sort=isset($_POST['sort'])?in($_POST['sort']):'';
			$status=isset($_POST['status'])?intval($_POST['status']):'';
			$hotOffers = isset($_POST['hotOffers'])?intval($_POST['hotOffers']):0;
			if($hotOffers==1){
				$count = $this->erp_model->table(T_ARTICLE_TYPES)->where("hotOffers=".$hotOffers."")->count();
				if($count>=5){
					echo json_encode(array("status"=>"error","msg"=>"修改失败，首页栏目推荐不能超过5个！"));exit;
				}
			}
			if(empty($name)){
				echo json_encode(array("status"=>"error","msg"=>"请填写栏目名称！"));exit;
			}
			if(empty($code)){
				echo json_encode(array("status"=>"error","msg"=>"请填写栏目编号！"));exit;
			}
			$ins_data=array(
				'atName'=>$name,
				'atCode'=>$code,
				'atSort'=>$sort,
				'atStatus'=>$status,
				'hotOffers'=>$hotOffers
			);
			$ins_res=$this->erp_model->table(T_ARTICLE_TYPES)->data($ins_data)->where("at_id=".$atid)->update();
			if($ins_res){
				echo json_encode(array("status"=>"success","msg"=>"修改成功！","closePopup"=>1,"isRefresh"=>1));exit;
			}else{
				echo json_encode(array("status"=>"error","msg"=>"修改失败！"));exit;
			}
		}
		$atid=isset($_GET['atid'])?intval($_GET['atid']):'';
		if(empty($atid)){
			$this->ajax_error("空参数错误!");
		}else{
			$info=$this->erp_model->table(T_ARTICLE_TYPES)->where("at_id=".$atid)->find();
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
			/*** 检查是否有要删除栏目的新闻数据 start ***/
			$msg='';
			if($result=$this->erp_model->table(T_ARTICLES)->field('at_id')->where("at_id IN({$ids_str})")->group('at_id')->select()){
				$msg='，但有部分栏目有新闻数据，无法删除';
				$atids=array_flip(explode(',',$ids_str));
				foreach($result as $rs){
					unset($atids[$rs['at_id']]);
				}
				$ids_str=implode(',',$atids);
			}
			/*** 检查是否有要删除栏目的新闻数据 start ***/
			if($ids_str){
				$this->erp_model->table(T_ARTICLE_TYPES)->where("at_id IN({$ids_str})")->delete();
			}else{
				echo json_encode(array("status"=>"error","msg"=>"删除失败，删除项已有新闻数据！"));exit;
			}
			echo json_encode(array("status"=>"success","msg"=>"删除成功{$msg}！","isRefresh"=>1,"closePopup"=>1));exit;
		}
	}
}