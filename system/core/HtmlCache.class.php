<?php

class HtmlCache
{
	static public $config;
	static private $module;
	static private $action;
	static private $cacheAble;
	static private $cacheFile;
	static public function  init($config=array())
	{
		self::$config['HTML_CACHE_ON']=isset($config['HTML_CACHE_ON'])?$config['HTML_CACHE_ON']:false;
		self::$config['HTML_CACHE_PATH']=isset($config['HTML_CACHE_PATH'])?$config['HTML_CACHE_PATH']:'./data/html_cache/';
		self::$config['HTML_CACHE_SUFFIX']=isset($config['HTML_CACHE_SUFFIX'])?$config['HTML_CACHE_SUFFIX']:'.html';
		self::$config['HTML_CACHE_RULE']=isset($config['HTML_CACHE_RULE'])?$config['HTML_CACHE_RULE']:'';
	}
	static public function read($module='',$action='')
	{
		self::$cacheAble=false;
		self::$module=isset($module)?$module:App::$module;
		self::$action=isset($action)?$action:App::$action;
		if(empty(self::$config)||(self::$config['HTML_CACHE_ON']!=true)||empty(self::$config['HTML_CACHE_RULE']))
		{
			return false;
		}
		if(false==self::_checkRule())
		{
			return false;
		}
		if(false==self::_checkDir())
		{
			return false;
		}
		self::$cacheAble=true;
		self::$cacheFile=self::$config['HTML_CACHE_PATH'].self::$module.'/'.self::$action.'/';
		self::$cacheFile.=md5($_SERVER['REQUEST_URI']).self::$config['HTML_CACHE_SUFFIX'];
		$expires=self::$config['HTML_CACHE_RULE'][self::$module][self::$action];
		if(file_exists(self::$cacheFile)&&(time()<(filemtime(self::$cacheFile)+$expires)))
		{
			readfile(self::$cacheFile);
			return true;
		}
		ob_start();
		return false;
	}
	static  public function write()
	{
		if(self::$cacheAble)
		{
			$contents=ob_get_contents();
			if(strlen($contents)>0&&file_put_contents(self::$cacheFile,$contents))
			{
				ob_end_clean();
				self::read(self::$module,self::$action);
			}
			else
			{
				ob_end_flush();
			}
		}
	}
	static  public function clear($path='')
	{
		$path   =  empty($path)?self::$config['HTML_CACHE_PATH']:$path;
		if ( $handle = opendir( $path ) )
		{
			while ( $file = readdir( $handle ) )
			{
				if (is_dir( $path .$file)&&$file!='.'&&$file!='..')
				self::clear($path .$file.'/');
				if ( is_file( $path .$file ))
				@unlink( $path .$file );
			}
			closedir( $handle );
			return true;
		}
		return false;
	}
	static private function _checkRule()
	{
		if(isset(self::$config['HTML_CACHE_RULE'])&&!empty(self::$config['HTML_CACHE_RULE']))
		{
			foreach(self::$config['HTML_CACHE_RULE'] as $key =>$value)
			{
				if($key==self::$module&&!empty(self::$config['HTML_CACHE_RULE'][self::$module]))
				{
					if (array_key_exists(self::$action,self::$config['HTML_CACHE_RULE'][self::$module]))
					{
						return true;
					}
				}
			}
		}
		return false;
	}
	static private function _checkDir()
	{
		if(substr(self::$config['HTML_CACHE_PATH'],-1) != "/")
		{
			self::$config['HTML_CACHE_PATH'] .= "/";
		}
		$cache_path=self::$config['HTML_CACHE_PATH'];
		if(self::_mkdir($cache_path))
		{
			$cache_path=$cache_path.'/'.self::$module;
			if(self::_mkdir($cache_path))
			{
				$cache_path=$cache_path.'/'.self::$action;
				if(self::_mkdir($cache_path))
				{
					return true;
				}
			}
		}
		return false;
	}
	static private function _mkdir($dir)
	{
		if((!file_exists($dir))||(!is_dir($dir)))
		{
			if (!@mkdir($dir,0777))
			{
				return false;
			}
		}
		if(!is_writable($dir))
		{
			if(!@chmod($dir,0777))
			{
				return false;
			}
		}
		return true;
	}
}
?>