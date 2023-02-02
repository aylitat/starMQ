# StarMQ1.0

> 运行环境要求 PHP8.0(开发环境是 PHP8.0,向下兼容)

## 预览

![](https://cdn.wgbor.cn/uploads/2023/02/02/167532925963db7eebdb8c9.png)

## 安装教程

1. 下载程序
2. 上传程序至服务器
3. 导入 star.sql 至数据库
4. 修改程序`config/database.php`文件的数据库配置信息（如下图）
   ![](https://cdn.wgbor.cn/uploads/2023/02/02/167532567063db70e6d4724.png)
5. 访问域名即可！

## Tips

- 默认账号密码为`admin` `123456`
- 项目运行目录选择 `public`
- 伪静态设置为`ThinkPHP`

```
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
```

- APP 监控软件为根目录得到 StarMQ.apk

## 捐赠

<center>
<img src="https://cdn.wgbor.cn/uploads/2023/02/02/167532711063db7686a4ac7.jpg" width="30%">
<img src="https://cdn.wgbor.cn/uploads/2023/02/02/167532713363db769d3831c.jpg" width="30%">
</center>

## 问题反馈

- 邮箱： `i@kain8.cn`
- QQ：1361582519
