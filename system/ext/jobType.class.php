<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		jiang <jiang@100msh.com>
 * @date		2014-10-11
 * @desc		工作分类类
 */
class jobType{
	private $erp_model;
	private $prefix;
	public function __construct(  ){
		$this->erp_model = App::db()->ERP;
        $this->prefix = $this->erp_model->pre;
	}
	public function getList($where,$start,$numPerPage){
		return $this->erp_model->table(T_JOB_TYPE)->where($where)->limit("$start,$numPerPage")->order("job_type_uptime desc")->select();
	}
	public function getInfo($job_type_id){
		return $this->erp_model->table(T_JOB_TYPE)->where("job_type_id=$job_type_id")->find();
	}
	public function getCount($where){
		return $this->erp_model->table(T_JOB_TYPE)->where($where)->count();
	}
	public function add($data){
		return $this->erp_model->table(T_JOB_TYPE)->data($data)->insert();
	}
	public function edit($job_type_id,$data){
		return $this->erp_model->table(T_JOB_TYPE)->data($data)->where("job_type_id=$job_type_id")->update();
	}

	public function del($ids){
		$type_ids=$ids;
		$check_ids=$this->erp_model->table(T_JOB)->field("job_type_id")->where("job_type_id in($type_ids)")->select();
		if(!empty($check_ids)){
// 			$alldel_ids=explode(',', $ids);
// 			foreach ($check_ids as $value) {
// 				$cantdel_ids[]=$value['job_type_id'];
// 			}
// 			$del_ids=array_diff($alldel_ids, $cantdel_ids);
// 			if(!empty($del_ids)){
// 				$del_ids_str=implode(',', $del_ids);
// 				$result=$this->erp_model->table(T_JOB_TYPE)->where("job_type_id in($del_ids_str)")->delete();
// 				if(!empty($result)){return 2;}else{return -1;}
// 			}else{
// 				return -2;//所选的id都已经关联新闻;
// 			}
			return -2;//所选的id中有一个关联了职位;
		}else{
			$result=$this->erp_model->table(T_JOB_TYPE)->where("job_type_id in($type_ids)")->delete();
			if(!empty($result)){return 1;}else{return -1;};
		}
	}
	public function checkTypeName($job_type_name,$job_type_id=null){
		$where = sprintf("job_type_name = '%s' %s",$job_type_name,$job_type_id===null?'':sprintf("and job_type_id <>%s",$job_type_id));
		$result=$this->erp_model->table(T_JOB_TYPE)->where($where)->find();
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}
	public function checkTypeOrder($job_type_order,$job_type_id=null){
		$where = sprintf("job_type_order = '%s' %s",$job_type_order,$job_type_id===null?'':sprintf("and job_type_id <>%s",$job_type_id));
		$result=$this->erp_model->table(T_JOB_TYPE)->where($where)->find();
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}

}