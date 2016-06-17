<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Lidc <lidaichen@100msh.com>
 * @date		2015-8-21
 * @desc		首页轮播图
 */
class HomeImg {
    private $model;
    private $prefix;
   
    public function __construct(){
        $this->model = App::db()->ERP;
        $this->prefix = $this->model->pre;
    }
    /**
     * @author lidc
     * @desc    获取首页图片列表数据
     * @param number $where
     * @param unknown $start
     * @param unknown $lenght
     */
    public function getHomeImgList($where=1, $start, $lenght){
        $result = $this->model->table(T_HOME_IMG)->field("id,web_type,img_desc,background,outlook_img,background_color,url1,status,img_order,bg_type")->where($where)->order("web_type,img_order,edit_time desc")->limit($start.",".$lenght)->select();
        return $result;
    }
    /**
     * @author  lidc
     * @desc    查询首页图片数量
     * @param unknown $where
     */
    public function getHomeImgCount($where){
        return $this->model->table(T_HOME_IMG)->where($where)->count();
    }
    /**
     * @author  lidc
     * @desc    根据条件查询一条首页图片信息
     * @param unknown $where
     */
    public function getHomeImgById($where){
        return $this->model->table(T_HOME_IMG)->where($where)->find();
    }
    /**
     * @author lidc
     * @desc    根据条件查询状态
     * @param unknown $where
     */
    public function getHomeImgByIdList($where){
        return $this->model->table(T_HOME_IMG)->field('status')->where($where)->select();
    }
    /**
     * @author  lidc
     * @desc    添加首页图片
     * @param unknown $data
     */
    public function add($data){
        return $this->model->table(T_HOME_IMG)->data($data)->insert();
    }
    /**
     * @author  lidc
     * @desc    删除图片列信息
     * @param unknown $kid
     */
    public function delImg($kid){
        $result = $this->model->table(T_HOME_IMG)->where(" id in($kid)")->delete();
        return $result;
    }
    /**
     * @author  lidc
     * @desc    修改首页图片
     * @param unknown $where
     * @param unknown $data
     */
    public function edit($where, $data){
        return $this->model->table(T_HOME_IMG)->where($where)->data($data)->update();
    }  
}