<?php
/**
 * @Date: 2012.06.24
 * @desc: 数据缓存
 **/
class DataCache {
	private static $_host;
	private static $_port;
	private static $_timeout;            /* seconds */
	private static $_expire;            /* seconds */
	private static $prefix;
	private static $debug;
	private static $instance;
    private static $_connected;
    
	function __construct(){
				
	}
    
	function __destruct(){
		$this->close();
	}

	/**
     * @name: connect 
     * @access: 
     * @param: 
     * @param: 
     * @param:
     * @return: 
     * @desc: 连接服务器
     **/
	private static  function _connect(){
        if ( ! is_resource(self::$instance) )
        {
            self::$_host =    App::$config['DataCache_HOST'];
            self::$_port =    App::$config['DataCache_PORT'];
            self::$_timeout = App::$config['DataCache_TIMEOUT'];
            self::$prefix =   App::$config['DataCache_PREFIX'];
            self::$instance = new Memcache;
            return self::$instance->connect( self::$_host, self::$_port, self::$_timeout );
        }
        return true;
	}

	public static function set( $key, $value, $expire=3600, $flag=MEMCACHE_COMPRESSED ) {
        self::_connect(  );
		return self::$instance->set( $key, $value, $flag, time() + $expire );
	}

	public static function get( $key ) {
        self::_connect(  );
		return self::$instance->get( $key );
	}

	public static function add( $key, $value ){
        self::_connect(  );
		return self::$instance->add( $key, $value );
	}

	public static function delete ( $key, $timeout = 0 ) {
        self::_connect(  );
		return self::$instance->delete( $key, $timeout );
	}

	public static function close(  ){
		self::$instance->close(  );
	}
}