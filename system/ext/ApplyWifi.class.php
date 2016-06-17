<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Lidc <lidaichen@100msh.com>
 * @date		2015-1-22
 * @desc		申请WiFi
 */
class ApplyWifi {
    private $model;
    private $adm_model;
    private $partner_model;
    private $prefix;
    public function __construct(){
        $this->model = App::db()->ERP;
        $this->partner_model = App::db()->PARTNER;
        $this->adm_model = App::db()->ADM;
        $this->prefix = $this->model->pre;
    }
	
	/**
	 * @author lidc
	 * @desc   获取申请WiFi商户资料列表
	 * @param  string  $where  查询条件
	 * @param  int $start  限制查询开始位
	 * @param  int $lenght 限制查询长度
	 * @return array
	 */
	public function getApplyWifiList($where=1,$start,$lenght){
	    return $this->model->table(T_APPLY_WIFI)->where($where)->order('apply_time desc')->limit($start.",".$lenght)->select();	    
	}
	
	public function addList(){
	    for ($i=0; $i<=1000; $i++){
	       $data = array(
	           'apply_username' =>'黄金档',
	           'sc_pid'    =>43,
	           'sc_id' =>4301,
	           'apply_address' => '芙蓉区KKK',
	           'apply_industry' =>0,
	           'apply_operators' =>3,
	           'apply_broadband'   => '3M',
	           'apply_phone'   => '18080808080',
	           'apply_store_name'  => '青青美容会所',
	           'apply_source' =>  '微信wifi链接',
	           'apply_time'    => 1422340979
	       );     
	       
	       $this->model->table(T_APPLY_WIFI)->data($data)->insert();
	    }
	}
	
	/**
	 * @author lidc
	 * @desc   获取申请WiFi商户总算
	 * @param string $where    条件查询
	 * @return int
	 */
	public function getApplyWifiCount($where=1){
	    return $this->model->table(T_APPLY_WIFI)->where($where)->count();
	}
	
	/**
	 * @author lidc
	 * @desc   获取地区
	 */
	public function getStatecityList($sc_id,$tem_arr=''){
	    $cl = $this->adm_model->table(TBL_STATECITY,true)->field('sc_id,sc_pid,sc_name')->where('sc_id='.$sc_id)->find();
	    if($cl){
	        $city_arr = array('sc_id'=>$cl['sc_id'],'sc_name'=>$cl['sc_name']);
	    }else{
	        $city_arr = 0;
	    }
	    
	    $sc_pid = $cl['sc_pid'];
	    if($sc_pid){
	         $csl = $this->getStatecityList($sc_pid, $city_arr);
	         $cs_arr = array('sc_pid'=>$csl['sc_id'],'sc_pname'=>$csl['sc_name']);
	         return array_merge($city_arr,$cs_arr);
	    }
	    return $city_arr;
	}
	
	public function getStatecityById($sc_id){
	   $this->adm_model->table(TBL_STATECITY,true)->field('sc_id,sc_pid,sc_name')->where('sc_id='.$sc_id)->find();
	}
	
	/**
	 * @author lidc
	 * @param unknown $sc_id
	 * @param string $tem_arr  临时数组
	 * @return multitype:|Ambigous <number, multitype:unknown >
	 * @desc   获取覆盖所在城市，并组合后返回数组
	 */
	public function getApplyWifiCityList($sc_id,$tem_arr=''){
	    $cl = $this->model->table(T_APPLYWIFI_CITY)->field('id,sc_pid,city_name')->where('id='.$sc_id)->find();
	    if($cl){
	        $city_arr = array('sc_id'=>$cl['id'],'sc_name'=>$cl['city_name']);
	    }else{
	        $city_arr = 0;
	    }
	    
	    $sc_pid = $cl['sc_pid'];
	    if($sc_pid){
	        $csl = $this->getApplyWifiCityList($sc_pid, $city_arr);
	        $cs_arr = array('sc_pid'=>$csl['sc_id'],'sc_pname'=>$csl['sc_name']);
	        return array_merge($city_arr,$cs_arr);
	    }
	    return $city_arr;
	}
	
	/**
	 * @author lidc
	 * @param unknown $where
	 * @desc   通过条件返回城市信息
	 */
	public function getApplyWifiCityById($id){
	    return $this->model->table(T_APPLYWIFI_CITY)->field('id,sc_pid,city_name')->where("id=".$id."")->find();
	}
	
	
	public function getApplyWifiCityByList($where){
	    return $this->model->table(T_APPLYWIFI_CITY)->where($where)->select();
	}
	
	/**
	 * @author lidc
	 * @desc   获取申请wifi商户详细信息
	 * @
	 */
	public function getApplyWifiById($applyId){
	    return $this->model->table(T_APPLY_WIFI)->where('apply_id='.$applyId)->find();
	}	
	
	/**
	 * @author lidc
	 * @param unknown $cat_id
	 * @desc 获取商户行业类别
	 */
	public function getPartnerById($cat_id){
	    return $this->partner_model->table(TBL_PARTNER_CATEGORY,true)->field('cat_name')->where('cat_id='.$cat_id)->find();
	    
	}
}
