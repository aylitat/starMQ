<?php

namespace app\controller;

use star\Epay;
use star\Http;
use think\facade\View;
use think\facade\Db;

class Index extends Base
{


    public function index()
    {
        return View::fetch();
    }


    //获取监控端状态
    public function getState()
    {
        $key = Db::name("setting")->where("key", "key")->find()['val'];
        $t = input("t");
        $_sign = $t . $key;
        if (md5($_sign) != input("sign")) {
            return json(["code" => -1, "msg" => "签名校验不通过", "data" => null]);
        }
        $lastheart = Db::name("setting")->where("key", "lastheart")->find()['val'];
        $lastpay = Db::name("setting")->where("key", "lastpay")->find()['val'];
        $jkstate = Db::name("setting")->where("key", "jkstate")->find()['val'];
        return json(["code" => 1, "msg" => "成功", "data" => ["lastheart" => $lastheart, "lastpay" => $lastpay, "jkstate" => $jkstate]]);
    }


    //App心跳接口
    public function appHeart()
    {
        $this->closeEndOrder();

        $key = Db::name("setting")->where("key", "key")->find()['val'];
        $t = input("t");
        $_sign = $t . $key;
        if (md5($_sign) != input("sign")) {
            return json(["code" => -1, "msg" => "签名校验不通过", "data" => null]);
        }
        Db::name("setting")->where("key", "lastheart")->update(["val" => time()]);
        Db::name("setting")->where("key", "jkstate")->update(["val" => 1]);
        return json(["code" => 1, "msg" => "成功", "data" => null]);
    }


    //App推送付款数据接口
    public function appPush()
    {
        $this->closeEndOrder();

        $key = Db::name("setting")->where("key", "key")->find()['val'];
        $t = input("t");
        $type = input("type");
        $price = input("price");
        $_sign = $type . $price . $t . $key;
        if (md5($_sign) != input("sign")) {
            return json(["code" => -1, "msg" => "签名校验不通过", "data" => null]);
        }
        Db::name("setting")
            ->where("key", "lastpay")
            ->update([
                "val" => time()
            ]);

        $res = Db::name("order")
            ->where("really_price", $price)
            ->where("state", 0)
            ->where("type", $type)
            ->find();


        if ($res) {
            Db::name("price")
                ->where("oid", $res['order_id'])
                ->delete();
            Db::name("order")->where("id", $res['id'])->update([
                "state" => 1,
                "pay_date" => time(),
                "close_date" => time()
            ]);
            $url = $res['notify_url'];
            $key = Db::name("setting")->where("key", "key")->find()['val'];
            $u = $this->create_call($res,$key);
            $re = Http::get($u['notify']);
            if ($re == "success") {
                return json(["code" => 1, "msg" => "成功", "data" => null]);
            } else {
                Db::name("order")->where("id", $res['id'])->update(["state" => 0]);
                return json(["code" => 1, "msg" => "异步通知失败", "data" => null]);
            }
        } else {
            $data = [
                "close_date" => 0,
                "create_date" => time(),
                "is_auto" => 0,
                "notify_url" => "",
                "order_id" => "无订单转账",
                "param" => "无订单转账",
                "pay_date" => 0,
                "pay_id" => "无订单转账",
                "pay_url" => "",
                "price" => $price,
                "really_price" => $price,
                "return_url" => "",
                "state" => 1,
                "type" => $type
            ];
            Db::name("order")->insert($data);
            return json(["code" => 1, "msg" => "成功", "data" => null]);
        }
    }

}
