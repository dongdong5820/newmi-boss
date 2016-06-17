<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Skylon <skylon@100msh.com>
 * @date		2014-7-7
 * @desc		城市地区管理
 */
class StateCity
{
	private $adm_model;
	private $prefix;
	public function __construct(  )
	{
		$this->adm_model = App::db()->ADM;
		$this->prefix = $this->adm_model->pre;
	}
	/**
	 * @author	skylon
	 * @desc	根据地区父ID 获取子地区列表
	 */
	public function getScListByScpid($sc_pid){
		return	$this->adm_model->table(TBL_STATECITY,true)->field("sc_id,sc_name")->where("sc_pid='$sc_pid'")->select();
		
	}
	
	
	
	/**
	 * @author lidc
	 * @param unknown $sc_id
	 * @desc 通过省份获取城市
	 */	
	public function getScnameByScid($sc_id){
		return	$this->adm_model->table(TBL_STATECITY,true)->field("sc_name")->where("sc_id='$sc_id'")->find();
	}
	/**
	 * @author	skylon
	 * @desc	根据地区ID 获取商圈列表
	 */
	public function getSListByScid($sc_id){
		return	$this->adm_model->table(TBL_AREA,true)->field("area_id,area_name")->where("statecity_id='$sc_id'")->select();
	}

	/**
	 * @author	skylon
	 * @desc	根据父地区ID 获取全部的最小级地区ID
	 */
	function getScIds($t_sc_id){
		if(is_array($t_sc_id)){
			$list=$this->getCityList();//$this->getCityList();
			if($list){
				foreach ($list as $val){
					$sc_pid=$val['sc_pid'];
					$sc_list[$sc_pid][]=$val['sc_id'];
				}
			}
			foreach ($t_sc_id as $v){
				if(!empty($sc_list[$v])){
					foreach ($sc_list[$v] as $v2){
						if(!empty($sc_list[$v2])){
							foreach ($sc_list[$v2] as $v3){
								if(!empty($sc_list[$v3])){
									foreach ($sc_list[$v3] as $v4){
										$sc_ids[]=$v4;
									}
								}else{
									$sc_ids[]=$v3;
								}
							}
						}else{
							$sc_ids[]=$v2;
						}
					}
				}else{
					$sc_ids[]=$v;
				}
			}
		}
		return $sc_ids;
	}
	/**
	 * @author	skylon
	 * @desc	根据子地区ID 获取全部的父地区名称信息
	 */
	public function getCityNameById($sc_id){
		$sc_info=$this->adm_model->table( TBL_STATECITY,true )->field("sc_id,sc_name,sc_pid")->where("sc_id='$sc_id'")->find(  );
		if($sc_info['sc_pid']!='0'){
			$sc_name_string = $sc_info['sc_name'];
			$sc_pid=$sc_info['sc_pid'];
			$sc_info_one=$this->adm_model->table( TBL_STATECITY,true )->field("sc_id,sc_name,sc_pid")->where("sc_id='$sc_pid'")->find(  );
			if($sc_info_one['sc_pid']!='0'){
				$sc_name_string = $sc_info_one['sc_name'].$sc_name_string;
				$sc_pid=$sc_info_one['sc_pid'];
				$sc_info_two=$this->adm_model->table( TBL_STATECITY,true )->field("sc_id,sc_name,sc_pid")->where("sc_id='$sc_pid'")->find(  );
				return $sc_info_two['sc_name'].$sc_name_string;
			}else{
				return $sc_name_string = $sc_info_one['sc_name'].$sc_name_string;
			}
		}
		return $sc_info['sc_name'];
	}
	/**
	 * @author	skylon
	 * @desc	根据商圈ID 获取全部的父地区名称信息
	 */
	function getCityNameByAreaId($area_id,$sc_id=""){
		$area_info=$this->adm_model->table(TBL_AREA,true)->field("statecity_id,area_name")->where("area_id='$area_id'")->find();
		if(!empty($sc_id)){
			$sc_name_arr=!empty($area_info)?$this->getCityNameById($sc_id)."[".$area_info['area_name']."]":$this->getCityNameById($sc_id);
		}else{
			$sc_name_arr=!empty($area_info)?$this->getCityNameById($area_info['statecity_id'])."[".$area_info['area_name']."]":"";
		}
		return $sc_name_arr;
	}
	/**
	 * @author	skylon
	 * @desc	全部地区信息
	 */
	private function getCityList(){
		if(!$sc_list=DataCache::get("SC_LIST")){
			$sc_list= $this->adm_model->table(TBL_STATECITY,true)->field("sc_id,sc_pid,sc_name")->where(1)->select();
			DataCache::set("SC_LIST",$sc_list,3600);
		}
		return $sc_list;
	}
	/**
	 * @author	skylon
	 * @desc	根据地区ID获取相关地区信息
	 */
	public function getCityListByScIds($sc_ids){
		return $sc_list= $this->adm_model->table(TBL_STATECITY,true)->field("sc_id,sc_name")->where("sc_id in($sc_ids)")->select();
	}
	/**
	 * @author	skylon
	 * @desc	根据最小级地区ID 获取全部的父地区ID
	 */
	function getScIdArr(&$muster,$sc_id){
		$sc_pid=$this->adm_model->table(TBL_STATECITY,true)->field("sc_pid")->where("sc_id=$sc_id")->find();
		if($sc_pid['sc_pid']!='0'){
			$muster = $sc_pid['sc_pid'].",".$muster;
			$this->getScIdArr($muster, $sc_pid['sc_pid']);
		}
		return $muster;
	}
	/**
	 *  @desc 根据市id得到省，市，区 id 数组  
	 *  @param int $sc_id 市id
	 *  @return array $result 省市区数组
	 */
	function getScArrByCity($sc_id){
		if(!empty($sc_id)){
			$sc_city_info=$this->adm_model->table(TBL_STATECITY,true)->field("sc_pid")->where("sc_id='$sc_id'")->find(); //查到市的pid
			if(empty($sc_city_info)){return "市级信息获取失败！";}
			$sc_pro_info=$this->adm_model->table(TBL_STATECITY,true)->field("sc_id")->where("sc_id=".$sc_city_info['sc_pid'])->find(); //市的pid得到省的id
			$sc_dis_info=$this->adm_model->table(TBL_STATECITY,true)->field("sc_id")->where("sc_pid in('$sc_id')")->select(); //市的得到区id
			if($sc_dis_info){
				foreach ($sc_dis_info as  $value) {
					$sc_dis_arr[]=$value['sc_id'];
				}
			}else{
				$sc_dis_arr=array();
			}
			$result=array(
				"city"=>$sc_id,
				"province"=>$sc_pro_info['sc_id'],
				"district"=>$sc_dis_arr,
			);
			return $result;
		}else{
			return "空参数";
		}
	}
}
?>