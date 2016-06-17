<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		jiang <jiang@100msh.com>
 * @date		2014-10-11
 * @desc		文章管理
 */
class newsMod extends commonMod{
	private $newscate_dao;
	private $news_dao;
	private $at_id;
	public function __construct(  ){
		parent::__construct();
		$this->news_dao=new News();
		$this->at_id=$this->news_dao->getArticleTypesById();
	}
	public function index(){
		$page = isset($_GET['page'])&&!empty($_GET['page']) ? intval($_GET['page']) : 1;//当前页
		$s_n_name     = isset($_GET['s_n_name']) ? in($_GET['s_n_name']) : '';//文章标题
		$s_n_n     = isset($_GET['s_n_n']) ? in($_GET['s_n_n']) : '';//文章标题
		$s_n_c     = isset($_GET['s_n_c']) ? in($_GET['s_n_c']) : '';//文章分类
		$where='1';
		if(!empty($s_n_name)){
			$where.=" and n.news_title like '%$s_n_name%'";
		}
		if(!empty($s_n_n)){
			$where.=" and n.news_title like '%$s_n_n%'";
		}
		
		$filter = array(
			's_n_name'  => $s_n_name,
			's_n_n'     => $s_n_n,
		);
		$numPerPage = 10;//每页显示数量
		$start      = ($page - 1) * $numPerPage;
		$news_list  = $this->news_dao->getList($where,$start,$numPerPage);
		$news_count = $this->news_dao->getCount($where);
		$page_string   = $this->page("news/index", $news_count,$numPerPage,5,4);
		$this->assign("news_list", $news_list);
		$this->assign("page_string", $page_string);
		$this->assign("filter", $filter);
		$this->assign("news_url", App::$config['official_site_url']);
		$this->display();
	}
	public function add(){
		if(isset($_POST)&&!empty($_POST)){
			$news_title=isset($_POST['news_title'])?in($_POST['news_title']):'';
			$news_cate_id=isset($_POST['news_cate_id'])?intval($_POST['news_cate_id']):0;
			$news_source=isset($_POST['news_source'])?in($_POST['news_source']):'';
			$news_author=isset($_POST['news_author'])?in($_POST['news_author']):'';
			$news_content=isset($_POST['news_content'])?in($_POST['news_content']):'';
			$digest = isset($_POST['digest']) ? in($_POST['digest']) : "";
			$news_tag=isset($_POST['news_tag'])?in($_POST['news_tag']):'';
			$news_date=isset($_POST['news_date'])?in($_POST['news_date']):'';
			$is_display=isset($_POST['is_show'])?intval($_POST['is_show']):1;
			if(empty($news_title)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章标题！" ));exit;}
			if(empty($news_cate_id)){echo json_encode(array('status' =>'error' ,'msg'=>"请选择文章分类！" ));exit;}
			// if(empty($news_source)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章来源！" ));exit;}
			if(empty($news_author)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章作者！" ));exit;}
			if(empty($news_tag)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章标签！" ));exit;}
			if(empty($news_date)){echo json_encode(array('status' =>'error' ,'msg'=>"请填写发表日期！" ));exit;}
			if(empty($news_content)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章内容！" ));exit;}
			preg_match_all("/./us", $news_title, $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'文章标题长度过长，长度请少于30个字！'));exit;
			}
			$check_title=$this->news_dao->checkNewsTitle($news_title);
			if($check_title==1){echo json_encode(array('status' =>'error' ,'msg'=>"文章标题已存在，请更换！" ));exit;}
			$ins_data=array(
				"news_title"=>$news_title,
				"news_cate_id"=>$news_cate_id,
				"news_source"=>$news_source,
				"news_tag"=>$news_tag,
				"digest"=>$digest,
				"news_addtime"=>time(),
				"is_display"=>$is_display,
				"news_author"=>$news_author,
				"news_content"=>$news_content,
				"user_id"=>$this->user_id,
			);
			//同步到导航新闻数据中
			
			$data_a = array(
				'at_id'=>$this->at_id,
				'aTitle'=>$news_title,
				'aSubTitle'=>$news_title,
				'aDigest'=>$digest,
				'aTags'=>$news_tag,								
				'aContent'=>$news_content,	
				'aStatus'=>$is_display,		
			);
			if(!empty($news_date)){
				$ins_data['news_date']=strtotime($news_date);
				$data_a['aPublishTime']=strtotime($news_date);
			}
			if(empty($_FILES['show_img']['name'])){
				echo json_encode(array('status' =>'error' ,'msg'=>"请上传文章缩略图！" ));exit;
			}
			$UpFileDir=App::$config['ACCESSORY_FOLDER']."official_attach/";
			$this->upload_obj=new Upload();
			$fileNamge="show_img";
			$ext = strtolower(strrchr($_FILES["show_img"]["name"], "."));
			$size = getimagesize($_FILES["show_img"]['tmp_name']);
			$this->upload_obj->UpFileAttribute($fileNamge);
			$UpFileName='img_'.time();//无文件后缀
			$MaxSize=MAX_PICFILE;
			$FileType=$FileType=array('.png','.jpg','.jpeg','.bmp');//后缀注意带上.
			$info=$this->upload_obj->Uploads($fileNamge,$UpFileDir,$UpFileName,$MaxSize,$FileType,null);
			switch ($info){
				case -1:
					echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));exit;
					break;
				case -2:
					echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
					break;
				default:
					break;
			}
			$file_path=App::$config['ACCESSORY_URL']."official_attach/". $UpFileName.$ext;
			$ins_data['show_img']=$file_path;			
			$data_a['aImgs']=serialize($file_path);
			$ins_result=$this->news_dao->add($ins_data);
			if($ins_result){
				$data_a['news_id']=$ins_result;
				$this->news_dao->addSynchronousArticles($data_a);
				//日志的管理
				$log_content="";
				$log_content[] = "news_title:" . $news_title;
				$log_content[] = "news_cate_id:" . $news_cate_id;
				$log_content[] = "news_source:" . $news_source;
				$log_content[] = "news_tag:" . $news_tag;
				$log_content[] = "digest:" . $digest;
				$log_content[] = "is_display:" . $is_display;
				$log_content[] = "news_author:" . $is_display;
				$log_content[] = "news_content:" . $is_display;
				$log_content[] = "user_id:" . $this->user_id;
				$log_content = implode(" , ", $log_content);
				$operationLogDAO_obj=operationLogDAO::getInstance();
				$operationLogDAO_obj->create_log('百米官网文章管理--添加文章',$log_content,'LC003',"LOT001");
				$out_data=array('status'=>"success",'msg'=>'文章添加成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				if(file_exists($UpFileDir.$UpFileName.$ext)){
					@unlink($UpFileDir.$UpFileName.$ext);
				}
				$out_data=array('status'=>"error",'msg'=>'文章添加失败！');
			}
			echo json_encode($out_data);exit;
		}
		$cate_list=$this->news_dao->getCateList();
		$KindEditor_obj=new KindEditor();
		$editor=$KindEditor_obj->create_editor("news_content",null,260,"editor");
		$this->assign('editor',$editor);
		$this->assign('cate_list',$cate_list);
		
		$this->display();
	}
	public function edit(){
		if(isset($_POST)&&!empty($_POST)){			
			$news_id=isset($_POST['news_id'])?intval($_POST['news_id']):'';
			if(empty($news_id)){echo json_encode(array('status' =>'error' ,'msg'=>"空参数错误" ));exit;}
			$news_title=isset($_POST['news_title'])?in($_POST['news_title']):'';
			$news_cate_id=isset($_POST['news_cate_id'])?intval($_POST['news_cate_id']):0;
			$news_source=isset($_POST['news_source'])?in($_POST['news_source']):'';
			$news_author=isset($_POST['news_author'])?in($_POST['news_author']):'';
			$news_content=isset($_POST['news_content'])?in($_POST['news_content']):'';
			$digest = isset($_POST['digest']) ? in($_POST['digest']) : "";
			$old_show_img=isset($_POST['old_show_img'])?in($_POST['old_show_img']):'';
			$news_tag=isset($_POST['news_tag'])?in($_POST['news_tag']):'';
			$news_date=isset($_POST['news_date'])?in($_POST['news_date']):'';
			$is_display=isset($_POST['is_show'])?intval($_POST['is_show']):1;
			if(empty($news_title)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章标题！" ));exit;}
			if(empty($news_cate_id)){echo json_encode(array('status' =>'error' ,'msg'=>"请选择文章分类！" ));exit;}
			// if(empty($news_source)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章来源！" ));exit;}
			if(empty($news_author)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章作者！" ));exit;}
			if(empty($news_tag)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章标签！" ));exit;}
			if(empty($news_date)){echo json_encode(array('status' =>'error' ,'msg'=>"请填写发表日期！" ));exit;}
			if(empty($news_content)){echo json_encode(array('status' =>'error' ,'msg'=>"请输入文章内容！" ));exit;}
			preg_match_all("/./us", $_POST['news_title'], $match);
			if( count($match[0])>30){
				echo json_encode(array('status'=>"error",'msg'=>'文章标题长度过长，长度请少于30个字！'));exit;
			}
			$check_title=$this->news_dao->checkNewsTitle($news_title,$news_id);
			if($check_title==1){echo json_encode(array('status' =>'error' ,'msg'=>"文章标题已存在，请更换！" ));exit;}
			$ins_data=array(
				"news_title"=>$news_title,
				"news_cate_id"=>$news_cate_id,
				"news_source"=>$news_source,
				"digest"=>$digest,
				"news_tag"=>$news_tag,
				"news_uptime"=>time(),
				"is_display"=>$is_display,
				"news_author"=>$news_author,
				"news_content"=>$news_content,
				"user_id"=>$this->user_id,
			);
			//同步到导航新闻数据中			
			$data_a = array(	
				'at_id'=>$this->at_id,			
				'aTitle'=>$news_title,
				'aSubTitle'=>$news_title,
				'aDigest'=>$digest,
				'aTags'=>$news_tag,								
				'aContent'=>$news_content,	
				'aStatus'=>$is_display,		
			);
			if(!empty($news_date)){
				$ins_data['news_date']=strtotime($news_date);
				$data_a['aPublishTime']=strtotime($news_date);
			}
			if(isset($_FILES['show_img']['name'])&&!empty($_FILES['show_img']['name'])){
				$UpFileDir=App::$config['ACCESSORY_FOLDER']."official_attach/";
				$this->upload_obj=new Upload();
				$fileNamge="show_img";
				$ext = strtolower(strrchr($_FILES["show_img"]["name"], "."));
				$size = getimagesize($_FILES["show_img"]['tmp_name']);
				$this->upload_obj->UpFileAttribute($fileNamge);
				$UpFileName='img_'.time();//无文件后缀
				$MaxSize=MAX_PICFILE;
				$FileType=$FileType=array('.png','.jpg','.jpeg','.bmp');//后缀注意带上.
				$info=$this->upload_obj->Uploads($fileNamge,$UpFileDir,$UpFileName,$MaxSize,$FileType,null);
				switch ($info){
					case -1:
						echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片类型不正确！请上传类型为png,jpg,jpeg,bmp的文件！"));exit;
						break;
					case -2:
						echo json_encode(array('status'=>"error",'msg'=>"图片上传失败:上传的图片大小不符合要求！请上传小于".$MaxSize."(KB)的文件！"));exit;
						break;
					default:						
						break;
				} 
				$file_path=App::$config['ACCESSORY_URL']."official_attach/". $UpFileName.$ext;
				$ins_data['show_img']=$file_path;
				$data_a['aImgs']=serialize($file_path);
			}
			$ins_result=$this->news_dao->edit($ins_data,$news_id);
			if($ins_result==1){
				if(isset($_FILES['show_img']['name'])&&!empty($_FILES['show_img']['name'])){
					$arr=explode("/",$old_show_img);
					$len=count($arr);
					$old_show_img=$arr[$len-1];
					if(file_exists($UpFileDir.$old_show_img)){
						@unlink($UpFileDir.$old_show_img);
					}
				}
				//同步至导航新闻中
				$this->news_dao->editSynchronousArticles($data_a, $news_id);				

				$out_data=array('status'=>"success",'msg'=>'文章修改成功！',"isRefresh"=>1,"closePopup"=>1);
			}else{
				if(file_exists($UpFileDir.$UpFileName.$ext)){
					@unlink($UpFileDir.$UpFileName.$ext);
				}
				$out_data=array('status'=>"error",'msg'=>'文章修改失败！');
			}
			echo json_encode($out_data);exit;
		}
		
		//将官网新闻同步到网易新闻，栏目为百米动态(at_id),首次需先打开同步好数据
//		$result = $this->news_dao->newsSynchronousArticles();
//		if($result){
//			json_encode(array('status'=>"success",'msg'=>'百米动态新闻同步成功！')); 
//		}else{
//			json_encode(array('status'=>"error",'msg'=>'百米动态新闻同步失败！')); 
//		}
		
		$news_id=$_GET['news_id']?intval($_GET['news_id']):'';
		if(empty($news_id)){$this->ajax_error("空参数错误!");}
		$info=$this->news_dao->getNewsInfo($news_id);
		if(empty($info)){$this->ajax_error("查无此条!");}
		$cate_list=$this->news_dao->getCateList();
		$this->assign('cate_list',$cate_list);
		$this->assign('info',$info);
		$KindEditor_obj=new KindEditor();
		$editor=$KindEditor_obj->create_editor("news_content",null,260,"editor");
		$this->assign('editor',$editor);
		$this->assign("news_url", App::$config['official_site_url']);
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
			if($result=$this->news_dao->del($id)){
				$result = $this->news_dao->delSynchronousArticles($id);
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
			echo json_encode(array('status'=>"error",'msg'=>'文章有部分未删除！',));exit;
		}elseif($suc){
			echo json_encode(array('status'=>"success",'msg'=>'文章删除成功！',"isRefresh"=>1,"closePopup"=>1));exit;
		}else{
			echo json_encode(array('status'=>"error",'msg'=>'文章删除失败！',));exit;
		}
	}
	public function detail(){
		$news_id=isset($_GET['news_id'])?intval($_GET['news_id']):'';
		if(empty($news_id)){
			$this->ajax_error("参数有问题,请检查!");
		}
		$detail_list=$this->news_dao->getNewsInfo($news_id);
		if(empty($detail_list)){$this->ajax_error("没有查到此条数据,请检查!");}
		$this->admusers_dao = new Admusers();
		$sys_list = $this->admusers_dao->getUserList($detail_list['user_id']);
		$detail_list['user_name']=isset($sys_list[$detail_list['user_id']]) ? $sys_list[$detail_list['user_id']]['username'] : '';
		$detail_list['news_content']=htmlspecialchars_decode($detail_list['news_content']);
		$this->assign("info", $detail_list);
		$this->assign("news_url", App::$config['official_site_url']);
		$this->display();
	}
	public function infos(){
		$news_id=isset($_GET['news_id'])?intval($_GET['news_id']):'';
		if(empty($news_id)){$this->ajax_error("详细信息参数有问题");}
		$detail_list=$this->news_dao->getNewsInfo($news_id);
		if(empty($detail_list)){$this->ajax_error("没有查到此条数据,请检查!");}
		$this->admusers_dao = new Admusers();
		$sys_list = $this->admusers_dao->getUserList($detail_list['user_id']);
		$detail_list['user_name']=isset($sys_list[$detail_list['user_id']]) ? $sys_list[$detail_list['user_id']]['username'] : '';
		$this->assign("info", $detail_list);
		$this->display();
	}
	public function changeDisplay(){
		$news_id=isset($_POST['id'])?intval($_POST['id']):0;
		$flag=isset($_POST['f'])?intval($_POST['f']):1;//1置顶 2取消置顶
		if(empty($news_id)){
			echo json_encode(array('status'=>"error",'msg'=>'空参数错误！',));exit;
		}
		switch ($flag) {
			case 2:
				$result=$this->news_dao->changetop($news_id,2);
				break;
			default:
				$result=$this->news_dao->changetop($news_id,1);
				break;
		}
		if($result){
			echo json_encode(array('status'=>"success",'msg'=>'修改成功！',"isRefresh"=>1));exit;
		}else{
			echo json_encode(array('status'=>"error",'msg'=>'修改失败！',));exit;
		}
	}
	
	
	
	
	
	
	
	
}