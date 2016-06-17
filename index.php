<?php
@date_default_timezone_set('PRC'); /* 设置服务器的时间为北京时间 */
// 获取请求头部信息，兼容nginx
if (!function_exists('getallheaders')) {
    function getallheaders(){
        $headers = '';
        foreach($_SERVER as $name => $value){
            if (substr($name, 0, 5) == 'HTTP_'){
                $headers[(str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))))] = $value;
            }
        }
        return $headers;
    }
}
$config['CORE_PATH']=dirname(__FILE__);
include $config['CORE_PATH'] . '/config.php';
include $config['CORE_PATH'] . '/const.inc.php';
require $config['CORE_PATH'] . '/system/core/App.class.php';
$app = new App($config);unset($config);
$app->run();