<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Skylon <skylon@100msh.com>
 * @date		2014-6-30
 * @desc		系统当前登录用户信息管理
 */
class U{

	public static $userdata = array(); //用于保存当前登录用户的用户信息
	/**
	 * 初始化登录用户信息
	 *
	 */
	public static function init(){
		$admuser_dao=new Admusers();
		$u = $admuser_dao->getSession();
		if(!empty($u['userId'])){
			self::$userdata = $u;
		}
	}
	
	/**
	 * 判断当前是否已登录，如果已登录返回true,否则返回false;
	 *
	 * @return boolean
	 */
	public static function isLogin(){	
		if(empty(self::$userdata['userId'])){
			return false;
		}
		return true;
	}
	/**
	 *@desc 获取当前登录的用户id
	**/
	public static function getUserId(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['userId'];
	}
	/**
	 *@desc 获取当前登录的用户登陆名
	**/
	public static function getUserName(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['userName'];
	}
	/**
	 *@desc 获取当前登录的用户真实姓名
	**/
	public static function getUserRealName(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['userRealName'];
	}
	/**
	 *@desc 获取当前登录的用户所属公司ID
	**/
	public static function getUserCompId(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['companyId'];
	}
	
	/**
	 *@desc 获取当前登录的用户所属公司名称
	**/
	public static function getUserCompName(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['companyName'];
	}
	/**
	 *@desc 获取当前登录的用户所属职位名称
	**/
	public static function getUserPosName(){
		if(!self::isLogin()){
			return false;
		}
		return self::$userdata['positionName'];
	}
	/**
	 * @author	skylon
	 * @desc	保存登陆信息
	 * @param	array() $userInfo 用户信息
	 */
	public static function setUserSession($userInfo){
		$_SESSION[App::$config['SPOT'] . 'userId'] = $userInfo['userId'];
		$_SESSION[App::$config['SPOT'] . 'userName'] = $userInfo['userName'];
		$_SESSION[App::$config['SPOT'] . 'userRealName'] = $userInfo['userRealName'];
		$_SESSION[App::$config['SPOT'] . 'companyId'] = $userInfo['companyId'];
		$_SESSION[App::$config['SPOT'] . 'companyName'] = $userInfo['companyName'];
		$_SESSION[App::$config['SPOT'] . 'positionName'] = $userInfo['positionName'];
		$_SESSION[App::$config['SPOT'] . 'user_access_token'] = $userInfo['user_access_token'];
		$_SESSION[App::$config['SPOT'] . 'login_time'] = $userInfo['login_time'];
	}
	/**
	 * @author	skylon
	 * @desc	保存系统列表信息
	 * @param	array() $sysInfo 系统信息
	 */
	public static function setSysSession($sysInfo){
		$_SESSION[App::$config['SPOT'] . 'sys_list'] = serialize($sysInfo);
	}
	/**
	 * @author	skylon
	 * @desc	保存用户系统权限信息
	 * @param	array() $accessInfo 用户系统权限信息
	 */
	public static function setAccessSession($accessInfo){
		$_SESSION[App::$config['SPOT'] . 'access_list'] = serialize($accessInfo);
	}
	/**
	 * @author	skylon
	 * @desc	退出登陆
	 * @param	int $userID 用户id
	 */
	public static function delSession(){
		unset($_SESSION[App::$config['SPOT'] . 'userId'] );
		unset($_SESSION[App::$config['SPOT'] . 'userName']);
		unset($_SESSION[App::$config['SPOT'] . 'userRealName']);
		unset($_SESSION[App::$config['SPOT'] . 'companyId']);
		unset($_SESSION[App::$config['SPOT'] . 'companyName']);
		unset($_SESSION[App::$config['SPOT'] . 'positionName']);
		unset($_SESSION[App::$config['SPOT'] . 'user_access_token']);
		unset($_SESSION[App::$config['SPOT'] . 'login_time']);
		unset($_SESSION[App::$config['SPOT'] . 'sys_list']);
		unset($_SESSION[App::$config['SPOT'] . 'access_list']);
		setcookie($_COOKIE['baimi_ticket'], '', - 36000000);
	}
}
?>