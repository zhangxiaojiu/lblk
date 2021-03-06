<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------

if (file_exists(CMF_ROOT . "data/conf/route.php")) {
    $runtimeRoutes = include CMF_ROOT . "data/conf/route.php";
} else {
    $runtimeRoutes = [];
}
$myRoutes = array
(
	'old' => 'portal/Index/old',
	'share/:id' => 'user/index/index',
    '__domain__'=>[
        'admin.sckemeinuo.com' => 'admin',
	'admin.mylabulaka.com' => 'admin',

        'www.sckemeinuo.com' => 'sckmn',
	'sckemeinuo.com' => 'sckmn',
	'flm.sckemeinuo.com' => 'sckmn/flm',

	
        'www.mylabulaka.com' => 'portal',
        'mylabulaka.com' => 'portal',
        'app.mylabulaka.com' => 'lklrj',
        'app.sckemeinuo.com'      => 'lklrj',
    ],
);
return array_merge($runtimeRoutes,$myRoutes);
