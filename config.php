<?php
/*网站信息配置*/
$config['VERSION']                      = '1.0.2014.1020';    //版本号,2014.1020表示发布日期
$config['SITENAME']                     = '';                 // 网站名称
$config['COPYRIGHT']                    = 'Copyright © 2013 100MSH.COM All Rights Reserved 版权所有 粤ICP备13004409号';  //网站版权
$config['SPOT']                         = '100msh_skylon_';        //站点标识
/*网站信息配置结束*/

/*日志和错误调试配置*/
$config['DEBUG']                    = TRUE;	              //是否开启调试模式，TRUE开启，FALSE关闭
$config['LOG_ON']                   = TRUE;               //是否开启出错信息保存到文件，TRUE开启，FALSE不开启
$config['LOG_PATH']                 = './data/log/';      //出错信息存放的目录，出错信息以天为单位存放，
$config['ERROR_URL']                = '';                 //出错信息重定向页面，为空采用默认的出错页面，
$config['ERROR_HANDLE']             = FALSE;              //让smarty接管错误
/*日志和错误调试配置结束*/

/*应用配置*/
//网址配置
$config['URL_REWRITE_ON']           = TRUE;              //是否开启重写，TRUE开启重写,FALSE关闭重写
$config['URL_MODULE_DEPR']          = '/';                //模块分隔符，
$config['URL_ACTION_DEPR']          = '-';                //操作分隔符，
$config['URL_PARAM_DEPR']           = '-';                //参数分隔符，如果改成其他的
$config['URL_HTML_SUFFIX']          = '.html';            //伪静态后缀设置，，例如 .html ，

//模块配置
$config['MODULE_PATH']              = '/module/';        //模块存放目录，
$config['MODULE_SUFFIX']            = 'Mod.class.php';    //模块后缀，
$config['MODULE_DEFAULT']           = 'index';            //默认模块，

		//操作配置
$config['ACTION_DEFAULT']           = 'index';            //默认操作，

//静态页面缓存
$config['HTML_CACHE_ON']            = FALSE;              //是否开启静态页面缓存，TRUE开启.FALSE关闭
$config['HTML_CACHE_PATH']          = './data/cache/html_cache/';//静态页面缓存目录，
$config['HTML_CACHE_SUFFIX']        = '.html';            //静态页面缓存后缀，
$config['HTML_CACHE_RULE']['index']['index'] = 1000;      //缓存时间,单位：秒
/*应用配置结束*/

/*模板配置*/
$config['TPL_PATH']                 = './templates/';     //模板目录，
$config['TPL_SUFFIX']               = '.html';             //模板后缀，
$config['TPL_CACHE_ON']             = FALSE;              //是否开启模板缓存，TRUE开启,FALSE不开启
$config['TPL_CACHE_PATH']           = './cache/tpl_cache/';//模板缓存目录，
$config['TPL_CACHE_SUFFIX']         = '.php';             //模板缓存后缀,
/*模板配置结束*/

/* 公共目录，放图片、CSS、JS */
$config['PUBLIC_PATH']              = 'public';            //公共目录
$config['SYSTEM_PATH']              = 'system';            //公共目录

/* smarty 配置 */
$config['SMARTY_DEBUGGING']         = FALSE;              //是否开启调试模式
$config['SMARTY_CACHING']           = FALSE;              //是否开启缓存
$config['SMARTY_TEMPLATE_DIR']      = './templates/';     //模板目录
$config['SMARTY_CACHE_LIFETIME']    = 30;                 //缓存时间
$config['SMARTY_COMPILE_DIR']       = './data/cache/smarty/compile_dir'; //smarty模板编译文件存放的目录
$config['SMARTY_CACHE_DIR']         = './data/cache/smarty/cache_dir';   //smarty模板缓存文件存放的目录
$config['SMARTY_LEFT_DELIMITER']    = '{{';                              //左定界符
$config['SMARTY_RIGHT_DELIMITER']   = '}}';                              //右定界符


//单点登录认证URL
$config['boss_url'] = 'http://172.16.1.9:9080/boss';
$config['boss_auth'] = 'http://172.16.1.9:9090/boss-cas';


/* 附件上传配置 */
$config['ACCESSORY_FOLDER']      = '/data001/data/sites'.'/100msh_upload/'; // 附件上传路径
$config['ACCESSORY_URL']         = 'http://d.100m.net/100msh_upload/'; // 附件访问路径
$config['ACCESSORY_SIZE']        = '5'; // 附件上传大小
$config['ACCESSORY_MULTI']       = FALSE; // 是否允许上传多个文件
$config['UPLOAD_AUTO']           = FALSE; // 是否自动生成
$config['ACCESSORY_NUM']         = '10'; // 附件上传数
$config['ACCESSORY_TYPE']        = 'jpg,bmp,gif,png,mp3,wma,mp4,3gp,apk'; // 上传文件的后缀名
$config['WATERMARK_SWITCH']      = FALSE; // 水印开关
$config['IMG_WATERMARK_LOGO']    = 'logo.png'; // 水印图片
$config['WATERMARK_PLACE']       = '5';   // 水印位置(1为左下角,2为右下角,3为左上角,4为右上角,默认为右下角)
$config['THUMBNAIL_SWITCH']      = TRUE; // 缩略图开关
$config['THUMB_REMOVE_ORIGIN']   = FALSE; // 是否删除原图(false 不删除，true 删除原图)
$config['THUMBNAIL_MAXWIDTH']    = '200'; // 缩略图最大宽度
$config['THUMBNAIL_MAXHIGHT']    = '140'; // 缩略图最大高度

/*文本编辑器配置*/
//KindEditor编辑器所在路径
$config["kindeditor_path"] = '/home/100msh/www/100msh_erp'."/plugins/KindEditor/";
$config["kindeditor_url"] = 'http://d.100m.net/100msh_erp'."/plugins/KindEditor/";
//KindEditor编辑器上传文件所在路径
$config["kindeditor_upload_path"] = $config['ACCESSORY_FOLDER'] . "/KEditor/";
$config["kindeditor_upload_url"] = $config['ACCESSORY_URL'] . "/KEditor/";

//百米官网PC预览地址
$config['official_site_url_pc']="http://d.100m.net/100msh_official";
//百米官网手机版预览地址
$config['official_site_url_mobile']='fff';

//预览新闻地址
$config['official_site_url'] = 'http://d.100m.net/100msh_official/news/';

$config['nav_site_url'] = 'http://192.168.10.102:8080/baimiNews/';	 	//百米动态新闻
//预览职位地址
$config['job_site_url'] = 'http://d.100m.net/100msh_official/about/jobdetail';
?>