<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		<chenzhandong@100msh.com>
 * @date		2014-10-11
 * @desc		栏目管理
 */
class mshArticleMod extends commonMod{
	public function __construct(){
		parent::__construct();
		$this->erp_model=APP::db()->ERP;
	}
	
	function index(){	    
		$stateCity_dao=new StateCity();		
		$this->authMod();//权限认证
		$page=isset($_GET['page'])&&!empty($_GET['page'])?intval($_GET['page']):1;//当前页
		$numPerPage=10;//每页显示数量
		$start=($page-1)*$numPerPage;
		$where='1';
		//关键词
		$s_search_key='';
		if(isset($_GET['s_search_key'])&&!empty($_GET['s_search_key'])){
			$s_search_key=in($_GET['s_search_key']);
			//$where.=" AND (INSTR(aTitle,'{$s_search_key}') OR INSTR(aDigest,'{$s_search_key}'))";
			$where.=" AND INSTR(aTitle,'{$s_search_key}')";
		}
		$s_at='';
		if(isset($_GET['s_at'])&&!empty($_GET['s_at'])){
			$s_at=intval($_GET['s_at']);
			if($s_at==10){
				$where.=" AND at_id=0";
			}else{
				$where.=" AND at_id=".$s_at;
			}
		}
		$s_city='';
		if(isset($_GET['s_city'])&&!empty($_GET['s_city'])){
			$s_city=intval($_GET['s_city']);
			$where.=" AND sc_id=".$s_city;
		}
		$s_recm='';
		if(isset($_GET['s_recm'])&&!empty($_GET['s_recm'])){
			$s_recm=$_GET['s_recm'];
			if(is_array($s_recm)){
				$condtmp='';
				foreach($s_recm as $recmind){
					if($recmind){
						$condtmp.="FIND_IN_SET({$recmind},aMSHRecommend)>0 OR ";
					}
				}
				if($condtmp){
					$condtmp=substr($condtmp,0,-4);
					$where.=" AND ({$condtmp})";
				}
			}
		}
		
		$hotOffers = isset($_GET['hotOffers']) ? strlen($_GET['hotOffers']) : '';
		if ($hotOffers) {
			$hotOffers = intval($_GET['hotOffers']);
			$where .= " AND hotOffers=".$hotOffers."";
			if($hotOffers==0){
				$hotOffers = "0";
			}
		}
		$s_status='';
		if(isset($_GET['s_status'])&&is_numeric($_GET['s_status'])){
			$s_status=$_GET['s_status'];
			$where.=" AND aStatus=".$s_status;
		}
		$recms=array(1=>'大图推荐 ',2=>' 热门 推荐 ');
		$list=$this->erp_model->table(T_ARTICLES)->where($where)->order('aPublishTime DESC')->limit("{$start},{$numPerPage}")->select();
		if($list){
			$atids=$scids=$types=$cities=array();
			foreach($list as $item){
				if($item['at_id']){$atids[$item['at_id']]=$item['at_id'];}
				if($item['sc_id']){$scids[$item['sc_id']]=$item['sc_id'];}
			}
			$atCodeArr = array();
			if(count($atids)){			    
				$atids=implode(',',$atids);
				$result=$this->erp_model->table(T_ARTICLE_TYPES)->field('at_id,atName,atCode')->where("at_id IN({$atids})")->select();
				foreach($result as $rs){
					$types[$rs['at_id']]=$rs['atName'];
					$atCodeArr[$rs['at_id']]=$rs['atCode'];
				}
			}
			if(count($scids)){
				$scids=implode(',',$scids);
				$data=$stateCity_dao->getCityListByScIds($scids);
				foreach($data as $dt){
					$cities[$dt['sc_id']]=$dt['sc_name'];
				}
			}
			$statuses=array(0=>'无效',1=>'有效',2=>'删除');
			foreach($list as $key=>$item){
				if(isset($types[$item['at_id']])){$list[$key]['tname']=$types[$item['at_id']];}
				else{$list[$key]['tname']='';}
				$list[$key]['status']=$statuses[$item['aStatus']];
				if(isset($cities[$item['sc_id']])){$list[$key]['city']=$cities[$item['sc_id']];}
				else{$list[$key]['city']='';}
				if($item['aMSHRecommend']){
					$recommends=explode(',',$item['aMSHRecommend']);
					$rec='';
					foreach($recommends as $recommend){
						$rec.=$recms[$recommend].',';
					}
					$list[$key]['recm']=substr($rec,0,-1);
				}else{$list[$key]['recm']='';}
				if($item['aImgs']){
					$imgs=unserialize(stripslashes($item['aImgs']));
					if(is_array($imgs)){
						$list[$key]['img']=$imgs[0];	
					}else{
						$list[$key]['img']=$imgs;
					}					
				}else{
					$list[$key]['img']='';
				}
				
				
				if(isset($atCodeArr[$item['at_id']])){$list[$key]['atCode']=$atCodeArr[$item['at_id']];}
				else{$list[$key]['atCode']='local';}
				
			}
		}

		$list_count=$this->erp_model->table(T_ARTICLES)->where($where)->count();
		$page_string=$this->page('mshArticle/index',$list_count,$numPerPage,5,4);
		
		$ats=array();
		$result=$this->erp_model->table(T_ARTICLE_TYPES)->field('at_id,atName')->where("atStatus=1")->select();
		foreach($result as $rs){
			$ats[$rs['at_id']]=$rs['atName'];
		}
		$cities=array();
		$scids='';
		if($data=$this->erp_model->table(T_ARTICLE_CITIES)->field('sc_id')->where('acStatus=1')->select()){
			foreach($data as $dt){
				$scids.=$dt['sc_id'].',';
			}
			$scids=substr($scids,0,-1);
			$data=$stateCity_dao->getCityListByScIds($scids);
			foreach($data as $dt){
				$cities[$dt['sc_id']]=$dt['sc_name'];
			}
		}
		
		$this->assign("s_search_key",$s_search_key);
		$this->assign("s_at",$s_at);
		$this->assign("s_city",$s_city);
		$this->assign("s_recm",$s_recm);
		$this->assign("s_status",$s_status);
		
		$this->assign('recms',$recms);
		$this->assign('hotOffers',$hotOffers);
		
		$this->assign("news_url", App::$config['nav_site_url']);
		
		$this->assign("list",$list);
		$this->assign("ats",$ats);
		$this->assign("cities",$cities);
		$this->assign("page_string",$page_string);
		$this->display();
	}
	
