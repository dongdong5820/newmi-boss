<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Rider <Rider@100msh.com>
 * @date		2014-7-1
 * @desc		Redis静态化封装类
*/
class RedisCache {
	private static $_host;		//主机名
	private static $_port;		//端口
	private static $_timeout;   //服务器连接限制时间 (秒)
	private static $_expire;    //缓存有效时间 (秒)
	private static $_prefix;	//前缀
	private static $instance;	//缓存实例

	function __construct(){}

	/**
	 * @desc	连接redis服务器
	 * @param	$options 参数  (persistent: 是否持久连接 timeout: 是否连接限制时间)
	 * @return 	booble/resource
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	*/
	private static function _connect($persistent = TRUE , $timeout = FALSE){
        if ( self::$instance === NULL ) {
        	$func = $persistent ? 'pconnect' : 'connect';
        	self::$_host 	= App::$config['Redis_HOST'];
        	self::$_port 	= App::$config['Redis_PORT'];
        	self::$_prefix 	= App::$config['Redis_PREFIX'];
        	self::$_timeout = App::$config['Redis_TIMEOUT'];
        	self::$_expire 	= App::$config['Redis_EXPIRE'];
            self::$instance = new Redis();
            if($timeout === false){
            	return self::$instance->$func(self::$_host, self::$_port);
            }else{
            	return self::$instance->$func(self::$_host, self::$_port, self::$_timeout);
            }
        }

        return true;
	}

	/**
	 * @desc	检查当前连接实例的状态
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	 */
	public static function ping(){
		self::_connect();
		try {
			return self::$instance->ping();
		}catch (Exception $e){
			if(APP::$config['DEBUG']){
				$error_info = $e->getMessage();
			    Error::show($error_info);
			}return false;
		}
	}

	/**
	 * @desc	关闭Redis的连接实例,但是不能关闭用pconnect连接的实例
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	*/
	public static function close(){
		return self::$instance->close();
	}

	/**
	 * @desc 清除当前连接缓存数据
	 * @return boolean
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	 */
	public static function clear() {
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->flushDB();
	}

	/**
	 * @desc	获取string数据类型缓存
	 * @param	string $key 缓存变量名
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	*/
	public static function get($key){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		$value = self::$instance->get($key);
		$jsonData  = json_decode( $value, true );
		return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
	}

	/**
	 * @desc	写入string数据类型缓存
	 * @param	string $key 缓存变量名
	 * @param	string $value 缓存数据
	 * @param	booblean $expire 是否设置有效时间 (秒)
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	*/
	public static function set( $key, $value, $expire=FALSE ) {
        $_ping = self::ping();
		if($_ping === false){
			return false;
		}
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value; //对数组/对象数据进行缓存处理，保证数据完整性
        if($expire === FALSE) {
        	$result = self::$instance->set($key, $value);
        }else{
        	$result = self::$instance->setex($key, self::$_expire, $value);
        }
        return $result;
	}

	/**
	 * @desc string数据类型值加加操作,类似 ++$i ,如果 key 不存在时自动设置为 0 后进行加加操作
	 * @param string $key 缓存变量名
	 * @param int $default 操作时的默认值
	 * @return int　操作后的值
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function incr($key,$default=1){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		if($default == 1){
            return self::$instance->incr($key);
        }else{
            return self::$instance->incrBy($key, $default);
        }
	}

	/**
	 * @desc string数据类型值减减操作,类似 --$i ,如果 key 不存在时自动设置为 0 后进行减减操作
	 * @param string $key 缓存变量名
	 * @param int $default 操作时的默认值
	 * @return int　操作后的值
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function decr($key,$default=1){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		if($default == 1){
			return self::$instance->decr($key);
		}else{
			return self::$instance->decrBy($key, $default);
		}
	}

	/**
	 * @desc 删除string数据类型$key的缓存
	 * @param string || array $key 缓存KEY，支持单个健:"key1" 或多个健:array('key1','key2')
	 * @return boolean
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-1
	 */
	public static function del($key) {
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->delete($key);
	}

	/**
	 * @desc	获取hash数据类型hash表对应的$key的缓存值
	 * @param	string $hkey hash表key
	 * @param	string $key hash表中缓存变量名
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hGet($hkey,$key){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		$value = self::$instance->hGet($hkey,$key);
		$jsonData  = json_decode( $value, true );
		return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
	}

	/**
	 * @desc	写入hash数据类型hash表对应key的缓存值
	 * @param	string $hkey hash表key
	 * @param	string $key hash表中缓存变量名
	 * @param	string $value 缓存数据
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hSet($hkey,$key,$value) {
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		$value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value; //对数组/对象数据进行缓存处理，保证数据完整性
		$result = self::$instance->hSet($hkey, $key, $value);
		return $result;
	}

	/**
	 * @desc	批量取得HASH表中的VALUE。
	 * @param	string $hkey hash表key
	 * @param	string $keys 例:Array('field1', 'field2')
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hMget($hkey,$keys){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hMget($hkey,$keys);
	}

	/**
	 * @desc	批量填充HASH表。不是字符串类型的VALUE，自动转换成字符串类型。使用标准的值。NULL值将被储存为一个空的字符串。
	 * @param	string $hkey hash表key
	 * @param	string $members array('field1'=>$value1,'field2'=>$value2)
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hMset($hkey,$members) {
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hMset($hkey, $members);
	}

	/**
	 * @desc	删除hash数据类型hash表对应的$key的缓存值
	 * @param	string $hkey hash表key
	 * @param	string $key hash表中缓存变量名
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hDel($hkey,$key){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hDel($hkey,$key);
	}

	/**
	 * @desc	取得hash数据类型hash表所有的key值,以数组形式返回
	 * @param	string $hkey hash表key
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hKeys($hkey){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hKeys($hkey);
	}

	/**
	 * @desc	取得hash数据类型hash表所有的value值,以数组形式返回
	 * @param	string $hkey hash表key
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hVals($hkey){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hVals($hkey);
	}

	/**
	 * @desc	取得hash数据类型hash表所有的key/value键值对,以数组形式返回
	 * @param	string $hkey hash表key
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hGetAll($hkey){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		$result = self::$instance->hGetAll($hkey);

		if(is_array($result) && !empty($result)){
			foreach ($result as $key=>$val){
				$jsonData  = json_decode( $val, true );
				$result[$key] = ($jsonData === NULL) ?  $val : $jsonData; //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
			}
		}else{
			return false;
		}

		if(!empty($result)){
			return $result;
		}else{
			return false;
		}
	}

	/**
	 * @desc	删除hash数据类型hash表对应hkey所有的键值对
	 * @param	string $hkey hash表key
	 * @return 	mixed
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hDelAll($hkey){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->delete($hkey);
	}

	/**
	 * @desc hash数据类型值hash表对应$hkey=>$key=>$value值加加操作,类似 ++$i ,如果 key 不存在时自动设置为 0 后进行加加操作
	 * @param string $hkey hash表key
	 * @param string $key 缓存变量名
	 * @param int $default 操作时的默认值
	 * @return int　操作后的值
	 * @author 	Rider <Rider@100msh.com>
	 * @date 	2014-7-2
	 */
	public static function hIncrBy($hkey,$key,$default=1){
		$_ping = self::ping();
		if($_ping === false){
			return false;
		}
		return self::$instance->hIncrBy($hkey, $key, $default);
	}

}