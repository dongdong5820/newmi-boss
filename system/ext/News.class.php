<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		jiang <jiang@100msh.com>
 * @date		2014-10-11
 * @desc		文章管理类
 */
class News{
	private $erp_model;
	private $prefix;
	public function __construct(  ){
		$this->erp_model = App::db()->ERP;
        $this->prefix = $this->erp_model->pre;
	}
	public function getList($where,$start,$numPerPage){
		$sql="SELECT n.*,c.news_cate_title FROM ".$this->prefix.T_NEWS." as n left join ".$this->prefix.T_NEWS_CATE." as c on n.news_cate_id=c.news_cate_id where $where order by n.news_date DESC,n.news_id DESC limit $start,$numPerPage";
		$list=$this->erp_model->query($sql);
		return $list;
	}
	public function getCount($where){
		$sql="SELECT count(*) as count FROM ".$this->prefix.T_NEWS." as n  where $where";
		$count=$this->erp_model->query($sql);
		return $count[0]['count'];
	}
	public function add($data){
		$news_content=$data['news_content'];
		unset($data['news_content']);
		$news_id=$this->erp_model->table(T_NEWS)->data($data)->insert();
		if(!empty($news_id)){
			$result=$this->erp_model->table(T_NEWS_CONTENT)->data(array("news_content"=>$news_content,"news_id"=>$news_id))->insert();			
		}
		if($result){
			return $news_id;
		}else{
			return 0;
		}
		
	}
	public function edit($data,$news_id){
		$news_content=$data['news_content'];
		unset($data['news_content']);
		$news_result=$this->erp_model->table(T_NEWS)->data($data)->where("news_id=".$news_id)->update();
		if(!empty($news_result)){
			$result=$this->erp_model->table(T_NEWS_CONTENT)->data("news_content='$news_content'")->where("news_id=".$news_id)->update();
			return 1;
		}else{
			return -1;
		}
	}
	public function del($id){
		$info=$this->erp_model->table(T_NEWS)->field("show_img")->where("news_id=$id")->find();
		$result_news=$this->erp_model->table(T_NEWS)->where("news_id =$id")->delete();
		$result_content=$this->erp_model->table(T_NEWS_CONTENT)->where("news_id =$id")->delete();
		if($result_news&&$result_content){
			$UpFileDir=App::$config['ACCESSORY_FOLDER']."official_attach/";
			$arr=explode("/",$info['show_img']);
			$len=count($arr);
			$show_img=$arr[$len-1];
			if(file_exists($UpFileDir.$show_img)){
				@unlink($UpFileDir.$show_img);
			}
			return 1;
		}else{
			return -1;
		}
	}
	public function checkNewsTitle($news_title,$news_id=null){
		if($news_title==null){
			$result=$this->erp_model->table(T_NEWS)->where("news_title='$news_title'")->find();
		}else{
			$result=$this->erp_model->table(T_NEWS)->where("news_title='$news_title' and news_id<>'$news_id'")->find();
		}
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}
	public function getCateList(){
		$list=$this->erp_model->table(T_NEWS_CATE)->field("news_cate_id,news_cate_title")->order("news_cate_order ASC")->select();
		return $list;
	}
	public function getNewsInfo($news_id){
		$sql="SELECT n.*,c.news_content FROM ".$this->prefix.T_NEWS." as n ,".$this->prefix.T_NEWS_CONTENT." as c where n.news_id=c.news_id and n.news_id=$news_id limit 0,1" ;
		$info=$this->erp_model->query($sql);
		if(empty($info)){return null;}
		return $info[0];
	}
	public function changetop($news_id,$flag){
		if($flag==1){
			return $this->erp_model->table(T_NEWS)->data("top_time='".time()."'")->where("news_id=$news_id")->update();
		}else{
			return $this->erp_model->table(T_NEWS)->data("top_time=null")->where("news_id=$news_id")->update();
		}
	}
	
	/**
	 * @author	lidc
	 * @desc	获取导航新闻栏目中的百米动态id
	 * @return	int 
	 */
	public function getArticleTypesById(){
		$result = $this->erp_model->table(T_ARTICLE_TYPES)->field("at_id")->where("atCode='100msh'")->find();
		return $result['at_id'];
	} 
	
	/**
	 * @author	lidc
	 * @desc	如果导航新闻数据中没有同步官网数据，首先需将官网新闻同步至导航新闻中，需手动调用
	 */
	public function newsSynchronousArticles(){
		$sql_n = "SELECT t1.news_id,t1.news_title,t1.digest,t1.news_tag,t1.news_date,t1.clicks,t1.news_date,t1.show_img,t1.is_display,t2.news_content FROM `t_news` as t1,`t_news_content` as t2  WHERE t1.news_id=t2.news_id";
		$n_list = $this->erp_model->query($sql_n);
		$n_arr = array();
		$at_id = $this->getArticleTypesById();
		$sc_id = 0;
		foreach ($n_list as $key=>$rows){
			$data = array(
				'at_id'=>$at_id,
				'sc_id'=>$sc_id,
				'aTitle'=>addslashes($rows['news_title']),
				'aSubTitle'=>addslashes($rows['news_title']),
				'aDigest'=>addslashes($rows['digest']),
				'aTags'=>$rows['news_tag'],
				'aID'=>'',
				'aType'=>'',
				'aSort'=>0,
				'aPublishTime'=>$rows['news_date'],
				'aReplyCount'=>intval($rows['clicks']),
				'aImgs'=>addslashes(serialize($rows['show_img'])),
				'news_id'=>$rows['news_id'],
				'aStatus'=>$rows['is_display']
			);
			$result = $this->erp_model->table(T_ARTICLES)->data($data)->insert();
			if($result){
				$a_id = $result;
				unset($data);
				$data2 = array(
					'a_id'=>$a_id,
					'aContent'=>addslashes($rows['news_content'])
				);
				$result = $this->erp_model->table(T_ARTICLE_CONTENT)->data($data2)->insert();
			}		
		}
		return $result;
	}
	
	//更新官网新闻同步更新导航新闻数据
	public function editSynchronousArticles($data,$news_id){
		$aContent=$data['aContent'];
		unset($data['aContent']);
		$articles_list = $this->erp_model->table(T_ARTICLES)->where("news_id=".$news_id."")->find();
		$a_id = $articles_list['a_id'];
		$result = $this->erp_model->table(T_ARTICLES)->data($data)->where("news_id=".$news_id."")->update();
		if($result && $a_id){
			$result = $this->erp_model->table(T_ARTICLE_CONTENT)->data(array('aContent'=>$aContent))->where("a_id=".$a_id."")->update();
		}
		return $result;
	}
	//添加官网新闻同时添加至导航新闻中。
	public function addSynchronousArticles($data){
		$aContent=$data['aContent'];
		unset($data['aContent']);		
		$a_id = $this->erp_model->table(T_ARTICLES)->data($data)->insert();
		if($a_id){
			$result = $this->erp_model->table(T_ARTICLE_CONTENT)->data(array('a_id'=>$a_id,'aContent'=>$aContent))->insert();
		}
		return $result;
	}
	
	public function delSynchronousArticles($news_id){
		$articles_list = $this->erp_model->table(T_ARTICLES)->where("news_id=".$news_id."")->find();		
		if($articles_list){
			$a_id = $articles_list['a_id'];
			$result = $this->erp_model->table(T_ARTICLES)->where("a_id=".$a_id."")->delete();
			if($result){	
				$result = $this->erp_model->table(T_ARTICLE_CONTENT)->where("a_id=".$a_id."")->delete();
			}
		}else{
			$result = 0;
		}
		return $result;			
	}
	
}