	function edit(){
		if(isset($_POST)&&!empty($_POST)){
			$aid=isset($_POST['aid'])?intval($_POST['aid']):'';
			$recm=isset($_POST['recm'])?in($_POST['recm']):'';
			$at_id = isset($_POST['at_id']) ? intval($_POST['at_id']) : "";
			$sc_id = isset($_POST['sc_id']) ? intval($_POST['sc_id']) : 0;
			$hotOffers = isset($_POST['hotOffers']) ? intval($_POST['hotOffers']):'';			
			if(empty($aid)){
				echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
			}
			
			$status=isset($_POST['status'])?intval($_POST['status']):'';
			if($recm){$recm=implode(',',$recm);}
			$ins_data=array(
				'aStatus'=>$status,
				'aMSHRecommend'=>$recm,
				'hotOffers'=>$hotOffers
			);

			$where ="";
			if($hotOffers==1){
				$where = " hotOffers=".$hotOffers;
			}
			if($hotOffers==2){
				$where = " hotOffers=".$hotOffers;	
			}
			$count = 0;
			if(!empty($where)){
				if(!empty($at_id) && $at_id){
					$where .= " AND at_id=".$at_id;
				}
				if(!empty($sc_id) && $sc_id){
					$where .= " AND sc_id=".$sc_id;
				}
				
				$count = $this->erp_model->table(T_ARTICLES)->where($where)->count();
				if($hotOffers==1 && $count>=1 ){
					echo json_encode(array("status"=>"error","msg"=>"修改失败,当前栏目首页大图推荐不能超过1条！"));exit;
				}
				if($hotOffers==2 && $count>=5 ){
					echo json_encode(array("status"=>"error","msg"=>"修改失败，当前栏目热门推荐不能超过5条！"));exit;	
				}				
			}
			$ins_res=$this->erp_model->table(T_ARTICLES)->data($ins_data)->where("a_id=".$aid)->update();
			if($ins_res){
				echo json_encode(array("status"=>"success","msg"=>"修改成功！","closePopup"=>1,"isRefresh"=>1));exit;
			}else{
				echo json_encode(array("status"=>"error","msg"=>"修改失败！"));exit;
			}
		}
		$aid=isset($_GET['aid'])?intval($_GET['aid']):'';
		if(empty($aid)){
			$this->ajax_error("空参数错误!");
		}else{			
			$info=$this->erp_model->table(T_ARTICLES)->field('a_id,aMSHRecommend,aStatus,at_id,sc_id,hotOffers')->where("a_id=".$aid)->find();
			if($info){
				$info['aMSHRecommend']=explode(',',$info['aMSHRecommend']);
				$this->assign('info',$info);
			}else{
				$this->ajax_error("查无此项!");
			}
		}
		$recms=array(1=>'大图推荐&nbsp;&nbsp; ',2=>' 热门 推荐 ');
		$this->assign('recms',$recms);
		$this->assign("news_url", App::$config['nav_site_url']);
		$atCode = isset($_GET['atCode']) ? in($_GET['atCode']) : "";
		$this->assign("atCode", $atCode);
		$this->display();
	}
	
