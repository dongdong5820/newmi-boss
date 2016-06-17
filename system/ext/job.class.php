<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		jiang <jiang@100msh.com>
 * @date		2014-10-11
 * @desc		文章管理类
 */
class job{
	private $erp_model;
	private $prefix;
	public function __construct(  ){
		$this->erp_model = App::db()->ERP;
        		$this->prefix = $this->erp_model->pre;
	}
	public function getList($where,$start,$numPerPage){
		$sql="SELECT j.*,jt.job_type_name FROM ".$this->prefix.T_JOB." as j left join ".$this->prefix.T_JOB_TYPE." as jt on j.job_type_id=jt.job_type_id where $where order by j.job_uptime desc limit $start,$numPerPage";
		$list=$this->erp_model->query($sql);
		return $list;
	}
	public function getCount($where){
		$sql="SELECT count(*) as count FROM ".$this->prefix.T_JOB." as j  where $where";
		$count=$this->erp_model->query($sql);
		return $count[0]['count'];
	}
	public function add($data){
		return $this->erp_model->table(T_JOB)->data($data)->insert();
		
	}
	public function edit($data,$job_id){
		return $this->erp_model->table(T_JOB)->data($data)->where("job_id=".$job_id)->update();
	}
	public function del($id){
        return $this->erp_model->table(T_JOB)->where("job_id =$id")->delete();
	}
	public function checkjobName($job_name,$job_type_id,$job_id=null){
		$where=sprintf("job_name='%s' and job_type_id=%d %s",$job_name,$job_type_id,$job_id===null?'':sprintf("and job_id<>%d",$job_id));
		$result=$this->erp_model->table(T_JOB)->where($where)->find();
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}
	public function checkjobOrder($job_order,$job_type_id,$job_id=null){
		$where=sprintf("job_order='%s' and job_type_id=%d %s",$job_order,$job_type_id,$job_id===null?'':sprintf("and job_id<>%d",$job_id));
		$result=$this->erp_model->table(T_JOB)->where($where)->find();
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}
	public function getJobTypeList(){
		$list=$this->erp_model->table(T_JOB_TYPE)->field("job_type_id,job_type_name")->order("job_type_order ASC")->select();
		return $list;
	}
	public function getJobInfo($job_id){
		$sql="SELECT j.*,jt.job_type_name FROM ".$this->prefix.T_JOB." as j left join ".$this->prefix.T_JOB_TYPE." as jt on j.job_type_id=jt.job_type_id where j.job_id=$job_id limit 0,1";
		$info=$this->erp_model->query($sql);
		if(empty($info)){return null;}
		return $info[0];
	}
	public function getUserName($user_id){
		$name=$this->erp_model->table( T_ADM_USERS )->field("user_name")->where( "user_id='$user_id'" )->find();
		if(empty($name)){return null;}
		return $name['user_name'];
	}
	
}