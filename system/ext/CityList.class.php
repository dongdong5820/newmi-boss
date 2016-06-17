<?php
/**
 * @copyright	©2013-2015 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Lidc <lidaichen@100msh.com>
 * @date		2015-5-29
 * @desc		导航跳转与城市关联
 */
class CityList {
    private $model;
    private $mop_model;
    private $adm_model;
    private $prefix;
    public function __construct(){
        $this->model = App::db()->ERP;
        $this->prefix = $this->model->pre;
        $this->mop_model = App::db()->MOP;
        $this->adm_model = App::db()->ADM;
    }

    /**
     * @author [lidc] 
     * @desc   获取导航跳转列表数据
     * @param  string  $where  [搜索条件组合]
     * @param  [int]  $start  [限制查询开始位]
     * @param  [int]  $lenght [限制查询长度]
     * @return [array]          [返回所有导航跳转数据]
     */
   public function getCityList($sc_name, $where, $start, $lenght){
        //取出百万点城市
        $node_deploy_array = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1")->order('deploy_id')->limit($start.",".$lenght)->select();
        $where_sc = "";
        if(!empty($sc_name)){
            $where_sc = $sc_name;
        }
        //取出所有城市数据
        $cityArr = $this->adm_model->table(TBL_STATECITY,true)->field('sc_id,sc_name')->where($where_sc)->select(); 
        //取出所有导航跳转数据
        $nav_Arr = $this->model->table(T_NAV)->where("nav_default=1 ")->find();      
        
        //取出导航跳转与城市管理数,用来判读首次加载,如果是首次加载,需将百万点城市和默认导航跳转添加默认数据至导航城市关联表
        $rel_count = $this->model->table(T_NAV_CITY_REL)->count();
        
        $relArr = array();
        if(!empty($where_sc)){
            if(isset($cityArr) && is_array($cityArr)){
                foreach ($cityArr as $k => $v) {
                    $rel_arr = $this->model->table(T_NAV_CITY_REL)->where("city_id=".$v['sc_id']." ")->find();
                    if($rel_arr){
                        $navArr = $this->model->table(T_NAV)->where("nav_id=".$rel_arr['nav_id']."")->find();
                        if($navArr){
                            $relArr[$k]['id'] = $rel_arr['id'];
                            $relArr[$k]['nav_url'] = $navArr['nav_url'];
                            $relArr[$k]['nav_name'] = $navArr['nav_name'];
                        }else{
                            $relArr[$k]['nav_url'] = $nav_Arr['nav_url'];
                            $relArr[$k]['nav_name'] = $nav_Arr['nav_name'];
                        }
//                         $relArr[$k]['id'] = 0;
                        $relArr[$k]['city_id'] = $v['sc_id'];
                        $relArr[$k]['city_name'] = $v['sc_name'];                        
                        
                        $snd = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("sc_pid=".$v['sc_id']."")->find();
                        $relArr[$k]['area_code'] = $snd['code_no'];
                    }                    
                }
            }
        }elseif ($where){            
            $nav_list = $this->model->table(T_NAV)->where($where)->select();
            if(is_array($nav_list) && count($nav_list)>0){
                foreach ($nav_list as $kn=>$vn){                                             
                    $rel_ls = $this->model->table(T_NAV_CITY_REL)->where("nav_id=".$vn['nav_id']."")->select();
                    if($rel_ls){
                        if($vn['nav_default']==1){
                            /* 
                            //修改时间：2015年10月12日 16:08:03
                            //不知道本段代码为何这么写、所以先注释、方便还原
                            $rel_list = $this->model->table(T_NAV_CITY_REL)->field('city_id')->where("nav_id<>".$vn['nav_id']."")->select();
                            if($rel_list){
                                $no_cityId = "";
                                foreach ($rel_list as $k1 => $v1) {
                                    $no_cityId = isset($no_cityId) && !empty($no_cityId) ? $no_cityId.",".$v1['city_id'] : $v1['city_id'];
                                }    
                                $node_deploy_array = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1 and sc_pid not in(".$no_cityId.")")->order('deploy_id')->limit($start.",".$lenght)->select();                            
                                foreach ($node_deploy_array as $key => $value) {
                                    $relArr[$key]['nav_url'] = $vn['nav_url'];
                                    $relArr[$key]['nav_name'] = $vn['nav_name'];
                                    $relArr[$key]['id'] = isset($rel_ls['id']) ? $rel_ls['id'] : 0;
                                    $relArr[$key]['city_id'] = $value['sc_pid'];
                                    $relArr[$key]['area_code'] = $value['code_no'];
                                    foreach ($cityArr as $k => $v) {
                                        if($value['sc_pid'] == $v['sc_id']){
                                            $relArr[$key]['city_name'] = $v['sc_name'];
                                        }
                                    }
                                }
                            }
                             */
                            //修复城市列表无法筛选的BUG
                            //修改开始
                            $node_deploy_array = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1")->order('deploy_id')->limit($start.",".$lenght)->select();
                            foreach ($node_deploy_array as $key => $value) {
                                $relArr[$key]['nav_url'] = $vn['nav_url'];
                                $relArr[$key]['nav_name'] = $vn['nav_name'];
                                $relArr[$key]['id'] = isset($rel_ls['id']) ? $rel_ls['id'] : 0;
                                $relArr[$key]['city_id'] = $value['sc_pid'];
                                $relArr[$key]['area_code'] = $value['code_no'];
                                foreach ($cityArr as $k => $v) {
                                    if($value['sc_pid'] == $v['sc_id']){
                                        $relArr[$key]['city_name'] = $v['sc_name'];
                                    }
                                }
                            }
                            //修改结束
                        }else{
                            foreach ($rel_ls as $kr=>$vr){
                                $relArr[$kr]['nav_url'] = $vn['nav_url'];
                                $relArr[$kr]['nav_name'] = $vn['nav_name'];
                                $relArr[$kr]['id'] = $vr['id'];
                                $relArr[$kr]['city_id'] = $vr['city_id'];
                                foreach ($cityArr as $k => $v) {
                                    if($vr['city_id'] == $v['sc_id']){
                                        $relArr[$kr]['city_name'] = $v['sc_name'];
                                        foreach ($node_deploy_array as $knd=>$vnd){
                                            if($vnd['sc_pid']==$v['sc_id']){
                                                $relArr[$kr]['area_code'] = $vnd['code_no'];
                                            }
                                        }
                                    }
                                }
                            }
                        }                    
                    }else{
                        if($vn['nav_default']==1){
                            $rel_list = $this->model->table(T_NAV_CITY_REL)->field('city_id')->where("nav_id<>".$vn['nav_id']."")->select();
                            foreach ($node_deploy_array as $key=>$value){
                                foreach ($rel_list as $k1=>$v1){
                                    if($v1['city_id']!=$value['sc_pid']){
                                            $relArr[$key]['nav_url'] = $nav_Arr['nav_url'];
                                            $relArr[$key]['nav_name'] = $nav_Arr['nav_name'];
                                            $relArr[$key]['id'] = isset($rel_ls['id']) ? $rel_ls['id'] : 0;
                                            $relArr[$key]['city_id'] = $value['sc_pid'];
                                            $relArr[$key]['area_code'] = $value['code_no'];
                                            foreach ($cityArr as $k => $v) {
                                                if($value['sc_pid'] == $v['sc_id']){
                                                    $relArr[$key]['city_name'] = $v['sc_name'];
                                                }
                                            }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }else{
            foreach ($node_deploy_array as $key=>$value){
                $city_id = $value['sc_pid'];
                $rel = $this->model->table(T_NAV_CITY_REL)->where("city_id=".$city_id."")->find();
                if($rel){    
                    $relArr[$key]['id'] = $rel['id'];
                    $nav_id = $rel['nav_id'];
                    $nav = $this->model->table(T_NAV)->where("nav_id=".$nav_id."")->find();
                    $relArr[$key]['nav_url'] = $nav['nav_url'];
                    $relArr[$key]['nav_name'] = $nav['nav_name'];                
                }else{
                    $relArr[$key]['id'] = 0;
                    $relArr[$key]['nav_url'] = $nav_Arr['nav_url'];
                    $relArr[$key]['nav_name'] = $nav_Arr['nav_name'];               
                }
                if(isset($cityArr) && is_array($cityArr)){
                    foreach ($cityArr as $k=>$v){
                        if($value['sc_pid'] == $v['sc_id']){
                            $relArr[$key]['city_name'] = $v['sc_name'];
                        }               
                    }
                }            
                $relArr[$key]['city_id'] = $city_id;
                $relArr[$key]['area_code'] = $value['code_no'];
            }
        }
        
        return $relArr;
   }

   /**
    * @author lidc
    * @desc   通过条件获取城市与导航跳转数量,因城市和导航跳转为不同库,所以在城市搜索情况下需判断
    * @param  [string] $where   [导航跳转搜索条件]
    * @param  [string] $sc_name [城市搜索条件]
    * @return [int]          [数量]
    */
   public function getCityListCount($where,$sc_name){
        if(!empty($sc_name)){
            return $this->adm_model->table(TBL_STATECITY,true)->where($sc_name)->count();
        }elseif($where){
            $nav_Arr = $this->model->table(T_NAV)->where($where)->select();
            if(is_array($nav_Arr) && count($nav_Arr)>0){
                foreach ($nav_Arr as $key => $value) {
                    if($value['nav_default']==1){
                        $node_deploy_count = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1")->count();
                        $relCount = $this->model->table(T_NAV_CITY_REL)->where("nav_id<>".$value['nav_id'])->count();
                        return $node_deploy_count-$relCount;                
                    }else{
                        $sql = "SELECT count(*) as num FROM ".$this->prefix.T_NAV_CITY_REL." a LEFT JOIN ".$this->prefix.T_NAV." b ON a.nav_id=b.nav_id";
                        $sql .=" WHERE $where ";
                        $result = $this->model->query($sql);
                        return $result[0]['num'];
                    }
                }
            }else{
                return 0;
            }
        }else{
            return $this->mop_model->table(T_SITE_NODE_DEPLOY)->where("db_init_status=1")->count();
        }        
   }

   /**
    * @author lidc <[email]>
    * @return [array] [搜索框中的导航跳转列数据]
    */
   public function getNavList(){
        //取出所有导航跳转数据
        $lists=$this->model->table(T_NAV)->where('nav_status=1')->order('nav_default desc')->select(false);
        return $lists;
   }
   
   /**
    * @author lidc <[email]>
    * @return [array] [搜索框中的导航跳转列数据]
    */
   public function getNavListById($nav_id){
       //取出所有导航跳转数据
       return $this->model->table(T_NAV)->where('nav_id='.$nav_id)->find();
   }

    /**
     * @author [lidc] <[email]>
     * @desc   [通过导航跳转nav_id获取对应的数据信息]
     * @param  [int] $nav_id [导航id]
     * @return [array]         [description]
     */
    public function getcityListById($where){
        return $this->model->table(T_NAV_CITY_REL)->where($where)->find();
    }
    
    /**
     * @author [lidc] <[email]>
     * @desc   [根据条件修改导航跳转信息数据]
     * @param  [type] $where [条件]
     * @param  [array] $data  [需要保存的导航跳转数据组合的数组]
     * @return [int]        [返回保存结果,大于1成功,否则失败]
     */
    public function add($data){
        return $this->model->table(T_NAV_CITY_REL)->data($data)->insert();
    }

    /**
     * @author [lidc] <[email]>
     * @desc   [根据条件修改导航跳转信息数据]
     * @param  [type] $where [条件]
     * @param  [array] $data  [需要保存的导航跳转数据组合的数组]
     * @return [int]        [返回保存结果,大于1成功,否则失败]
     */
    public function edit($where, $data){
        return $this->model->table(T_NAV_CITY_REL)->where($where)->data($data)->update();
    }

    /**
     *@author lidc <[email]>
     * @desc  获取城市信息
     * @param  [type] $sc_id [description]
     * @return [type]        [description]
     */
    public function getCityBySc_Id($sc_id){
        return $this->adm_model->table(TBL_STATECITY,true)->field('sc_id,sc_name')->where("sc_id=".$sc_id."")->find(); 
    }
    
    public function getSiteNodeDeployById($sc_pid){
        return  $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1 and sc_pid=".$sc_pid."")->find();
    }

    public function getSiteNodeDeployCityList(){
        $node_deploy_array = $this->mop_model->table(T_SITE_NODE_DEPLOY)->field('sc_pid,code_no')->where("db_init_status=1")->order('deploy_id')->select();
        //取出所有城市数据
        $cityArr = $this->adm_model->table(TBL_STATECITY,true)->field('sc_id,sc_name,jianpin')->order('jianpin asc')->select(); 

        $city_array = array();
        foreach ($node_deploy_array as $key => $value) {
            foreach ($cityArr as $k => $v) {
                if($value['sc_pid']==$v['sc_id']){
                    $city_array[$key]['sc_id'] = $v['sc_id'];
                    $city_array[$key]['sc_name'] = $v['sc_name'];
                    $city_array[$key]['jianpin'] = substr( $v['jianpin'], 0, 1 );
                }
            }
        }
        
        $rs = $this->sysSortArray($city_array,"jianpin","SORT_ASC");
        $resutl = array();
        foreach ($rs as $k => $v) {
            $resutl[$v['jianpin']][] = $v;
        }
        return $resutl;
    }

    /** 
    * Sort an two-dimension array by some level two items use array_multisort() function. 
    * 
    * sysSortArray($Array,"Key1","SORT_ASC","SORT_RETULAR","Key2"……) 
    * @author Chunsheng Wang <> 
    * @param array $ArrayData the array to sort. 
    * @param string $KeyName1 the first item to sort by. 
    * @param string $SortOrder1 the order to sort by("SORT_ASC"|"SORT_DESC") 
    * @param string $SortType1 the sort type("SORT_REGULAR"|"SORT_NUMERIC"|"SORT_STRING") 
    * @return array sorted array. 
    */ 
    public function sysSortArray($ArrayData,$KeyName1,$SortOrder1 = "SORT_ASC",$SortType1 = "SORT_REGULAR"){
        if(!is_array($ArrayData)){
            return $ArrayData;
        } 
        $ArgCount = func_num_args(); 
        for($I=1;$I<$ArgCount;$I++){ 
            $Arg = func_get_arg($I);
            if(!preg_match("/SORT/",$Arg)){ 
                $KeyNameList[] = $Arg; 
                $SortRule[] = '$'.$Arg; 
            }else{ 
                $SortRule[] = $Arg; 
            }
        } 
        foreach($ArrayData AS $Key => $Info){
            foreach($KeyNameList AS $KeyName){ 
                ${$KeyName}[$Key] = $Info[$KeyName]; 
            }
        } 
        $EvalString = 'array_multisort('.join(",",$SortRule).',$ArrayData);'; 
        eval ($EvalString); 
        return $ArrayData; 
    }   
}
