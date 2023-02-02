<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class Base extends BaseController
{
    public function initialize()
    {
        //系统设置
        $set = $this->getSetting();
        //服务器信息
        $info = $this->info();
        //订单列表
        $list = Db::name("order")->order('id', 'desc')->paginate(10);
        // 获取分页显示
        $page = $list->render();
        View::assign([
            "info" => $info, //系统信息
            "user" => Session::get("user"), //用户名。存在Session中
            "list" => $list, //订单列表
            "page" => $page, //分页显示
            "set" => $set, //系统设置
        ]);
    }

    //获取设置信息
    protected function getSetting()
    {
        $notifyUrl = Db::name("setting")->where("key", "notifyUrl")->find();
        $returnUrl = Db::name("setting")->where("key", "returnUrl")->find();
        $key = Db::name("setting")->where("key", "key")->find();
        $lastheart = Db::name("setting")->where("key", "lastheart")->find();
        $lastpay = Db::name("setting")->where("key", "lastpay")->find();
        $jkstate = Db::name("setting")->where("key", "jkstate")->find();
        $close = Db::name("setting")->where("key", "close")->find();
        $payQf = Db::name("setting")->where("key", "payQf")->find();
        $wxpay = Db::name("setting")->where("key", "wxpay")->find();
        $zfbpay = Db::name("setting")->where("key", "zfbpay")->find();
        if ($key['val'] == "") {
            $key['val'] = md5(time());
            Db::name("setting")->where("key", "key")->update([
                "val" => $key['val']
            ]);
        }

        return [
            "notifyUrl" => $notifyUrl['val'],
            "returnUrl" => $returnUrl['val'],
            "key" => $key['val'],
            "lastheart" => $lastheart['val'],
            "lastpay" => $lastpay['val'],
            "jkstate" => $jkstate['val'],
            "close" => $close['val'],
            "payQf" => $payQf['val'],
            "wxpay" => $wxpay['val'],
            "zfbpay" => $zfbpay['val'],
        ];

    }

    //服务器信息
    protected function info()
    {
        //GD库信息
        if (function_exists("gd_info")) {
            $gd_info = @gd_info();
            $gd = $gd_info["GD Version"];
        } else {
            $gd = "GD库未开启!";
        }

        //MySQL版本
        $mysql = Db::query("SELECT VERSION();");
        $mysql = $mysql[0]['VERSION()'];


        //系统信息
        $info = [
            "程序版本" => APP_VERSION,
            "操作系统" => PHP_OS,
            "服务器引擎" => $_SERVER['SERVER_SOFTWARE'],
            "服务域名" => Request::domain(),
            "PHP版本" => PHP_VERSION,
            "MySQL版本" => $mysql,
            "GD库版本" => $gd,
        ];
        return $info;
    }

    //查询控制台数据
    protected function console()
    {
        $today = strtotime(date("Y-m-d"), time());
        //查找创建时间为今日的订单
        $todayOrder = Db::name("order")
            ->where("create_date >=" . $today)
            ->where("create_date <=" . ($today + 86400))
            ->count();
        //查询创建时间为今日并且已支付的订单的price字段相加
        $todayMoney = Db::name("order")
            ->where("create_date >=" . $today)
            ->where("create_date <=" . ($today + 86400))
            ->where("state", ">=", 1)
            ->sum("price");
        //查询订单表的长度
        $countOrder = Db::name("order")->count();
        //查询所有已支付订单的price字段相加
        $countMoney = Db::name("order")
            ->where("state", ">=", 1)
            ->sum("price");
        return [
            "todayorder" => $todayOrder,
            "todaymoney" => $todayMoney,
            "countorder" => $countOrder,
            "countmoney" => $countMoney
        ];
    }


    //关闭过期订单接口(请用定时器至少1分钟调用一次)
    public function closeEndOrder()
    {
        $res = Db::name("setting")->where("key", "lastheart")->find();
        $lastheart = $res['val'];
        if ((time() - $lastheart) > 60) {
            Db::name("setting")->where("key", "jkstate")->update(["val" => 0]);
        }

        $time = Db::name("setting")->where("key", "close")->find();

        $closeTime = time() - 60 * $time['val'];
        $close_date = time();

        $res = Db::name("order")
            ->where("create_date <=" . $closeTime)
            ->where("state", 0)
            ->update(array("state" => -1, "close_date" => $close_date));

        if ($res) {
            $rows = Db::name("order")->where("close_date", $close_date)->select();
            foreach ($rows as $row) {
                Db::name("price")
                    ->where("oid", $row['order_id'])
                    ->delete();
            }

            $rows = Db::name("price")->select();
            foreach ($rows as $row) {
                $re = Db::name("order")->where("order_id", $row['oid'])->find();
                if ($re) {

                } else {
                    Db::name("price")
                        ->where("oid", $row['oid'])
                        ->delete();
                }
            }
            return json(["code" => "200", "msg" => "成功清理{$res}条订单"]);
        } else {
            return json(["code" => "200", "msg" => "没有等待清理的订单"]);
        }
    }
    
    //支付异步同调方法
        protected function create_call($data,$key){
        $sign = md5("money=".$data['price']."&name=".$data['name']."&out_trade_no=".$data['pay_id']."&pid=1000"."&trade_no=".$data['order_id']."&trade_status=TRADE_SUCCESS&type=".$data['type'].$key);
        $array=array('pid'=>1000,'trade_no'=>$data['order_id'],'out_trade_no'=>$data['pay_id'],'type'=>$data['type'],'name'=>$data['name'],'money'=>$data['price'],'trade_status'=>'TRADE_SUCCESS');
        $urlstr=http_build_query($array);
            if(strpos($data['notify_url'],'?'))
            {
                $url['notify']=$data['notify_url'].'&'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
            }
            else
            {
                $url['notify']=$data['notify_url'].'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
            }
            if(strpos($data['return_url'],'?'))
            {
                $url['return']=$data['return_url'].'&'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
            }
            else
            {
                $url['return']=$data['return_url'].'?'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
            }
		    return $url;
    }
    
    
    
    
}