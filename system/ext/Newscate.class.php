<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		sucd
 * @date		2016-05-31
 * @desc		文章分类类
 */
class Newscate{
	private $erp_model;
	private $prefix;
	public function __construct(  ){
		$this->erp_model = App::db()->ERP;
        $this->prefix = $this->erp_model->pre;
	}
	public function getList($where,$start,$numPerPage){
		return $this->erp_model->table(T_NEWS_CATE)->where($where)->limit("$start,$numPerPage")->order("news_cate_order desc")->select();
	}
	public function getInfo($news_cate_id){
		return $this->erp_model->table(T_NEWS_CATE)->where("news_cate_id=$news_cate_id")->find();
	}
	public function getCount($where){
		return $this->erp_model->table(T_NEWS_CATE)->where($where)->count();
	}
	public function add($data){
		return $this->erp_model->table(T_NEWS_CATE)->data($data)->insert();
	}
	public function edit($news_cate_id,$data){
		return $this->erp_model->table(T_NEWS_CATE)->data($data)->where("news_cate_id=$news_cate_id")->update();
	}

	public function del($ids){
		$cate_ids=$ids;
		$check_ids=$this->erp_model->table(T_NEWS)->field("news_cate_id")->where("news_cate_id in($cate_ids)")->select();
		if(!empty($check_ids)){
			$alldel_ids=explode(',', $ids);
			foreach ($check_ids as $value) {
				$cantdel_ids[]=$value['news_cate_id'];
			}
			$del_ids=array_diff($alldel_ids, $cantdel_ids);
			if(!empty($del_ids)){
				$del_ids_str=implode(',', $del_ids);
				$result=$this->erp_model->table(T_NEWS_CATE)->where("news_cate_id in($del_ids_str)")->delete();
				if(!empty($result)){return 2;}else{return -1;}
			}else{
				return -2;//所选的id都已经关联新闻;
			}
		}else{
			$result=$this->erp_model->table(T_NEWS_CATE)->where("news_cate_id in($cate_ids)")->delete();
			if(!empty($result)){return 1;}else{return -1;};
		}
	}
	public function checkCateTitle($news_cate_title,$news_cate_id=null){
		if($news_cate_title==null){
			$result=$this->erp_model->table(T_NEWS_CATE)->where("news_cate_title='$news_cate_title'")->find();
		}else{
			$result=$this->erp_model->table(T_NEWS_CATE)->where("news_cate_title='$news_cate_title' and news_cate_id<>'$news_cate_id'")->find();
		}
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}

}