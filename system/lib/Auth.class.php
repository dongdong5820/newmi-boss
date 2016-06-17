<?php

class Auth{


	public function __construct(  ){
		
	}
	/**
	 * 模块验证[方式1]，验证通过返回true,否则返回false;
	 *
	 * @param unknown_type $arg_list
	 */
	public function authMod($arg_list){
		$module = App::$module;
		$action = App::$action;
		if (empty($module) || empty($action)) {
			return false;
		}		
		$a_param = "";
		if(!empty($arg_list)){
			foreach ($arg_list as $v){
				$a_param .= '-' . $v;
			}
		}
		$auth_key = "#".$module . '/' . $action . $a_param;
		return $this->check_accessrights($auth_key);
	}

	/**
	 * 模块验证[方式2]，验证通过返回true,否则返回false;	
	 *
	 * @param String $auth_key
	 */
	public function authModSpec($auth_key){
		if(empty($auth_key)){
			return false;
		}
		return $this->check_accessrights($auth_key);
	}

	/**
	 * 核对权限，如果核对正确返回true，否则返回false;
	 *
	 * @param String $auth_key
	 */
	private function check_accessrights($auth_key){	
		
		$user_id = U::getUserId();//当前登录用户	
		if(empty($user_id)){
			return false;
		}
		$skey=$auth_key;
		$list_access=array();
		$s_access_list=$_SESSION[App::$config['SPOT'] . 'access_list'];
		if(!empty($s_access_list)){
			$s_access_list=unserialize($s_access_list);
			foreach ($s_access_list as $val){
				$list_access[]=$val['accessLink'];
				if(!empty($val['children'])){
					foreach ($val['children'] as $val1){
						$list_access[]=$val1['accessLink'];
						if(!empty($val1['children'])){
							foreach ($val1['children'] as $val2){
								$list_access[]=$val2['accessLink'];
							}
						}
					}
				}
			}
			if(in_array($skey, $list_access)){
				return TRUE;
			}
		}
		return FALSE;
	}
}