<?php
/**
 * @Copyright: Copyright © 2013 100MSH.COM All Rights Reserved 版权所有 粤ICP备13004409号
 * ---------------------------------------------
 * @desc: 页面自动中转
**/
class App{
	static public $module;
	static public $action;
	static public $config = array();
	static public $db_config = array(); //数据库基本配置数组
	static public $db_conn_cfg = array(); //数据库连接配置数组

	public function __construct($config=array()){
		self::$config = $config;
		require self::$config['CORE_PATH'].'/system/lib/common.function.php';
		$_GET = clean($_GET);
		$_POST = clean($_POST);
		$_REQUEST = clean($_REQUEST);
		$_COOKIE = clean($_COOKIE);
		$_FILES = clean($_FILES);
		$_SERVER = clean($_SERVER);
		
		if (self::$config['DEBUG']){
			error_reporting(E_ALL);
			if(!class_exists('Error',false))require self::$config['CORE_PATH'].'/system/core/Error.class.php';
		}else{
			error_reporting(0);
		}
		if (function_exists('spl_autoload_register')){
			spl_autoload_register('self::autoload');
		}
		if (self::$config['HTML_CACHE_ON']){
			require self::$config['CORE_PATH'] .'/system/core/HtmlCache.class.php';
			HtmlCache::init(self::$config);
		}
	}
	private function _parseUrl(){
		$script_name = $_SERVER["SCRIPT_NAME"];
		$uri = $_SERVER["REQUEST_URI"];
		if (@strpos($uri,$script_name,0) !== FALSE){
			$url = substr($uri,strlen($script_name));
		}else{
			$script_name = dirname($script_name);
			if (@strpos($uri,$script_name,0) !== FALSE){
				$url = substr($uri,strlen($script_name));
			}
		}
		if ($url &&$url[0] == '/'){
			$url = substr($url,1);
		}
		if ($url &&FALSE !== ($pos = @strrpos($url,'?'))){
			$url = substr($url,0,$pos);
		}
		if ($url &&($pos = strrpos($url,self::$config['URL_HTML_SUFFIX'])) >0){
			$url = substr($url,0,$pos);
		}
		$flag=0;
		if($url&&($pos=@strpos($url,self::$config['URL_MODULE_DEPR'],1))>0){
			self::$module=substr($url,0,$pos);
			$url=substr($url,$pos+1);
			$flag=1;
		}else{
			self::$module = $url;
		}
		$flag2=0;
		if($url&&($pos=@strpos($url,self::$config['URL_ACTION_DEPR'],1))>0){
			self::$action=substr($url,0,$pos);
			$url=substr($url,$pos+1);
			$flag2=1;
		}else{
			if ($flag){
				self::$action=$url;
			}
		}
		if ( $flag2 ){
			$param = explode(self::$config['URL_PARAM_DEPR'],$url);
			$param_count = count($param);
			for($i = 0;$i <$param_count;$i = $i +2){
				$_GET[$i] = $param[$i];
				if (isset($param[$i+1])){
					if (!is_numeric($param[$i])){
						$_GET[$param[$i]] = $param[$i+1];
					}
					$_GET[$i+1]=$param[$i+1];
				}
			}
		}
	}
	private function _define(){
		$root = str_replace(basename($_SERVER["SCRIPT_NAME"]),'',$_SERVER["SCRIPT_NAME"]);
		$root = substr($root,0,-1);
		$root = 'http://'.$_SERVER['HTTP_HOST'] .$root;
		if( preg_match('@([a-z0-9_-]+)@i',self::$config['PUBLIC_PATH'],$match ) ){
			$public_path = $match[1];
		}else{
			$public_path = 'public';
		}
		self::$config['__ROOT__']=$root;
		self::$config['__PUBLIC__']=$root .'/'.$public_path;
		if (self::$config['URL_REWRITE_ON']){
			self::$config['__APP__']=self::$config['__ROOT__'];
		}else{
			self::$config['__APP__']=self::$config['__ROOT__'] .'/'.basename($_SERVER["SCRIPT_NAME"]);
		}
		self::$config['__URL__']=self::$config['__APP__'] .'/'.self::$module;
	}
	public function run(){
		$this->_parseUrl();
		self::$module = empty(self::$module) ?self::$config['MODULE_DEFAULT'] : self::$module;
		self::$action = empty(self::$action) ?self::$config['ACTION_DEFAULT'] : self::$action;
		self::$module = str_replace(array("/","\\"),'',self::$module);
		self::$action = str_replace(array("/","\\"),'',self::$action);
		$this->_define();
		if($this->_checkModule(self::$module)){
			$module = self::$module;
		}else{
			$this->error(self::$module ."模块不存在");
		}
		if (FALSE == $this->_readHtmlCache($module,self::$action)){
			$this->_execute($module);
		}
	}
	private function _checkModule($module){
		if (is_file(self::$config['CORE_PATH'].self::$config['MODULE_PATH'] .$module .self::$config['MODULE_SUFFIX'])){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	private function _execute($module){
		$module_suffix = explode('.',self::$config['MODULE_SUFFIX'],2);
		$classname = $module .$module_suffix[0];
		if(!class_exists($classname,false)){require self::$config['CORE_PATH'].self::$config['MODULE_PATH'] .$module .self::$config['MODULE_SUFFIX'];}
		if (class_exists($classname,false)){
			$object = new $classname();
			if ($classname == self::$action){
				return TRUE;
			}
			$action = "";
			if (method_exists($object,self::$action)){
				$action = self::$action;
			}else{
				$this->error(self::$action ."操作方法在".$module ."模块中不存在");
			}
			call_user_func(array(&$object,$action));
			$this->_writeHtmlCache();
		}else{
			$this->error($classname ." 类不存在");
		}
	}
	private function _readHtmlCache($module='',$action=''){
		if (self::$config['HTML_CACHE_ON'] && HtmlCache::read($module,$action))return TRUE;
		return FALSE;
	}
	private function _writeHtmlCache(){
		if (self::$config['HTML_CACHE_ON']){
			HtmlCache::write();
		}
	}
	static public function autoload($className){
		if(class_exists($className,false))return true;
		$class_array = array();
		if(strrpos($className,'Mod')>0){
			$class_array[] = self::$config['CORE_PATH'] .'/module/'.$className .'.class.php';
		}else{
			$base_path =  self::$config['CORE_PATH'].'/system/';
			$class_array[] = $base_path .'/lib/'.$className .'.class.php';
			$class_array[] = $base_path .'/ext/'.$className .'.class.php';
		}
		foreach ($class_array as $file){
			if(is_file($file)){
				require $file;return TRUE;
			}
		}
		return FALSE;
	}
	public function error($str){
		if(APP::$config['DEBUG']){
			Error::show($str);
		}else{
			$error_url=APP::$config['ERROR_URL'];
			if($error_url!=''){ //如果这是错误页面跳转错误页面
				header('Location: ' . $error_url);
			}exit;	
		}
	}

	/*数据库初始化*/
	/* static public function db(){
		static $db_handler;
		if(isset($db_handler)){return $db_handler;}
		if(!isset($db_cfg)){
		    require self::$config['CORE_PATH'].'/db.config.php';
			if(!class_exists('Model',false))require self::$config['CORE_PATH'].'/system/core/Model.class.php';
			if(!class_exists('DBhand',false))require self::$config['CORE_PATH'].'/system/core/DBhand.class.php';
		};
		$db_handler=new DBhand($db_cfg,$config);
		return $db_handler;
	} */

	/**
	 * 切换当前的数据库连接
	 * @access public
	 * @param mixed $db_config  数据库连接信息
	 * @return Model
	 */
	static public function db($db_config='') {
		static $db_handler;
		static $db_conf;
		static $db_conn_cfg;
		if('' === $db_config && isset($db_handler)) {
			return $db_handler;
		}
		if(!isset($db_conf) || !isset($db_conn_cfg)){
			require self::$config['CORE_PATH'].'/db.config.php';
			if(!class_exists('Model',false))require self::$config['CORE_PATH'].'/system/core/Model.class.php';
			if(!class_exists('DBhand',false))require self::$config['CORE_PATH'].'/system/core/DBhand.class.php';
			$db_conf = $db_cfg;
			$db_conn_cfg = $config;
		};
		if('' === $db_config) {
			$db_handler=new DBhand($db_conf,$db_conn_cfg);
		}else{
			$db_conf = array_merge($db_conf,$db_config);
			$db_handler=new DBhand($db_conf,$db_conn_cfg);
		}
		return $db_handler;
	}
}
?>