<?php
//数据过滤函数库
/*
功能：用来过滤字符串和字符串数组，防止被挂马和sql注入
参数$data，待过滤的字符串或字符串数组，
$force为true，忽略get_magic_quotes_gpc
*/
function in($data, $force=false, $htmlspecialchar=true)
{
	return $data;
}

//参数过滤
function clean($data) {
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			unset($data[$key]);
			$data[clean($key)] = clean($value);
		}
	} else { 
		$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8'); //仅编码双引号
		if(!get_magic_quotes_gpc()) {
			$data = addslashes($data);//转义单双引号
		}
	}

	return $data;
}

//用来还原字符串和字符串数组，把已经转义的字符还原回来
function out($data)
{
	if(is_string($data))
	{
		return $data = stripslashes($data);
	}
	else if(is_array($data))//如果是数组采用递归过滤
	{
		foreach($data as $key=>$value)
		{
			$data[$key]=out($value);
		}
		return $data;
	}
	else
	{
		return $data;
	}
}

//文本输入
function text_in($str)
{
	$str=strip_tags($str,'<br>');
	$str = str_replace(" ", "&nbsp;", $str);
	$str = str_replace("\n", "<br>", $str);
	if(!get_magic_quotes_gpc())
	{
		$str = addslashes($str);
	}
	return $str;
}

//文本输出
function text_out($str)
{
	$str = str_replace("&nbsp;", " ", $str);
	$str = str_replace("<br>", "\n", $str);
	$str = stripslashes($str);
	return $str;
}

//html代码输入
function html_in($str)
{
	$search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
	"'<iframe[^>]*?>.*?</iframe>'si", // 去掉iframe
	);
	$replace = array ("",
	"",
	);
	$str=@preg_replace ($search, $replace, $str);
	$str=htmlspecialchars($str);
	if(!get_magic_quotes_gpc())
	{
		$str = addslashes($str);
	}
	return $str;
}

//html代码输出
function html_out($str)
{
	if(function_exists('htmlspecialchars_decode'))
	$str=htmlspecialchars_decode($str);
	else
	$str=html_entity_decode($str);

	$str = stripslashes($str);
	return $str;
}

// 获取客户端IP地址
function get_client_ip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER['REMOTE_ADDR'];
	else
	$ip = "unknown";
	return $ip;
}

//中文字符串截取
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
	switch($charset)
	{
		case 'utf-8':$char_len=3;break;
		case 'UTF8':$char_len=3;break;
		default:$char_len=2;
	}
	//小于指定长度，直接返回
	if(strlen($str)<=($length*$char_len))
	{
		return $str;
	}
	if(function_exists("mb_substr"))
	{
		$slice= mb_substr($str, $start, $length, $charset);
	}
	else if(function_exists('iconv_substr'))
	{
		$slice=iconv_substr($str,$start,$length,$charset);
	}
	else
	{
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	if($suffix)
	return $slice."…";
	return $slice;
}

