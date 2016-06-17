<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Bruce <xuehaitao@100msh.com>
 * @date		2014-7-2
 * @desc		日志分类管理
 */
class LogCate
{
	private $log_model;
    public function __construct(  )
    {
        $this->log_model = App::db()->LOG;
        $this->prefix = $this->log_model->pre;
    }

    /**
     * 获取日志分类列表
     * @param $filter array 过滤的条件
     * @param $start int 数据开始的位置
     * @param $numPerPage int 偏移量
     * @return array
     */
    public function getList($filter,$start,$numPerPage) {
    	$where = '1=1';
    	if($filter['s_search_key']) {
            $where .= " AND cate_name LIKE '%" . $filter['s_search_key'] . "%' OR cate_no LIKE '%" . $filter['s_search_key'] . "%'";
        }
    	if($filter['cate_name']) {
    		$where .= " AND cate_name LIKE '%" . $filter['cate_name'] . "%'";
    	}
    	if($filter['cate_no']) {
    		$where .= " AND cate_no LIKE '%" . $filter['cate_no'] . "%'";
    	}

    	return $this->log_model->table(T_LOG_CATE)->where($where)->limit("$start,$numPerPage")->select();
    }

    /**
     * 获取日志分类总数
     * @param $filter array 过滤的条件
     * @return int
     */
    public function getCount($filter) {
    	$where = '1=1';
    	if($filter['s_search_key']) {
            $where .= " AND cate_name LIKE '%" . $filter['s_search_key'] . "%' OR cate_no LIKE '%" . $filter['s_search_key'] . "%'";
        }
    	if($filter['cate_name']) {
    		$where .= " AND cate_name LIKE '%" . $filter['cate_name'] . "%'";
    	}
    	if($filter['cate_no']) {
    		$where .= " AND cate_no LIKE '%" . $filter['cate_no'] . "%'";
    	}
    	return $this->log_model->table(T_LOG_CATE)->where($where)->count();
    }

    /**
     * 获取表所有数据
     * @return array
     */
    public function getAll() {
    	return $this->log_model->table(T_LOG_CATE)->select();
    }

    /**
     * 检查日志分类是否存在
     * @param  string $cate_name 分类名称
     * @return boolean
     */
    public function checkCateNameExist($cate_name) {
    	$exist = false;

    	$condition = array('cate_name' => $cate_name);
    	if($this->log_model->table(T_LOG_CATE)->field('cate_name')->where($condition)->find()) {
    		$exist = true;
    	}

    	return $exist;
    }

    /**
     * 检查分类编码
     * @param  string $cate_no 分类编号
     * @return boolean
     */
    public function checkCateNoExist($cate_no) {
    	$exist = false;
    	
    	$condition = array('cate_no' => $cate_no);
    	if($this->log_model->table(T_LOG_CATE)->field('cate_no')->where($condition)->find()) {
    		$exist = true;
    	}

    	return $exist;
    }

    /**
     * 获取一行分类
     * @param  int $log_cate_id
     * @return array
     */
    public function getRow($log_cate_id) {
    	$condition = array('log_cate_id' => $log_cate_id);
    	return $this->log_model->table(T_LOG_CATE)->where($condition)->find();
    }

    /**
     * 插入数据
     * @param  array $data 需要插入的数据
     * @return boolean
     */
    public function insert($data) {
    	return $this->log_model->table(T_LOG_CATE)->data($data)->insert();
    }

    /**
     * 更新数据
     * @param  int $log_cate_id 分类ID
     * @param  array $data 需要更新的数据
     * @return boolean
     */
    public function update($log_cate_id, $data) {
    	$condition = array('log_cate_id' => $log_cate_id);
    	return $this->log_model->table(T_LOG_CATE)->where($condition)->data($data)->update();
    }

    /**
     * 删除分类
     * @param  string $kid 分类，多个分类用逗号隔开
     * @return boolean
     */
    public function delete($kid) {
    	$condition = " log_cate_id in ($kid)";
    	return $this->log_model->table(T_LOG_CATE)->where($condition)->delete();
    }

}