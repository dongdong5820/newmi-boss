<?php

/* * **********************数据表 start **************************** */
	/**
	 * 首页轮播图
	 */
	const T_HOME_IMG = "home_img";
	const T_HOME_IMG_DESC = "home_img_desc";

	/**
	 * 文章管理
	 */
	const T_NEWS = "news";//文章表
	const T_NEWS_CATE="news_cate";//文章分类表
	const T_NEWS_CONTENT="news_content";//文章内容表

	/**
	 * 申请WiFi
	 */
	const T_APPLY_WIFI="apply_wifi";       //申请WiFi
	const T_APPLYWIFI_CITY = "applywifi_city"; //微信申请wifi覆盖城市

	/**
	 * 职位管理
	 */
	const T_JOB_TYPE = "job_type";//职位分类表
	const T_JOB = "job";//职位详细表

	/**
	 * 新闻管理
	 */
	const T_ARTICLE_TYPES='article_types';
	const T_ARTICLE_CITIES='article_cities';
	const T_ARTICLE_CONTENT='article_content';
	const T_ARTICLE_ATTACHEMENTS='article_attachments';
	const T_ARTICLES='articles';

	/**
	 * 导航跳转
	 */
	const T_NAV = 'nav';
	const T_NAV_CITY_REL = 'nav_city_rel';

	/**
	 * 操作日志
	 */
	const T_LOG_CATE           = 'log_cate';
	const T_LOG_OPER_TYPE      = 'log_oper_type';
	const T_LOG_TIME_PARTITION = 'log_time_partition';

	/**
	 * 其他
	 */
	const TBL_PARTNER_CATEGORY = 'anl_partner_category';// 合作商户分类
	const T_SITE_NODE_DEPLOY = 'site_node_deploy';//获取百万点城市
	const TBL_STATECITY='anl_statecity';	//地区表
	const TBL_AREA='anl_area';		//商圈表

/* * **********************数据表 end **************************** */


/* 数据库全局配置 start */
$config['DB_TYPE'] = 'mysql';				//数据库类型
$config['DB_PORT'] = 3306;			   	//数据库端口，mysql默认是3306，
$config['DB_CHARSET'] = 'utf8';			 //数据库编码，
$config['DB_PREFIX'] = 't_';			 	//数据库表前缀
$config['DB_PCONNECT'] = FALSE;				//TRUE表示使用永久连接，FALSE表示不适用永久连接，一般不使用永久连接

$config['DB_CACHE_ON'] = FALSE;			  			//是否开启数据库缓存，TRUE开启，FALSE不开启
$config['DB_CACHE_PATH'] = './data/cache/db_cache/'; 	//数据库查询内容缓存目录，地址相对于入口文件，
$config['DB_CACHE_TIME'] = 600;							//缓存时间,0不缓存，-1永久缓存
$config['DB_CACHE_CHECK'] = TRUE;			   		//是否对缓存进行校验，
$config['DB_CACHE_FILE'] = 'cachedata';					//缓存的数据文件名
$config['DB_CACHE_SIZE'] = '15M';			  			//预设的缓存大小，最小为10M，最大为1G
$config['DB_CACHE_FLOCK'] = TRUE;			   		//是否存在文件锁，设置为FALSE，将模拟文件锁，
/* 数据库全局配置 end */

$db_cfg = array(
	//ERP
	'ERP'=>array(
		'DB_HOST'=>'db.inc.100msh.com',	  	//数据库主机
		'DB_USER'=>'100msh_erp',	//数据库用户名
		'DB_PWD' =>'123456',	  		//数据库密码
		'DB_NAME'=>'100msh_erp',	//数据库名
	    'DB_PORT'=>3306
	),
	//操作日志数据库链接
	'LOG' => array(
		'DB_HOST' => 'db.inc.100msh.com',      	//数据库主机
		'DB_USER' => '100msh_erp_log',  //数据库用户名
		'DB_PWD'  => '123456',      	//数据库密码
		'DB_NAME' => '100msh_erp_log',  //数据库名
		'DB_PORT'=>3306
	),
	'ADM'=>array(
		'DB_HOST'=>'db.inc.100msh.com',	  	//数据库主机
		'DB_USER'=>'100msh_admin',	  //数据库用户名
		'DB_PWD' =>'123456',	  		//数据库密码
		'DB_NAME'=>'100msh_admin',	  //数据库名
		'DB_PORT'=>3307
	),
	//商户管理数据库链接
	'PARTNER'=>array(
		'DB_HOST'=>'db.inc.100msh.com',      	//数据库主机
		'DB_USER'=>'100msh_partner',   //数据库用户名
		'DB_PWD'=>'123456',      	    //数据库密码
		'DB_NAME'=>'100msh_partner',    //数据库名
		'DB_PORT'=>3308
	),
	//百万点数据库链接
	'MOP' => array(
		'DB_HOST' => 'db.inc.100msh.com', //数据库主机
		'DB_USER' => '100msh_mop', //数据库用户名
		'DB_PWD' => '123456', //数据库密码
		'DB_NAME' => '100msh_mop', //数据库名
		'DB_PORT'=>3309
	),
);
