<?php
/**
 * @Copyright: Copyright © 2009-2011 动网创新科技深圳有限公司 All Rights Reserved
 * ---------------------------------------------
 * @desc: 数据操作类
**/
class Model{
	public $db = NULL;
	public $cache=NULL;
	public $config =array();
	public $sql = '';
	public  $pre = '';
	private $data =array();
	private $options=array();
	public function __construct($config=array()){
		$this->config=$config;
		$this->options['field']='*';
		$this->pre= $this->config['DB_PREFIX'];
	}
	public function connect(){
		if(!is_object($this->db)){
			$db_type= $this->config['DB_TYPE'];
			if(!is_file(dirname(__FILE__).'/db/'.$db_type.'.class.php')){
				$this->error($db_type.'数据库类型没有驱动');
			}
			if(!class_exists($db_type,false)){require(dirname(__FILE__).'/db/'.$db_type.'.class.php');}
			$this->db = new $db_type();
			$this->db->connect(
			$this->config['DB_HOST'] .":".$this->config['DB_PORT'],
			$this->config['DB_USER'],
			$this->config['DB_PWD'],
			$this->config['DB_NAME'],
			$this->config['DB_CHARSET'],
			$this->config['DB_PCONNECT'],
			$this->config['DB_PREFIX']
			);
		}
	}
	public function table($table,$ignore_prefix=false)
	{
		if($ignore_prefix)
		{
			$this->options['table']='`'.$table.'`';
		}
		else
		{
			$this->options['table']='`'.$this->config['DB_PREFIX'].$table.'`';
		}
		return $this;
	}
	public function __call($method,$args)
	{
		$method=strtolower($method);
		if(in_array($method,array('field','data','where','group','having','order','limit','cache')))
		{
			$this->options[$method] =$args[0];
			return $this;
		}
		else
		{
			$this->error($method.'方法在Model.class.php类中没有定义');
		}
	}
	public function query($sql)
	{
		if(empty($sql))
		{
			return false;
		}
		$this->sql=$sql;
		if(strpos(trim(strtolower($sql)),'select')===0)
		{
			$data=array();
			$data=$this->_readCache('query');
			if(!empty($data))
			{
				return $data;
			}
			$this->connect();
			$query=$this->db->query($this->sql);
			while($row=$this->db->fetch_array($query))
			{
				$data[]=$row;
			}
			$this->_writeCache($data,'query');
			return $data;
		}
		else
		{
			$this->connect();
			return $query=$this->db->query($this->sql);
		}
	}
	public function count()
	{
		$table=$this->options['table'];
		$field='count(*)';
		$where=$this->_parseCondition();
		$this->sql="SELECT $field FROM $table $where";
		$data="";
		$data=$this->_readCache('count');
		if(!empty($data))
		{
			return $data;
		}
		$this->connect();
		$query=$this->db->query($this->sql);
		$data=$this->db->fetch_array($query);
		$this->_writeCache($data['count(*)'],'count');
		return $data['count(*)'];
	}
	public function find()
	{
		$table=$this->options['table'];
		$field=$this->options['field'];
		$this->options['limit']=1;
		$where=$this->_parseCondition();
		$this->options['field']='*';
		$this->sql="SELECT $field FROM $table $where";
		$data="";
		$data=$this->_readCache('find');
		if(!empty($data))
		{
			return $data;
		}
		$this->connect();
		$query=$this->db->query($this->sql);
		$data=$this->db->fetch_array($query);
		$this->_writeCache($data,'find');
		return $data;
	}
	public function select()
	{
		$table=$this->options['table'];
		$field=$this->options['field'];
		$where=$this->_parseCondition();
		$this->options['field']='*';
		$this->sql="SELECT $field FROM $table $where";
		$data=array();
		$data=$this->_readCache('select');
		if(!empty($data))
		{
			return $data;
		}
		$this->connect();
		$query=$this->db->query($this->sql);
		while($row=$this->db->fetch_array($query))
		{
			$data[]=$row;
		}
		$this->_writeCache($data,'select');
		return $data;
	}
	public function insert()
	{
		$this->connect();
		$table=$this->options['table'];
		$data=$this->_parseData('add');
		$this->sql="INSERT INTO $table $data";
		$query = $this->db->query($this->sql);
		if($this->db->affected_rows())
		{
			$id=$this->db->insert_id();
			return empty($id)?$this->db->affected_rows():$id;
		}
		return false;
	}
	public function replace()
	{
		$this->connect();
		$table=$this->options['table'];
		$data=$this->_parseData('add');
		$this->sql="REPLACE INTO $table $data";
		$query = $this->db->query($this->sql);
		if($this->db->affected_rows())
		{
			return  $this->db->insert_id();
		}
		return false;
	}
	public function update()
	{
		$this->connect();
		$table=$this->options['table'];
		$data=$this->_parseData('save');
		$where=$this->_parseCondition();
		if(empty($where))
		{
			return false;
		}
		$this->sql="UPDATE $table SET $data $where";

		$query = $this->db->query($this->sql);
		$affected_count = $this->db->affected_rows();
		if(intval($affected_count) >= 0){
			return true;
		}
	}
	public function delete()
	{
		$this->connect();
		$table=$this->options['table'];
		$where=$this->_parseCondition();
		if(empty($where))
		{
			return false;
		}
		$this->sql="DELETE FROM $table $where";
		$query = $this->db->query($this->sql);
		return $this->db->affected_rows();
	}
	public function getSql()
	{
		return $this->sql;
	}
	public function clear()
	{
		if($this->config['DB_CACHE_ON'])
		return $this->cache->clear();
		return false;
	}
	private function _parseData($type)
	{
		if((!isset($this->options['data']))||(empty($this->options['data'])))
		{
			unset($this->options['data']);
			return false;
		}
		if(is_string($this->options['data']))
		{
			$data=$this->options['data'];
			unset($this->options['data']);
			return $data;
		}
		switch($type)
		{
			case 'add':
				$data=array();
				$data['key']="";
				$data['value']="";
				foreach($this->options['data'] as $key=>$value)
				{
					$data['key'].="`$key`,";
					$data['value'].="'$value',";
				}
				$data['key']=substr($data['key'],0,-1);
				$data['value']=substr($data['value'],0,-1);
				unset($this->options['data']);
				return " (".$data['key'].") VALUES (".$data['value'].") ";
				break;
			case 'save':
				$data="";
				foreach($this->options['data'] as $key=>$value)
				{
					$data.="`$key`='$value',";
				}
				$data=substr($data,0,-1);
				unset($this->options['data']);
				return $data;
				break;
			default:
				unset($this->options['data']);
				return false;
		}
	}
	private function _parseCondition()
	{
		$condition="";
		if(!empty($this->options['where']))
		{
			$condition=" WHERE ";
			if(is_string($this->options['where']))
			{
				$condition.=$this->options['where'];
			}
			else if(is_array($this->options['where']))
			{
				foreach($this->options['where'] as $key=>$value)
				{
					$condition.=" `$key`='$value' AND ";
				}
				$condition=substr($condition,0,-4);
			}
			else
			{
				$condition="";
			}
			unset($this->options['where']);
		}
		if(!empty($this->options['group'])&&is_string($this->options['group']))
		{
			$condition.=" GROUP BY ".$this->options['group'];
			unset($this->options['group']);
		}
		if(!empty($this->options['having'])&&is_string($this->options['having']))
		{
			$condition.=" HAVING ".$this->options['having'];
			unset($this->options['having']);
		}
		if(!empty($this->options['order'])&&is_string($this->options['order']))
		{
			$condition.=" ORDER BY ".$this->options['order'];
			unset($this->options['order']);
		}
		if(!empty($this->options['limit'])&&(is_string($this->options['limit'])||is_numeric($this->options['limit'])))
		{
			$condition.=" LIMIT ".$this->options['limit'];
			unset($this->options['limit']);
		}
		if(empty($condition))
		return "";
		return $condition;
	}
	public function initCache()
	{
		if(is_object($this->cache))
		{
			return true;
		}
		else if($this->config['DB_CACHE_ON'])
		{
			require_once(dirname(__FILE__).'/Cache.class.php');
			$config['DATA_CACHE_PATH']=$this->config['DB_CACHE_PATH'];
			$config['DATA_CACHE_TIME']=$this->config['DB_CACHE_TIME'];
			$config['DATA_CACHE_CHECK']=$this->config['DB_CACHE_CHECK'];
			$config['DATA_CACHE_FILE']=$this->config['DB_CACHE_FILE'];
			$config['DATA_CACHE_SIZE']=$this->config['DB_CACHE_SIZE'];
			$config['DATA_CACHE_FLOCK']=$this->config['DB_CACHE_FLOCK'];
			$this->cache=new Cache($config);
			return true;
		}
		else
		{
			return false;
		}
	}
	public  function _readCache($cache_prefix)
	{
		$expire=isset($this->options['cache'])?$this->options['cache']:$this->config['DB_CACHE_TIME'];
		if($expire==0)
		return false;
		$data="";
		if($this->config['DB_CACHE_ON']&&function_exists('db_cache_get_ext'))
		{
			$data=db_cache_get_ext($cache_prefix.$this->sql);
		}
		if($this->initCache())
		{
			$data=$this->cache->get($cache_prefix.$this->sql);
		}
		if(!empty($data))
		{
			unset($this->options['cache']);
			return $data;
		}
		else
		{
			return "";
		}
	}
	private function _writeCache($data,$cache_prefix)
	{
		$expire=isset($this->options['cache'])?$this->options['cache']:$this->config['DB_CACHE_TIME'];
		unset($this->options['cache']);
		if($expire==0)
		return false;
		if($this->config['DB_CACHE_ON']&&function_exists('db_cache_set_ext'))
		{
			return $data=db_cache_set_ext($cache_prefix.$this->sql,$data,$expire);
		}
		if($this->initCache())
		{
			return $this->cache->set($cache_prefix.$this->sql,$data,$expire);
		}
		return false;
	}
	public function error($str){
		if(!class_exists('Error',false))require dirname(__FILE__) .'/Error.class.php';
		Error::show($str);
	}

