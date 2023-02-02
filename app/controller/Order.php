<?php

namespace app\controller;


use app\middleware\Check;
use star\Http;
use think\facade\Db;
use think\Request;

class Order extends Base
{

    protected $middleware = [
        Check::class => []
    ];
    public function budan(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        $id = $request->post("id");
        $order = Db::name('order')->where('id', $id)->find();
        if (empty($order)) {
            return json(['code' => 0, 'msg' => '订单不存在!']);
        }
        $url = $order['notify_url'];
        $url = $url . "?out_trade_no=" . $order['pay_id'] . "&trade_no=" . $order['order_id'] . "&type=" . $order['type'];
        $result = Http::get($url);
        if ($result == "success") {
            if ($order['state'] == 0) {
                Db::name("price")
                    ->where("oid", $order['order_id'])
                    ->delete();
            }
            Db::name("order")->where("id", $order['id'])->update(["state" => 1]);
            return json(["code" => 200, "msg" => "补单成功!"]);
        } else {
            return json(["code" => 201, "msg" => "补单失败!"]);;
        }

    }

    public function del(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        $id = $request->post("id");
        $res = Db::name("order")->delete($id);
        if ($res != 0) {
            return json(["code" => 200, "msg" => "删除成功!"]);
        } else {
            return json(["code" => 201, "msg" => "删除失败!"]);
        }
    }

    public function delorder(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        $res = Db::name("order")->where('state != 1')->delete();
        if ($res != 0) {
            return json(["code" => 200, "msg" => "删除成功!"]);
        } else {
            return json(["code" => 201, "msg" => "删除失败!"]);
        }
    }
}