<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Skylon <skylon@100msh.com>
 * @date		2014-6-30
 * @desc		后台用户管理
 */
class BossApi
{
	private $sys_id;
	public function __construct(  )
	{
		$this->sys_id=SYS_ID;
	}
	
	/**
	 * $desc 根据tiket 值获取登录用户名称
     * @param $ticket 唯一标示
     * @return username 登录用户名
     */
	public function check_ticket($ticket){
		$url=App::$config['boss_auth']."/authenticate/validate?ticket=".$ticket."&sysId=".$this->sys_id;
		$result=$this->getHttpContent($url);
		$result=json_decode($result,true);
		if($result['code']==SUCCESS){
			return $result['data']['username'];
		}
		return "";
	}
	/**
	 * $desc 根据$username 值获取登录用户信息已经权限信息
     * @param $$username  唯一标示
     * @return username 登录用户名t
     */
	public function get_userinfo($username){
		$url=App::$config['boss_auth']."/user/getinfo?";
		$post_data = array ("username" => $username,"sysId" => $this->sys_id);
		$results=$this->getHttpContent($url,'POST',$post_data);
		$results=json_decode($results,true);
		if($results['code']==SUCCESS){
			return $results['data'];
		}
		return "";
	}
	/**
	 * $desc 根据$username 用户退出
     * @param $$username  唯一标示
     * @return username 登录用户名
     */
	public function boss_out($ticket){
		$url=App::$config['boss_auth']."/authenticate/logout?ticket=".$ticket;
		$result=$this->getHttpContent($url,'POST');
		$result=json_decode($result,true);
		if($result['code']==SUCCESS){
			return true;
		}
		return "";
	}
	
	/**
	 * $desc 根据userids 获取用户信息
     * @param $userids  用户IDS
     * @return usernameS 用户信息
     */
	public function boss_getUserNames($userids){
		$url=App::$config['boss_auth']."/user/getByIds?userIds=".$userids;
		$results=$this->getHttpContent($url);
		$results=json_decode($results,true);
		if($results['code']==SUCCESS){
			return $results['data'];
		}
		return "";
	}
	
    /** 
	 * @author       skylon
	 * @param        $url 
	 * @param string $method 
	 * @param array  $postData 
	 * 
	 * @return mixed|null|string 
	 */  
	function getHttpContent($url, $method = 'GET', $postData = array())  
	{  
	    $data = '';  
	    if (!empty($url)) {  
	        try {  
	            $ch = curl_init();  
	            curl_setopt($ch, CURLOPT_URL, $url);  
	            curl_setopt($ch, CURLOPT_HEADER, false);  
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	            curl_setopt($ch, CURLOPT_TIMEOUT, 60); //30秒超时  
	            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
	            //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);  
	            if (strtoupper($method) == 'POST') {  
	                $curlPost = is_array($postData) ? http_build_query($postData) : $postData;  
	                curl_setopt($ch, CURLOPT_POST, 1);  
	                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);  
	            }  
	            $data = curl_exec($ch);  
	            curl_close($ch);  
	        } catch (Exception $e) {  
	            $data = null;  
	        }  
	    }  
	    return $data;  
	}  
//   
//    
    public  function test_array(){
    	$result['data']['user']=array(
    								'userId'=>'1',
    								'userName'=>'huanglifeng',
    								'userRealName'=>'黄丽锋',
    								'companyId'=>'1',
    								'companyName'=>'深圳总部',
    								'positionName'=>'管理员',
    							);
    	$result['data']['sysRights']['0']=array('sysId'=>1,'sysName'=>'boss系统','img'=>'http://d.100m.net/100msh_upload/access_img/c6b102c7f3fde270c3e719578ad3e49b.png');
    	$result['data']['sysRights']['1']=array('sysId'=>2,'sysName'=>'米控系统','img'=>'http://d.100m.net/100msh_upload/access_img/c6b102c7f3fde270c3e719578ad3e49b.png');
    	
    	$result['data']['rights']['0']=array('accessName'=>'百米官网','accessId'=>'90','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['0']['children']['0']=array('accessName'=>'首页图片','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['0']['children']['1']=array('accessName'=>'文章管理','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['0']['children']['0']['children']['0']=array('accessName'=>'图片列表','accessId'=>'125','accessLink'=>'test/index','accessImg'=>'');
    	$result['data']['rights']['0']['children']['0']['children']['1']=array('accessName'=>'图片分类','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['0']['children']['1']['children']['0']=array('accessName'=>'文章列表','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['0']['children']['1']['children']['1']=array('accessName'=>'文章分类','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	
    	
    	$result['data']['rights']['1']=array('accessName'=>'百米导航','accessId'=>'90','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['1']['children']['0']=array('accessName'=>'首页图片','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	$result['data']['rights']['1']['children']['0']['children']['0']=array('accessName'=>'图片分类','accessId'=>'125','accessLink'=>'','accessImg'=>'');
    	
    	return $result['data'];
    }
    
	
}