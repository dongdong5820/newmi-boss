<?php

class mysql {
	public $link;
	public $dbhost;
	public $dbuser;
	public $dbpw;
	public $dbcharset;
	public $pconnect;
	public $tablepre;
	public $goneaway;
	public function connect($dbhost,$dbuser,$dbpw,$dbname = '',$dbcharset = '',$pconnect =false,$tablepre='')
	{
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		$this->dbcharset = $dbcharset;
		$this->pconnect = $pconnect;
		$this->tablepre = $tablepre;
		$this->goneaway = 5;
		if($pconnect)
		{
			if(!$this->link = @mysql_pconnect($dbhost,$dbuser,$dbpw))
			{
				$this->halt('无法连接到数据库服务器');
			}
		}
		else
		{
			if(!$this->link = @mysql_connect($dbhost,$dbuser,$dbpw))
			{
				$this->halt('无法连接到数据库服务器');
			}
		}
		if($this->version() >'4.1')
		{
			if($dbcharset)
			{
				mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary",$this->link);
			}
			if($this->version() >'5.0.1')
			{
				mysql_query("SET sql_mode=''",$this->link);
			}
		}
		if($dbname)
		{
			$this->select_db($dbname);
		}
	}
	public function select_db($dbname)
	{
		return mysql_select_db($dbname,$this->link);
	}
	public function query($sql)
	{
		if(!($query = mysql_query($sql,$this->link)))
		{
			$this->halt('MySQL Query Error',$sql);
		}
		return $query;
	}
	public function fetch_array($query,$result_type = MYSQL_ASSOC)
	{
		return mysql_fetch_array($query,$result_type);
	}
	public function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ?$id : mysql_result($this->query("SELECT last_insert_id()"),0);
	}
	public function affected_rows() {
		return mysql_affected_rows($this->link);
	}
	public function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}
	public function num_fields($query) {
		return mysql_num_fields($query);
	}
	public function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
	public function free_result($query) {
		return mysql_free_result($query);
	}
	public function error()
	{
		return (($this->link) ?mysql_error($this->link) : mysql_error());
	}
	public function errno()
	{
		return intval(($this->link) ?mysql_errno($this->link) : mysql_errno());
	}
	public function version()
	{
		return mysql_get_server_info($this->link);
	}
	public function close()
	{
		if($this->link)
		@mysql_close($this->link);
	}
	public  function __destruct()
	{
		$this->close();
	}
	public function halt($message = '',$sql = '')
	{
		$error = $this->error();
		$errorno = $this->errno();
		if($errorno == 2006 &&$this->goneaway-->0)
		{
			$this->connect($this->dbhost,$this->dbuser,$this->dbpw,$this->dbname,$this->dbcharset,$this->pconnect,$this->tablepre);
			$this->query($sql);
		}
		else
		{
			if(App::$config['DEBUG'])
			{
				$str= "	<b>出错</b>: $message<br>
						<b>SQL</b>: $sql<br>
						<b>错误详情</b>: $error<br>
						<b>错误代码</b>:$errorno<br>";
				if(!class_exists('Error',false))require(dirname(__FILE__).'/../Error.class.php');
				Error::show($str);
			}
		}
	}
}
?>