	/**
	 * @desc 启动事务
	 * @author Rider
	 * @access public
	 * @return void
	 */
	public function affairs_start() {
		$this->query("BEGIN");
	}

	/**
	 * @desc 提交事务
	 * @author Rider
	 * @access public
	 * @return boolean
	 */
	public function affairs_submit() {
		$this->query("COMMIT");
	}

	/**
	 * @desc 事务回滚
	 * @author Rider
	 * @access public
	 * @return boolean
	 */
	public function affairs_rolled_back() {
		$this->query("ROLLBACK");
	}

	/**
	 * @desc 插入多条数据
	 * @author Rider
	 * @access public
	 * @param	$field	字段		`name`,`sex` ...
	 * @param	$array  要插入的数据组, 例子(array(0=>array(name=>'张三',sex=>1,...),....))
	 * @return	true,false
	 */
	public function insertall($field,$array) {
		if(!$field || !$array || !is_array($array)){
			throw new Exception('参数 字段 或者 数据数组 错误');
		}

		$this->connect();
		$table=$this->options['table'];

		$fieldValue = "";
		$fieldName = "";
		$fieldValueStr = "";
		foreach ($array as $key=>$row){
			$fieldValue .= "(";
			foreach ($row as $k=>$v){
				$fieldValueStr .= '\'' . $v . '\'' . ",";
			}
			$fieldValueStr = rtrim($fieldValueStr,',');
			$fieldValue .= $fieldValueStr;
			$fieldValue .= "),";
			$fieldValueStr = "";
		}

		$fieldValue = rtrim($fieldValue,',');

		$this->sql="INSERT INTO " . $table . " (" . $field . ") VALUES " . $fieldValue ;
		$query = $this->db->query($this->sql);

		if($this->db->affected_rows() == count($array)){  //影响的记录数是否等于需要插入的数组数
			return $this->db->affected_rows();
		}else{
			return false;
		}

	}
}
?>