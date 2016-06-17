<?php
/**
 * @Copyright: Copyright © 2013-2014 深圳市百米生活电子商务有限公司 All Rights Reserved
 * @link:  http://www.100msh.net
 * @author tiger/2014-06-01
 * ---------------------------------------------
 * @desc: 公用模块
**/
class commonMod{
    public $tpl;
    public $config;
    public function __construct(){
        if($this->isFirstLoading()) {
            //匹配访问地址，如果是首页则不作为，否则地址改为‘#’加上对应的模块名称
            $siteReg = preg_replace('/(\/)/', '\/', App::$config['__APP__']);
            $siteReg = preg_replace('/(\.)/', '\.', $siteReg);
            $regex = '/' . $siteReg .'/';
            $ref = 'http://'.@$_SERVER['HTTP_HOST'].@$_SERVER["REQUEST_URI"];
            $hash = preg_replace($regex, '', $ref);
            $hash = preg_replace('/^\//', '', $hash);
        }
        session_start();
        if (!class_exists('Smarty', false)) {
            require(App::$config['CORE_PATH'] . '/system/ext/smarty/Smarty.class.php');
            $smarty = new Smarty();
            $smarty->debugging = App::$config['SMARTY_DEBUGGING'];
            $smarty->caching = App::$config['SMARTY_CACHING'];
            $smarty->cache_lifetime = App::$config['SMARTY_CACHE_LIFETIME'];
            $smarty->template_dir = App::$config['SMARTY_TEMPLATE_DIR'];
            $smarty->compile_dir = App::$config['SMARTY_COMPILE_DIR'];
            $smarty->cache_dir = App::$config['SMARTY_CACHE_DIR'];
            $smarty->left_delimiter = App::$config['SMARTY_LEFT_DELIMITER'];
            $smarty->right_delimiter = App::$config['SMARTY_RIGHT_DELIMITER'];
            /* 允许使用PHP strpos函数 */
            //$smarty->security_settings['MODIFIER_FUNCS'] = array('strpos');
            $this->tpl = $smarty;
        }
       	
        /* 当前登录用户信息初始化 */
        
		U::init();
		$this->checkLogin();
		$this->user_id= U::getUserId();

        /* 设置常用的模板变量值 */
        $this->setCommonTplValue();
    }

    /**
     * @desc 判断页面是否首次加载
     * @return {Boolean} 返回true则是首次加载
    **/
    public function isFirstLoading() {
        $headers = getallheaders();
        if(isset($headers['Aq'])){
            return $headers['Aq'] == 1 ? false : true;
        }else {
            return true;
        }
    }

    //模板变量解析
    public function assign($name, $value) {
        return $this->tpl->assign($name, $value);
    }

    //模板数组解析
    public function assignArray($array) {
        return $this->tpl->assign($array);
    }

    //模板输出
    public function display($tpl = '') {
        //实现不加参数时，自动加载相应的模板
        $tpl = empty($tpl) ? App::$module . '/' . App::$action . App::$config['TPL_SUFFIX'] : $tpl;
        $this->tpl->display($tpl);
        exit;
    }
	//模板获取
    public function rfetch($tpl='' ){
    	//实现不加参数时，自动加载相应的模板
    	$tpl = empty( $tpl ) ? APP::$module. '/' . APP::$action . App::$config['TPL_SUFFIX'] : $tpl;
    	return $this->tpl->fetch( $tpl );
    }
    //直接跳转
    protected function redirect($url) {
        header('location:' . $url, FALSE, 301);
        exit;
    }
	//ajax错误操作跳转
    protected function ajax_error($msg) {
       echo "<script>tooltip.tip('error', '$msg');</script>";exit;
    }
    
    /**
     * @name: page
     * @access: protected
     * @param: $url: 基准网址
     * @param: $totalRecords: 一共有几条记录
     * @param: $perPage: 每页显示条数
     * @param: $pageBarNum: 分页栏每页显示的页数
     * @param: $mode: 分页显示的样式
     * @param  $is_ajax ajax 分页，需要ajax 这里传js方法名
     * @param  $ajax_anchor 锚点，要刷新后要跳到的某区域
     * @return: string
     * @desc: 分页
    **/
    protected function page($url, $totalRecords, $perPage = 10, $pageBarNum = 5, $mode = 4,$is_ajax=false,$ajax_anchor='') {
    	$page_ary = '';
        if($is_ajax)$page_ary = array('ajax'=>$is_ajax,'ajax_anchor'=>$ajax_anchor);
        $page = new Page($page_ary);
        return $page->show($url, $totalRecords, $perPage, $pageBarNum, $mode);
    }

    /**
     * @name: setCommonTplValue
     * @access: public
     * @return: void
     * @desc: 设置常用的模板变量值
    **/
    function setCommonTplValue() {
        //在模板中使用的常量,
        $this->assign("__APP__", App::$config['__APP__']);
        $this->assign("__URL__", App::$config['__URL__']);
        $this->assign("__PUBLIC__", App::$config['__PUBLIC__']);
        $this->assign("__ROOT__", App::$config['__ROOT__']);
    }

    /* 检查是否登录 */
    function checkLogin() {
        if (!U::isLogin()) {
            if($this->isFirstLoading()) {
                if (App::$module == 'index') {
                    $this->redirect(App::$config['boss_url']);
                    exit;
                }
            }else {
                echo '<script>location.href="' . App::$config['boss_url'] .'"</script>';exit;
            }
        }
        return TRUE;
    }

    /**
     * @desc 权限验证[方式1],参数个数不受限制
     * @example 调用方式：module与action会自动添加，只需传递特定的参数，如：authMod($_GET[0],$_GET[1]....);
     **/
    function authMod() {
        $auth = new Auth();
        $arg_list = func_get_args();
        $flag = $auth->authMod($arg_list);
        if (!$flag) {
            //验证未通过时处理
    		echo '<script>location.href="' . App::$config['boss_url'] . '"</script>';
            exit;
        }
        return true;
    }
}
?>