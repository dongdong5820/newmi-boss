<?php
/**
 * @copyright	©2013-2016 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Skylon <skylon@100msh.com>
 * @date		2016-05-12
 * @desc		后台用户管理
 */
class Admusers
{
	private $bossapi_dao;
	public function __construct(  )
	{
		$this->bossapi_dao=new BossApi();
	}
	/**
	 * @author	skylon
	 * @desc	获取登陆信息
	 * @param	array() $userInfo 用户信息
	 */
	public function getSession(){
		$admuserdata="";
		if(!empty($_SESSION[App::$config['SPOT'] . 'user_access_token']) && !empty($_COOKIE['baimi_ticket']) && $_SESSION[App::$config['SPOT'] . 'user_access_token']==$_COOKIE['baimi_ticket']){//判断子系统会话是否存在 
			$access_token=$_SESSION[App::$config['SPOT'] . 'user_access_token'];
			$login_time=$_SESSION[App::$config['SPOT'] . 'login_time'];
			$current_time = time();
			$access_token_expire = ACCESS_TOKEN_EXPIRE;//access_token失效时间
			if(($current_time - $login_time)<$access_token_expire){//用户登录未超时
				$_SESSION[App::$config['SPOT'] . 'login_time'] = time();
				$admuserdata=array(
					'userId'=>$_SESSION[App::$config['SPOT'] . 'userId'],
					'userName'=>$_SESSION[App::$config['SPOT'] . 'userName'],
					'userRealName'=>$_SESSION[App::$config['SPOT'] . 'userRealName'],
					'companyId'=>$_SESSION[App::$config['SPOT'] . 'companyId'],
					'companyName'=>$_SESSION[App::$config['SPOT'] . 'companyName'],
					'positionName'=>$_SESSION[App::$config['SPOT'] . 'positionName'],
					'login_time'=>$_SESSION[App::$config['SPOT'] . 'login_time'],
				);
			}else{
				$this->bossapi_dao->boss_out();
				U::delSession();
				return "";
			}
		}else{
			if(!empty($_COOKIE['baimi_ticket'])){//判断boss 系统的token唯一值
				$ticket=$_COOKIE['baimi_ticket'];
				$user_name=$this->bossapi_dao->check_ticket($ticket);
				if(!empty($user_name)){
					$user_data=$this->bossapi_dao->get_userinfo($user_name);
					if(!empty($user_data)){
						$admuserdata=$user_data['user'];
						$admuserdata['user_access_token']=$ticket;
						$admuserdata['login_time']=time();
						$sys_list=$user_data['sysRights'];
						$access_list=$user_data['rights'];
						U::setUserSession($admuserdata);
						U::setSysSession($sys_list);
						U::setAccessSession($access_list);
					}else{
						return "";
					}
				}
			}else{
				return "";
			}
		}
		return $admuserdata;	
	}
	/**
	 * @author	skylon
	 * @desc	获取系统列表
	 * @param	array()
	 */
	public function getSysList(){
		$sys_list=$_SESSION[App::$config['SPOT'] . 'sys_list'];
		return unserialize($sys_list);
	}
	/**
	 * @author	skylon
	 * @desc	获取用户信息列表
	 * @param	array()
	 */
	public function getUserList($user_ids){
		$uers_list=$this->bossapi_dao->boss_getUserNames($user_ids);
		$info_list=array();
		if(!empty($uers_list)){
			foreach ($uers_list as $val){
				//$info_list[$val['userId']]=!empty($val['userRealName'])?$val['username']."[".$val['userRealName']."]":$val['username'];
				$info_list[$val['userId']]=array("username"=>$val['username'],"userRealName"=>$val['userRealName']);
			}
		}
		return $info_list;
	}
	/**
	 * @author	skylon
	 * @desc	获取系统权限列表
	 * @param	array()
	 */
	public function getAccessList(){
		$access_list=$_SESSION[App::$config['SPOT'] . 'access_list'];
		return unserialize($access_list);
	}
}