	function detail(){
	    $aid=isset($_GET['aid'])?intval($_GET['aid']):'';		
		if(empty($aid)){
			$this->ajax_error("空参数错误!");
		}else{
			$sql="SELECT ac.a_id,aTitle,aIsDown,aContent,aContentPhotos,aContentVideos,ac.at_id,ac.sc_id FROM ".$this->erp_model->pre.T_ARTICLES." ac"
				." LEFT JOIN ".$this->erp_model->pre.T_ARTICLE_CONTENT." acd ON ac.a_id=acd.a_id"
				." LEFT JOIN ".$this->erp_model->pre.T_ARTICLE_ATTACHEMENTS." aca ON ac.a_id=aca.a_id WHERE ac.a_id=".$aid;
			$data=$this->erp_model->query($sql);
			
			$info=$data[0];
			if($info['aContentPhotos']){
				$photos=unserialize(stripslashes($info['aContentPhotos']));
				/*$str='';
				if($info['aIsDown']==1){
					$str='http://192.168.0.8:81/100msh_upload/navnews/';
				}*/
				foreach($photos as $photo){
					$info['aContent']=str_replace($photo['ref'],'<img src="'.$photo['src'].'" alt="'.$photo['alt'].'" width="650">',$info['aContent']);
				}
			}else{
				$info['aContent']=html_out($info['aContent']);
			}
			if($info){
				$this->assign('info',$info);
			}else{
				$this->ajax_error("查无此项!");
			}
		}
		$atCode = isset($_GET['atCode']) ? in($_GET['atCode']) : "";
		$this->assign("atCode", $atCode);
		$this->assign("news_url", App::$config['nav_site_url']);
		$this->display();
	}
	
	function del(){
		$ids_str=isset($_GET['kid'])?in($_GET['kid']):'';
		if(empty($ids_str)){
			echo json_encode(array("status"=>"error","msg"=>"空参数错误！"));exit;
		}else{
			if($ids_str){
				$this->erp_model->table(T_ARTICLES)->where("a_id IN({$ids_str})")->delete();
				$this->erp_model->table(T_ARTICLE_CONTENT)->where("a_id IN({$ids_str})")->delete();
				$this->erp_model->table(T_ARTICLE_ATTACHEMENTS)->where("a_id IN({$ids_str})")->delete();
			}else{
				echo json_encode(array("status"=>"error","msg"=>"删除失败！"));exit;
			}
			echo json_encode(array("status"=>"success","msg"=>"删除成功！","isRefresh"=>1,"closePopup"=>1));exit;
		}
	}
}