<?php
/**
 * @desc: 日志管理
**/

class operationLogDAO{
	private static $_instance = null;
	private $prefix = '';

	private $log_cate_arr=array ('LC001' => 1,'LC002' => 2,'LC003' => 3 ,'LC004' => 4 ,'LC005' => 5 ,'LC006' => 6 ,'LC007' => 7,'LC008' => 8,'LC009' => 9);
	private $oper_type_arr=array('LOT001' => 1, 'LOT002' => 2 ,'LOT003' => 3 ,'LOT004' => 4 ,'LOT005' => 5);

	static function getInstance() {
		if(self::$_instance == NULL) self::$_instance = new operationLogDAO();
		return self::$_instance;
	}
	function __construct(){
         $this->prefix = App::db()->LOG->pre;
	}
	function __destruct(){
		//do nothing
	}

	/**
	* @desc 创建日志
	* @param $log_title   日志标题
	* @param $log_content 日志内容
	* @param $log_cate    日志分类(LC001登录日志,LC002订单日志,LC003商品日志,LC004库存日志,LC005新闻日志...)
	* @param $log_oper_type   日志操作类型(LOT001添加，LOT002修改，LOT003删除，LOT004查询，LOT005其他)
	* @return array 成功或失败信息
	**/
	public function create_log($log_title,$log_content,$log_cate,$log_oper_type='LOT005',$userid=""){
		global $userdata,$user_type;
		$user_type=1;
		if($log_cate!='LC001'){
			$user_id   = U::getUserId();
            $user_name = U::getUserName();
            $real_name = U::getUserRealName();
			if(empty($user_id)){
				return array(
				    "is_success" => false,
				    "info" => "用户未登录!",
				);
			}//用户未登录
		}else{
			/*移到Boss系统后，米控将不会保存登录日志*/
			$adm_user = new Admusers();
			$user_id=intval($userid);
			$user_name=$adm_user->getUserNameById($user_id);
			$real_name=$adm_user->getUserRealnameById($user_id);
		}

		if(!array_key_exists($log_cate,$this->log_cate_arr)){
			$log_cate_arr_A=$this->get_log_cate_arr();
			if(!array_key_exists($log_cate,$log_cate_arr_A)){
				return array(
				"is_success" => false,
				"info" => "日志分类不正确!",
				);
			}
			$this->log_cate_arr = $log_cate_arr_A;
		}//日志分类不存在
		if(!array_key_exists($log_oper_type,$this->oper_type_arr)){
			$oper_type_arr_A=$this->get_log_oper_arr();
			if(!array_key_exists($log_oper_type,$oper_type_arr_A)){
				return array(
				"is_success" => false,
				"info" => "日志操作类型不正确!",
				);
			}
			$this->oper_type_arr = $oper_type_arr_A;
		}//日志操作类型不存在
		if(empty($log_title) || empty($log_cate) || empty($log_oper_type) || empty($user_type)|| empty($log_content)){
			return array(
			"is_success" => false,
			"info" => "日志写入失败，参数为空!",
			);
		}

		$log_data= array(
			"oper_user_id"          => $user_id,
			"oper_user_name"        => $user_name,
			"oper_user_realname"    => $real_name,
			"oper_user_type"        => $user_type,
			"log_cate_id"           => $this->log_cate_arr[$log_cate],
			"log_oper_type_id"      => $this->oper_type_arr[$log_oper_type],
			"log_title"             => in($log_title),
			"log_content"           => in($log_content),
			"log_ip"                => get_client_ip(),
			"log_time"              => time(),
		);
		//日志表以月份进行分表插入
		$nowm=date("Ym",time());
		$lastday=Date('t',time());
		//查找库里是否存在当前月的日志表，如：anl_log_201302
		$nowm_tab="log_".$nowm;
		$findresult='';

		if(mysql_fetch_array(App::db()->LOG->query("SHOW TABLES LIKE '".$this->prefix.$nowm_tab."'"))) {
			$findresult=App::db()->LOG->table($nowm_tab)->data($log_data)->insert();
			//App::db()->LOG->table(T_LOG_CATE)->where("used=0 AND cate_no='". $log_cate."'")->data(array('used'=>1))->update();

			return  array(
				"is_success" => true,
				"info" => "操作成功!",
			);
		} else {
			//创建该表
			$carete_tal="CREATE TABLE IF NOT EXISTS `".$this->prefix."${nowm_tab}` (
                     `log_id` int(11) NOT NULL AUTO_INCREMENT,
                     `oper_user_id` int(11) NOT NULL,
                     `oper_user_name` varchar(20) NOT NULL,
                     `oper_user_realname` varchar(20) NOT NULL,
                     `oper_user_type` tinyint(1) NOT NULL default '1',
                     #`partner_id` int(11) NOT NULL,
                     `log_cate_id` int(11) NOT NULL,
                     `log_oper_type_id` int(11) NOT NULL,
                     `log_title` varchar(100) NOT NULL,
                     `log_content` text,
                     `log_time` int(11) NOT NULL,
                     `log_ip` varchar(30) NOT NULL,
                      PRIMARY KEY (`log_id`)
                   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


			$result = App::db()->LOG->query($carete_tal);
			if($result){
				//创建一张年月表
				$ym_date=array(
				"partition_title"=>date("Y-m",time()),
				"partition_start"=>strtotime($nowm.'01 00:00:00'),
				"partition_end"=>strtotime($nowm.$lastday.' 23:59:59'),
				"partition_table"=>$this->prefix . 'log_'.$nowm,
				"partition_desc"=>'按月分表',
				);
				$res_ym=App::db()->LOG->table(T_LOG_TIME_PARTITION)->data($ym_date)->insert();
				if($res_ym){
					$findresult=App::db()->LOG->table($nowm_tab)->data($log_data)->insert();
					//App::db()->LOG->table(T_LOG_CATE)->where("used=0 AND cate_no='". $log_cate."'")->data(array('used'=>1))->update();
					
					return  array(
						"is_success" => true,
						"info" => "操作成功!",
					);
				}else{
					return  array(
					"is_success" => false,
					"info" => "日志分表年月表信息插入不成功!",
					);
				}

			}else{
				return  array(
				"is_success" => false,
				"info" => "日志表创建不成功!",
				);
			}
		}
	}
	/**
     * @desc 获得日志分类表的cate_name与log_cate_id的健值对
     * @return array
     */
	function get_log_cate_arr(){
		$query = "SELECT log_cate_id,cate_no FROM ". $this->prefix . T_LOG_CATE." ORDER BY log_cate_id ASC";
		$result = App::db()->LOG->query($query);

		$list = array();
		if(!empty($result)) {
			foreach($result as $row){
				$list[$row['cate_no']] = $row['log_cate_id'];
			}
		}
		
		return $list;
	}

	/**
     * @desc 获得日志操作类型表type_name与log_oper_type_id的健值对
     * @return array
     */
	function get_log_oper_arr(){
		$query = "SELECT log_oper_type_id,type_no FROM ". $this->prefix . T_LOG_OPER_TYPE." ORDER BY log_oper_type_id ASC";
		$result =App::db()->LOG->query($query);
		$list = array();
		if(!empty($result)) {
			foreach($result as $row){
				$list[$row['type_no']] = $row['log_oper_type_id'];
			}
		}
		return $list;
	}
}
?>