//检查是否是正确的邮箱地址，是则返回true，否则返回false
function is_email($user_email){
	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
	if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false){
		if (preg_match($chars, $user_email)){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
// 检查字符串是否是UTF8编码,是返回true,否则返回false
function is_utf8($string)
{
	return preg_match('%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents,$from='gbk',$to='utf-8'){
	$from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
	$to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
	if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
		//如果编码相同或者非字符串标量则不转换
		return $fContents;
	}
	if(is_string($fContents) ) {
		if(function_exists('mb_convert_encoding')){
			return mb_convert_encoding ($fContents, $to, $from);
		}elseif(function_exists('iconv')){
			return iconv($from,$to,$fContents);
		}else{
			return $fContents;
		}
	}
	elseif(is_array($fContents)){
		foreach ( $fContents as $key => $val ) {
			$_key =     auto_charset($key,$from,$to);
			$fContents[$_key] = auto_charset($val,$from,$to);
			if($key != $_key )
			unset($fContents[$key]);
		}
		return $fContents;
	}
	else{
		return $fContents;
	}
}

//获取微秒时间，常用于计算程序的运行时间
function utime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

//生成唯一的值
function getuniqid()
{
	return md5(uniqid(rand(), true));
}

//遍历删除目录和目录下所有文件
function del_dir($dir)
{
	if (!is_dir($dir))
	{
		return false;
	}
	$handle = opendir($dir);
	while (($file = readdir($handle)) !== false)
	{
		if ($file != "." && $file != "..")
		{
			is_dir("$dir/$file")?	del_dir("$dir/$file"):@unlink("$dir/$file");
		}
	}
	if (readdir($handle) == false)
	{
		closedir($handle);
		@rmdir($dir);
	}
}

/*
*  取出指定二维数组中指定 key 的数据集
*  @param  $array      数组数据
*  @param  $keyid      要取得的 key
*  @param  $mode       取出的模式: 'string': 字符串  'array': 数组  'sum': 同 key 元素之和
*/
function get_array(&$array, $keyid, $mode='array') {
	if(!is_array($array)) return false;
	$arr_tmp = array();
	foreach($array as $key => $val) {
		if(!in_array($val[$keyid], $arr_tmp)) {
			$arr_tmp[] = $val[$keyid];
		}
	}
	return ($mode == 'array')? $arr_tmp: implode("','", $arr_tmp);
}


/*
*  根据指定的数组和关键字生成一维数组
*/
function create_array($array, $key_val, $key_name, $key_name2='') {
	if(is_array($array)) {
		$result = array();
		foreach($array as $val) {
			if(!empty($key_name2) && !empty($val[$key_name2])){
				$result[$val[$key_val]] = $val[$key_name]." (".$val[$key_name2] .")";
				continue;
			}
			$result[$val[$key_val]] = $val[$key_name];
		}
		return $result;
	}
	return false;
}

/*
*  计算文件大小
*  @param int $size 文件大小
*/
if ( !function_exists("get_realsize")){
	function get_realsize($size = 0)
	{
		$kb = 1024;         // Kilobyte
		$mb = 1024 * $kb;   // Megabyte
		$gb = 1024 * $mb;   // Gigabyte
		$tb = 1024 * $gb;   // Terabyte
		if($size < $kb)
		{
			return $size." B";
		}
		else if($size < $mb)
		{
			return round($size/$kb,2)." KB";
		}
		else if($size < $gb)
		{
			return round($size/$mb,2)." MB";
		}
		else if($size < $tb)
		{
			return round($size/$gb,2)." GB";
		}
		else
		{
			return round($size/$tb,2)." TB";
		}
	}
}


function getIPLoc($queryIP){

    $url = 'http://ip.qq.com/cgi-bin/searchip?searchip1=' . $queryIP;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_ENCODING, 'gb2312');

    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回

    $result = curl_exec($ch);

    $result = mb_convert_encoding($result, "utf-8", "gb2312"); // 编码转换，否则乱码

    curl_close($ch);

    preg_match("@<span>(.*)</span></p>@iU", $result, $ipArray);

    $loc = $ipArray[1];

    return $loc;

}

/*
 * 循环创建目录
 * @param string $dir 目录如：xxx/xxx/xx
 * @param int $mode 权限码
 */
function createDirectory($dir,$mode=0777){
	if( ! $dir ){return false;}
	$dir = str_replace( "\\", "/", $dir);
	$mdir = "";
	foreach(explode("/", $dir) as $val){
		$mdir .= $val."/";
		if( $val == ".." || $val == "." ){continue;}
		if(!file_exists($mdir)) {
			if(!@mkdir($mdir)){
				return false;
				exit;
			}else{chmod($mdir,$mode);}
		}
	}
	return true;
}

/**
 * @desc 中文反序列化
 */
function mb_unserialize($serial_str) {
	$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
	return unserialize($out);
}

/**
 * @desc 判断是否json格式
 */
function is_json($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

/**
  * 解析json串
  * @param type $json_str
  * @return type
*/
function analyJson($json_str) {
    $out_arr = array();

    if(is_string($json_str)){
    	$json_str = str_replace('\\', '', $json_str);
    	preg_match('/{.*}/', $json_str, $out_arr);
    }else{
    	return FALSE;
    }

    if (!empty($out_arr)) {
    	$result = json_decode($out_arr[0], TRUE);
    } else {
    	return FALSE;
    }
    return $result;
}

/**
 * @desc 求出2个数组的不同值
 */
function array_diffs($array1,$array2){
	return array_merge(array_diff($array1,$array2),array_diff($array2,$array1));
}
?>