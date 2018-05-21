<?php
//根目录
!defined('BASE_PATH') && define('BASE_PATH', str_replace('\\', '/', realpath(dirname(dirname(dirname(__FILE__))) . '/')) . '/');

//系统目录
!defined('SYS_PATH') && define('SYS_PATH', str_replace('\\', '/', realpath(dirname(dirname(__FILE__)) . '/')) . '/');

//网站WEB目录
!defined('WEB_PATH') && define('WEB_PATH', BASE_PATH);

//应用目录
!defined('APP_PATH') && define('APP_PATH', BASE_PATH . 'src/');

//设置时区
!defined('TIME_ZONE') && define('TIME_ZONE', 'Asia/Shanghai');

//版本号
define('VERSION', '0.1.0');

//发布日期
define('RELEASE', '160803');

//内部版本号
define('UNFRAMED_VERSION', '0.1.0.0');
