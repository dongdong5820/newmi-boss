<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		sucd
 * @date		2016-05-31
 * @desc		文章分类页
 */
class newscateMod extends commonMod{
	public function __construct(  ){
		parent::__construct();
		$this->newscate_dao=new Newscate();
	}
	public function index(){
		$this->authMod();

		$page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;//当前页
		$s_n_c_name     = isset($_GET['s_n_c_name']) ? in($_GET['s_n_c_name']) : '';
		$s_n_c_n     = isset($_GET['s_n_c_n']) ? in($_GET['s_n_c_n']) : '';
		$where='1';
		if(!empty($s_n_c_name)){
			$where.=" and news_cate_title like '%$s_n_c_name%'";
		}
		if(!empty($s_n_c_n)){
			$where.=" and news_cate_title like '%$s_n_c_n%'";
		}
		$filter = array(
			's_n_c_name'  => $s_n_c_name,
			's_n_c_n'     => $s_n_c_n
		);
		$numPerPage = 10;//每页显示数量
		$start      = ($page - 1) * $numPerPage;
		$newscate_list  = $this->newscate_dao->getList($where,$start,$numPerPage);
		$newscate_count = $this->newscate_dao->getCount($where);
		$page_string   = $this->page("newscate/index", $newscate_count,$numPerPage,5,4);
		$this->assign("newscate_list", $newscate_list);
		$this->assign("page_string", $page_string);
		$this->assign("filter", $filter);
		$this->display();
	}
	public function add(){
		if(isset($_POST)&&!empty($_POST)){
			$news_cate_title=isset($_POST['news_cate_title'])?in($_POST['news_cate_title']):'';
			$news_cate_desc=isset($_POST['news_cate_desc'])?in($_POST['news_cate_desc']):'';
			$news_cate_order=isset($_POST['news_cate_order'])?intval($_POST['news_cate_order']):1;
			$news_cate_pid=isset($_POST['news_cate_pid'])?intval($_POST['news_cate_pid']):0;
			if(empty($news_cate_title)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入分类名称！" ));exit;}
			preg_match_all("/./us", $news_cate_title, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'分类名称长度过长，长度请少于30个字！'));exit;
			}
			$check_title=$this->newscate_dao->checkCateTitle($news_cate_title);
			if($check_title==1){echo json_encode(array('status' =>'error' ,'msg'=>"分类名称已存在，请更换！" ));exit;}
			$ins_data=array(
				"news_cate_title"=>$news_cate_title,
				"news_cate_order"=>$news_cate_order,
				"news_cate_pid"=>$news_cate_pid,
			);
			if(!empty($news_cate_desc)){
				$ins_data["news_cate_desc"]=$news_cate_desc;
			}
			$ins_result=$this->newscate_dao->add($ins_data);
			if($ins_result){
			    //日志的管理
			    $log_content="";
			    $log_content[] = "news_cate_title:" . $news_cate_title;
			    $log_content[] = "news_cate_order:" . $news_cate_order;
			    $log_content[] = "news_cate_pid:" . $news_cate_pid;
			    $log_content = implode(" , ", $log_content);
			    $operationLogDAO_obj=operationLogDAO::getInstance();
			    $operationLogDAO_obj->create_log('百米官网文章管理--添加文章分类',$log_content,'LC003',"LOT001");
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
			$news_cate_id=isset($_POST['news_cate_id'])?intval($_POST['news_cate_id']):'';
			if(empty($news_cate_id)){
				$out_data=array('status'=>"error",'msg'=>'空参数错误！');
			}
			$news_cate_title=isset($_POST['news_cate_title'])?in($_POST['news_cate_title']):'';
			$news_cate_desc=isset($_POST['news_cate_desc'])?in($_POST['news_cate_desc']):'';
			$news_cate_order=isset($_POST['news_cate_order'])?intval($_POST['news_cate_order']):1;
			// $is_show=isset($_POST['is_show'])?in($_POST['is_show']):1;
			if(empty($news_cate_title)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入分类名称！" ));exit;}
			preg_match_all("/./us", $news_cate_title, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'分类名称长度过长，长度请少于30个字！'));exit;
			}
			$check_title=$this->newscate_dao->checkCateTitle($news_cate_title,$news_cate_id);
			if($check_title==1){echo json_encode(array('status' =>'error' ,'msg'=>"分类名称已存在，请更换！" ));exit;}
			$ins_data=array(
				"news_cate_title"=>$news_cate_title,
				"news_cate_desc"=>$news_cate_desc,
				"news_cate_order"=>$news_cate_order,
				// "is_show"=>$is_show,
				// "news_cate_pid"=>$news_cate_pid,
			);
			$ins_result=$this->newscate_dao->edit($news_cate_id,$ins_data);
			if($ins_result){
			    //日志的管理
			    $log_content="";
			    $log_content[] = "news_cate_title:" . $news_cate_title;
			    $log_content[] = "news_cate_order:" . $news_cate_order;
			    $log_content[] = "news_cate_desc:" . $news_cate_desc;
			    $log_content = implode(" , ", $log_content);
			    $operationLogDAO_obj=operationLogDAO::getInstance();
			    $operationLogDAO_obj->create_log('百米官网文章管理--修改文章分类',$log_content,'LC003',"LOT002");
				$out_data=array('status'=>"success",'msg'=>'分类修改成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				$out_data=array('status'=>"error",'msg'=>'分类修改失败！');
			}
			echo json_encode($out_data);exit;
		}
		$news_cate_id=isset($_GET['news_cate_id'])?intval($_GET['news_cate_id']):'';
		if(empty($news_cate_id)){
			$this->ajax_error("空参数错误!");
		}else{
			$info=$this->newscate_dao->getInfo($news_cate_id);
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
			$del_result=$this->newscate_dao->del($all_ids);
			switch ($del_result) {
				case 1:
				    //日志的管理
				    $log_content="";
				    $log_content[] = "cate_id:" . $all_ids;
				    $log_content = implode(" , ", $log_content);
				    $operationLogDAO_obj=operationLogDAO::getInstance();
				    $operationLogDAO_obj->create_log('百米官网文章管理--删除文章分类',$log_content,'LC003',"LOT003");
					echo json_encode(array("status"=>"success","msg"=>"删除成功！","isRefresh"=>1));exit;
					break;
				case 2:
					echo json_encode(array("status"=>"success","msg"=>"所选的分类部分已经关联文章无法删除，未关联的删除成功！","isRefresh"=>1));exit;
					break;
				case -1:
					echo json_encode(array("status"=>"error","msg"=>"删除失败！"));exit;
					break;
				case -2:
					echo json_encode(array("status"=>"error","msg"=>"所选的分类都已经关联文章，无法删除！"));exit;
					break;
				default:
					echo json_encode(array("status"=>"error","msg"=>"删除失败！"));exit;
					break;
			}
		}
	}
}