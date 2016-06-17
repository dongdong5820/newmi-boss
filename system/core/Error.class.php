<?php
/**
 * @Copyright: Copyright © 2009-2011 动网创新科技深圳有限公司 All Rights Reserved
 * ---------------------------------------------
 * @desc: 错误处理类
**/
if(App::$config['ERROR_HANDLE']){
	function exception_handler(Exception $e) {
		throw new Error($e->getMessage(),$e->getCode(),$e->getFile(),$e->getLine());
	}
	function error_handler($errorCode,$errorMessage,$errorFile,$errorLine) {
		throw new Error($errorMessage,$errorCode,$errorFile,$errorLine);
	}
	set_exception_handler('exception_handler');
	set_error_handler('error_handler',E_ALL ^E_NOTICE);
}
class Error extends Exception{
	public $errorMessage='';
	public $errorFile='';
	public $errorLine=0;
	public $errorCode='';
	public $errorLevel='';
	public $trace='';
	public function __construct($errorMessage,$errorCode=0,$errorFile='',$errorLine=0)
	{
		parent::__construct($errorMessage,$errorCode);
		$this->errorMessage=$errorMessage;
		$this->errorCode=$errorCode==0?$this->getCode():$errorCode;
		$this->errorFile=$errorFile==''?$this->getFile():$errorFile;
		$this->errorLine=$errorLine==0?$this->getLine():$errorLine;
		$this->errorLevel=$this->getLevel();
		$this->trace=$this->trace();
		$this->showError();
	}
	public function trace()
	{
		$trace = $this->getTrace();
		$traceInfo='';
		$time = date("Y-m-d H:i:s");
		foreach($trace as $t) {
			$traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') ';
			$traceInfo .= $t['class'].$t['type'].$t['function'].'(';
			$traceInfo .=")<br />\r\n";
		}
		return $traceInfo ;
	}
	public function getLevel()
	{
		$Level_array=array(	1=>'致命错误(E_ERROR)',
		2 =>'警告(E_WARNING)',
		4 =>'语法解析错误(E_PARSE)',
		8 =>'提示(E_NOTICE)',
		16 =>'E_CORE_ERROR',
		32 =>'E_CORE_WARNING',
		64 =>'编译错误(E_COMPILE_ERROR)',
		128 =>'编译警告(E_COMPILE_WARNING)',
		256 =>'致命错误(E_USER_ERROR)',
		512 =>'警告(E_USER_WARNING)',
		1024 =>'提示(E_USER_NOTICE)',
		2047 =>'E_ALL',
		2048 =>'E_STRICT'
		);
		return isset($Level_array[$this->errorCode])?$Level_array[$this->errorCode]:$this->errorCode;
	}
	static public function show($message="")
	{
		throw new Error($message);
	}
	static public function write($message)
	{
		$log_path=App::$config['LOG_PATH'];
		if(!is_dir($log_path))
		{
			@mkdir($log_path,0755);
		}
		$log_path= rtrim($log_path,"/")."/";
		$time=date('Y-m-d H:i:s');
		$ip=get_client_ip();
		$destination =$log_path .date("Y-m-d").".log";
		@error_log("{$time} | {$ip} | {$_SERVER['PHP_SELF']} |{$message}\r\n",3,$destination);
	}
	public function showError(){
		if(App::$config['LOG_ON']){
			self::write($this->message);
		}
		echo
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统错误提示!</title>
</head>
<body>
	<div style="border:1px solid #9CF; margin:20px auto; width:800px;">
	<div style="border:1px solid #fff; padding:15px; background:#f0f6f9;">
	<div style="border-bottom:1px #9CC solid; font-size:26px;font-family: "Microsoft Yahei", Verdana, arial, sans-serif; line-height:40px; height:40px; font-weight:bold">系统错误提示!</div>
	<div style="height:20px; border-top:1px solid #fff"></div>
	<div style="border:1px dotted #F90; border-left:6px solid #F60; padding:15px; background:#FFC">
		出错信息：'.$this->message.'
	</div>';
		if($this->errorCode&&App::$config['DEBUG'])
		{
			echo  '<div style="border:1px dotted #F90; border-left:6px solid #F60; padding:15px; background:#FFC">
			出错文件：'.$this->errorFile.'
		</div>
		<div style="border:1px dotted #F90; border-left:6px solid #F60; padding:15px; background:#FFC">
			错误行：'.$this->errorLine.'
		</div>
		<div style="border:1px dotted #F90; border-left:6px solid #F60; padding:15px; background:#FFC">
			错误级别：'.$this->errorLevel.'
		</div>
		<div style="border:1px dotted #F90; border-left:6px solid #F60; padding:15px; background:#FFC;line-height:20px;">
			Trace信息：<br>'.$this->trace.'
		</div>';
		}
		echo '<div style="height:20px;"></div>
<div style=" font-size:15px;">您可以选择 &nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'" title="重试">重试</a> &nbsp;&nbsp;<a href="javascript:history.back()" title="返回">返回</a>  或者  &nbsp;&nbsp;<a href="'.App::$config['__APP__'].'" title="回到首页">回到首页</a> </div>
</div>
</div>
</body>
</html>';
		exit;
	}
}
?>