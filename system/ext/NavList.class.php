<?php
/**
 * @copyright	©2013-2015 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Lidc <lidaichen@100msh.com>
 * @date		2015-5-28
 * @desc		导航跳转列表
 */
class NavList {
    private $model;
    private $prefix;
    public function __construct(){
        $this->model = App::db()->ERP;
        $this->prefix = $this->model->pre;
    }

    /**
     * @author [lidc] 
     * @desc   获取导航跳转列表数据
     * @param  string  $where  [搜索条件组合]
     * @param  [int]  $start  [限制查询开始位]
     * @param  [int]  $lenght [限制查询长度]
     * @return [array]          [返回所有导航跳转数据]
     */
    public function getNavList($where=1,$start,$lenght){
        return $this->model->table(T_NAV)->where($where)->order('nav_date desc,nav_id desc')->limit($start.",".$lenght)->select();
    }

    /**
     * @author [lidc] <[email]>
     * @desc   [获取导航跳转条数]
     * @param  string  $where [搜索条件]
     * @return [int]        [返回条数值]
     */
    public function getNavListCount($where=1){
        return $this->model->table(T_NAV)->where($where)->count();
    }

    /**
     * @author [lidc] <[email]>
     * @desc   [通过导航跳转nav_id获取对应的数据信息]
     * @param  [int] $nav_id [导航id]
     * @return [array]         [description]
     */
    public function getNavListById($nav_id){
        return $this->model->table(T_NAV)->where("nav_id=".$nav_id."")->find();
    }

    public function getCheckNavList($where){
        return $this->model->table(T_NAV)->where($where)->count();
    }

    public function getCheckCityNavRelCount($where){
        return $this->model->table(T_NAV_CITY_REL)->where($where)->count();
    }
    
    public function editTNavCityRel($where,$data){
        return $this->model->table(T_NAV_CITY_REL)->where($where)->data($data)->update();
    }

    public function getNavListDefaultById($nav_id){
        if(!empty($nav_id)){
            return $this->model->table(T_NAV)->where("nav_id=".$nav_id." or nav_default=1")->find();
        }else{
            return $this->model->table(T_NAV)->where("nav_default=1")->find();
        }        
    }
    
    public function getNavListDefault(){
        return $this->model->table(T_NAV)->field('nav_id')->where("nav_default=1")->find();
    }

    /**
     * @author [lidc] 
     * @desc   [新增导航跳转数据] 
     * @param [array] $data [通过数值组合的数据]
     * @return [int] [返回保存结果,大于1成功,否则失败]
     */
    public function add($data){
        return $this->model->table(T_NAV)->data($data)->insert();
    }

    /**
     * @author [lidc] <[email]>
     * @desc   [根据条件修改导航跳转信息数据]
     * @param  [type] $where [条件]
     * @param  [array] $data  [需要保存的导航跳转数据组合的数组]
     * @return [int]        [返回保存结果,大于1成功,否则失败]
     */
    public function edit($where, $data){
        return $this->model->table(T_NAV)->where($where)->data($data)->update();
    }
    
    /**
     * @author [lidc] <[email]>
     * @desc   [根据条件删除单条/多条导航跳转数据]
     * @param  [type] $kid [需要删除的nav_id]
     * @return [int]      [返回删除结果,>=1表示删除成功,否则失败]
     */
    public function del($kid){
        return $this->model->table(T_NAV)->where(" nav_id in ($kid) ")->delete();
    }

    public function getNavListBiId($kid){
        return $this->model->table(T_NAV)->where("nav_id in ($kid)")->select();
    }
}
