<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Bruce <xuehaitao@100msh.com>
 * @date		2014-7-2
 * @desc		日志管理
 */
class LogOperType
{
	private $log_model;
    public function __construct(  )
    {
        $this->log_model = App::db()->LOG;
        $this->prefix = $this->log_model->pre;
    }

    public function getAll() {
        return $this->log_model->table(T_LOG_OPER_TYPE)->select();
    }
}