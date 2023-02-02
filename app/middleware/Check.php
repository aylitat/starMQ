<?php

namespace app\middleware;

use app\middleware\Response;
use think\facade\Session;

class Check
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //判断登录状态
        //如果session不存在 && login不在pathinfo里
        if (!Session::has('Admin_Login') && !preg_match('/login/', $request->pathinfo())) {

            return redirect((string)url("/User/login/"));
        }
        return $next($request);
    }
}
