<?php

namespace app\controller;


use app\middleware\Check;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Request;

class User extends Base
{
    protected $middleware = [
        Check::class => []
    ];

    public function index()
    {
        View::assign(
            $this->console()
        );
        return View::fetch();
    }

    public function set()
    {

        return View::fetch();
    }

    public function jk()
    {
        return View::fetch();
    }

    public function code()
    {
        return View::fetch();
    }

    public function order()
    {
        return View::fetch();
    }

    //公共头部
    public function header()
    {

        return View::fetch();
    }

    public function update(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        $user = $request->post("user");
        $pass = $request->post("pass");
        $data = Db::name('user')->where('id',1)->find();
        $salt = $data["salt"];
        $res = Db::name('user')->where('id',1)->update([
            "user" => $user,
            "pass" => md5($salt . $pass)
        ]);
        if ($res){
            return json(["code" => 200, "msg" => "修改成功!", "data" => null]);
        }else{
            return json(["code" => 201, "msg" => "修改失败!", "data" => null]);
        }
    }

    //登录
    public function login(Request $request)
    {
        if (!$request->isPost()) {
            if (Session::has("Admin_Login")) {
                return redirect("/User");
            }
            return View::fetch();
        } else if ($request->isPost()) {
            $username = $request->post("username");
            $password = $request->post("password");
            try {
                $result = validate(\app\validate\User::class)->check([
                    "username" => $username,
                    "password" => $password
                ]);
                if ($result != true) {
                    return json(["code" => 201, "msg" => $result]);
                }
            } catch (ValidateException $e) {
                return json(["code" => 201, "msg" => $e->getMessage()]);
            }
            $user = \app\model\User::where('user', $username)->find();
            if (!$user) {
                return json(["code" => 201, "msg" => "账号或密码错误!"]);
            }
            if (md5($user["salt"] . $password) != $user["pass"]) {
                return json(["code" => 201, "msg" => "账号或密码错误!"]);
            }
            Session::set("Admin_Login", md5($user));
            Session::set("user", $username);
            return json(["code" => 200, "msg" => "登录成功!"]);
        }
    }

    public function logout(Request $request)
    {
        if (!$request->isPost()) {
            return redirect("/User/index");
        }
        Session::delete("Admin_Login");
        Session::delete("user");
        return json(["code" => 200, "msg" => "登出成功!"]);
    }
}
