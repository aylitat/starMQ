<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';
define("APP_VERSION", "V1.0.0");
// 执行HTTP应用并响应
$app = new App();
$http = $app->http;
// 检测程序安装
//if(!is_file(__DIR__ . '/install.lock')){
//    $response = $http->name('install')->run();
//}
$app->route->rule('','/Pay/submit');
$response = $http->run();

$response->send();

$http->end($response);
