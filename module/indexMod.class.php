<?php
/**
 * @copyright	©2013-2014 百米生活电子商务有限公司 All Rights Reserved
 * @link		http://www.100msh.net
 * ---------------------------------------------------------------------
 * @author		Skylon <skylon@100msh.com>
 * @date		2014-6-30
 * @desc		mop项目主页
 */
class indexMod extends commonMod{
    function index(){
        $this->display();
    }
    function icenter(){
    	$this->assign('user_realname','');
    	$this->display();
    }
	/**
	 * @author	skylon
	 * @desc	退出登陆
	 */
	public function out(){
		$bossapi_dao=new BossApi();
		$res=$bossapi_dao->boss_out($_COOKIE['baimi_ticket']);
		U::delSession();
		$this->redirect(App::$config['boss_url']);
		exit;
	}
}