<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Lidc <lidaichen@100msh.com>
 * @date		2015-3-8
 * @desc		申请WiFi
 */
class ApplyWifiCity {
    private $model;
    private $prefix;
    public function __construct(){
        $this->model = App::db()->ERP;
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
	public function getApplyWifiCityList($where=1,$start,$lenght){
	    return $this->model->table(T_APPLYWIFI_CITY)->where($where)->limit($start.",".$lenght)->select();	    
	}
	
	/**
	 * @author lidc
	 * @desc   获取申请WiFi商户总算
	 * @param string $where    条件查询
	 * @return int
	 */
	public function getApplyWifiCityCount($where=1){
	    return $this->model->table(T_APPLYWIFI_CITY)->where($where)->count();
	}
	
	public function getApplyWifiCityP($where){
	    return $this->model->table(T_APPLYWIFI_CITY)->where($where)->select();	
	}
	
	public function getApplyWifiCityById($id){
	    return $this->model->table(T_APPLYWIFI_CITY)->where('id='.$id)->find();
	}
	
    public function add($data){
        return $this->model->table(T_APPLYWIFI_CITY)->data($data)->insert();    
    }
    
    public function edit($where,$data){
        return $this->model->table(T_APPLYWIFI_CITY)->where($where)->data($data)->update();
    }
    
    public function del($id){        
        return $this->model->table(T_APPLYWIFI_CITY)->where(" id in($id)")->delete();
    }
}
