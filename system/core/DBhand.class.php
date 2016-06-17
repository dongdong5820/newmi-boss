<?php
/**
 *@desc 该类用于设定多个数据库访问的实例，便于对多个数据库进行操作
 *@example $handle=new DBhand(
 * 'API'=>array(
				'DB_HOST'=>'localhost',      	//数据库主机
				'DB_USER'=>'100msh_api',      	//数据库用户名
				'DB_PWD'=>'123456',      	    //数据库密码
				'DB_NAME'=>'100msh_api',      	//数据库名
			),
  //路由器管理数据库链接			
  'MAC'=>array(
			'DB_HOST'=>'localhost',      	//数据库主机
			'DB_USER'=>'100msh_macadmin',      	//数据库用户名
			'DB_PWD'=>'123456',      	    //数据库密码
			'DB_NAME'=>'100msh_macadmin',      	//数据库名
		),
 * );
 * $handle->API->query('SQL查询'); 或者 $handle->MAC->query('SQL查询');
 **/
class DBhand{
	private $_m;private $_cfg;
	public function __get($name){
		if(empty($this->_m[$name]))return null;
		if(!is_object($this->_m[$name])){
			$this->_m[$name]=new Model(array_merge($this->_cfg,$this->_m[$name]));
		}return $this->_m[$name];
	}
	public function __construct($array,$config){
		$this->_m=$array;$this->_cfg=$config;
	}
}