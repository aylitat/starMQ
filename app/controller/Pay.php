<?php

namespace app\controller;

use star\Epay;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Pay extends Base
{

    public function submit()
    {
        $this->closeEndOrder();
        $key = Db::name("setting")->where('key', "key")->find()['val'];
        $data = Request::param('', '', 'strip_tags');
        if (empty($data['pid'])) {
            View::assign('error_tips', "PID不可为空");
            return View::fetch();
        }
        if (empty($data['out_trade_no'])) {
            View::assign('error_tips', "订单号不可为空");
            return View::fetch();
        }
        if (empty($data['type'])) {
            View::assign('error_tips', "支付类型不可为空");
            return View::fetch();
        }
        if ($data['notify_url']) {
            $notify_url = $data["notify_url"];
        } else {
            $res = Db::name("setting")->where("key", "notifyUrl")->find();
            $notify_url = $res['val'];
        }
        if ($data['return_url']) {
            $return_url = $data["return_url"];
        } else {
            $res = Db::name("setting")->where("key", "returnUrl")->find();
            $return_url = $res['val'];
        }
        if (empty($data['name'])) {
            View::assign('error_tips', "商品名称不可为空");
            return View::fetch();
        }
        if (empty($data['money'])) {
            View::assign('error_tips', "金额不可为空");
            return View::fetch();
        }
        if ($data['pid'] != 1000) {
            View::assign('error_tips', "商户不存在");
            return View::fetch();
        }
        if ($data['money'] <= 0) {
            View::assign('error_tips', "金额错误");
            return View::fetch();
        }
        $epay = new Epay();
        $isSign = $epay->getEpaySignVeryfy($data, $data["sign"], $key); //生成签名结果
        if (!$isSign) {
            View::assign('error_tips', "验签失败,请检查PID或者Key是否正确");
            return View::fetch();
        }
        $is_orderNo = Db::name('order')->where('pay_id', $data['out_trade_no'])->find();
        if ($is_orderNo && $is_orderNo['account_id'] != 0) {
            View::assign('error_tips', "订单号重复,请重新发起");
            return View::fetch();
        }

        $jkstate = Db::name("setting")->where("key", "jkstate")->find()['val'];
        if ($jkstate != "1") {
            View::assign('error_tips', "监控端状态异常，请检查");
            return View::fetch();
        }
        $reallyPrice = bcmul($data["money"], 100);

        $payQf = Db::name("setting")->where("key", "payQf")->find()['val'];
        $orderId = "StarMQ-Pay" . date("YmdHms") . rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9);
        $ok = false;
        for ($i = 0; $i < 10; $i++) {
            $tmpPrice = $reallyPrice . "-" . $data["type"];

            $row = Db::execute("INSERT IGNORE INTO star_price (price,oid) VALUES ('" . $tmpPrice . "','" . $orderId . "')");
            if ($row) {
                $ok = true;
                break;
            }
            if ($payQf == 1) {
                $reallyPrice++;
            } else if ($payQf == 2) {
                $reallyPrice--;
            }
        }
        if (!$ok) {
            return json($this->getReturn(-1, "订单超出负荷，请稍后重试"));
        }

        $reallyPrice = bcdiv($reallyPrice, 100, 2);

        if ($data["type"] == "wxpay") {
            $payUrl = Db::name("setting")->where("key", "wxpay")->find()['val'];
        } else if ($data["type"] == "alipay") {
            $payUrl = Db::name("setting")->where("key", "zfbpay")->find()['val'];
        }
        if ($payUrl == "") {
            View::assign('error_tips', "请您先进入后台配置程序");
            return View::fetch();
        }
        $isAuto = 1;
        $_payUrl = Db::name("qrcode")
            ->where("price", $reallyPrice)
            ->where("type", $data["type"])
            ->find();
        if ($_payUrl) {
            $payUrl = $_payUrl['pay_url'];
            $isAuto = 0;
        }


        $createDate = time();
        $db = [
            "close_date" => 0,
            "create_date" => $createDate,
            "is_auto" => $isAuto,
            "notify_url" => $notify_url,
            "order_id" => $orderId,
            "param" => "",
            "pay_date" => 0,
            "pay_id" => $data["out_trade_no"],
            "pay_url" => $payUrl,
            "price" => $data["money"],
            "really_price" => $reallyPrice,
            "return_url" => $return_url,
            "state" => 0,
            "name" => $data["name"],
            "type" => $data["type"]

        ];
        Db::name("order")->insert($db);
        exit("<script>window.location.href='/Pay/console?orderId={$orderId}';</script>");
    }

    public function console($orderId = '')
    {
        if (Request::isPost()) {
            $data = Request::param('', '', 'strip_tags');
            $orderId = $data['orderId'];

            if (empty($orderId)) {
                return json(['code' => 0, 'msg' => '订单号为空!']);
            }
            $res = Db::name('order')->where('order_id', $orderId)->find();
            if (empty($res)) {
                return json(['code' => 0, 'msg' => '订单不存在!']);
            }
            if ($res['state'] == -1) {
                return json(['code' => 0, 'msg' => '订单已过期!']);
            }
            if ($res['state'] == 0) {
                return json(['code' => 0, 'msg' => '获取二维码成功!']);
            }
            $key = Db::name("setting")->where("key", "key")->find()["val"];

            $res['price'] = number_format($res['price'], 2, ".", "");
            $res['really_price'] = number_format($res['really_price'], 2, ".", "");
            
            $u = $this->create_call($res,$key);
            
            return json(['code' => 200, 'msg' => '订单支付成功!', 'url' => $u['return']]);
        }
        $db = Db::name("order")->where("order_id", $orderId)->find();
        $time = $db["close_date"];
        $type = $db['type'];
        $money = $db['really_price'];
        $pay_url = $db["pay_url"];
        View::assign([
            "order" => $orderId,
            "type" => $type,
            "money" => $money,
            "payurl" => $pay_url,
            "time" => $time
        ]);
        return View::fetch();

    }
    
}