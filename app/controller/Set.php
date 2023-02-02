<?php

namespace app\controller;

use app\middleware\Check;
use think\cache\driver\Redis;
use think\facade\Db;
use think\Request;

class Set extends Base
{

    protected $middleware = [
        Check::class => []
    ];

    public function data(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        Db::name("setting")->where("key", "notifyUrl")->update(["val" => input("notifyUrl")]);
        Db::name("setting")->where("key", "returnUrl")->update(["val" => input("returnUrl")]);
        Db::name("setting")->where("key", "close")->update(["val" => input("close")]);
        Db::name("setting")->where("key", "payQf")->update(["val" => input("payQf")]);

        return json(["code" => 200, "msg" => "提交成功!"]);
    }

    public function pay(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        Db::name("setting")->where("key", "wxpay")->update(["val" => input("wxpay")]);
        Db::name("setting")->where("key", "zfbpay")->update(["val" => input("zfbpay")]);

        return json(["code" => 200, "msg" => "提交成功!"]);
    }

    public function rekey(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        $res = Db::name("setting")->where("key", "key")->update(["val" => strtoupper(md5(time()))]);
        if ($res) {
            return json(["code" => 200, "msg" => "重置成功!"]);
        } else {
            return json(["code" => 201, "msg" => "重置失败!"]);
        }
    }
}
