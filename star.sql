SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `star`
--

-- --------------------------------------------------------

--
-- 表的结构 `star_order`
--

CREATE TABLE `star_order` (
  `id` bigint(20) NOT NULL COMMENT 'ID',
  `close_date` bigint(20) NOT NULL COMMENT '关闭时间',
  `create_date` bigint(20) NOT NULL COMMENT '创建时间',
  `is_auto` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '商品名',
  `notify_url` varchar(255) DEFAULT NULL COMMENT '异步地址',
  `order_id` varchar(255) DEFAULT NULL COMMENT '订单号',
  `param` varchar(255) DEFAULT NULL COMMENT '参数',
  `pay_date` bigint(20) NOT NULL COMMENT '支付时间',
  `pay_id` varchar(255) DEFAULT NULL COMMENT '支付id',
  `pay_url` varchar(255) DEFAULT NULL COMMENT '支付地址',
  `price` decimal(19,2) NOT NULL COMMENT '金额',
  `really_price` decimal(19,2) NOT NULL COMMENT '实付金额',
  `return_url` varchar(255) DEFAULT NULL COMMENT '回调地址',
  `state` int(11) NOT NULL COMMENT '状态',
  `type` char(10) NOT NULL COMMENT '支付类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表';

-- --------------------------------------------------------

--
-- 表的结构 `star_price`
--

CREATE TABLE `star_price` (
  `price` varchar(255) NOT NULL COMMENT '金额',
  `oid` varchar(255) NOT NULL COMMENT 'oid'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='临时表';

-- --------------------------------------------------------

--
-- 表的结构 `star_qrcode`
--

CREATE TABLE `star_qrcode` (
  `id` bigint(20) NOT NULL COMMENT 'ID',
  `pay_url` varchar(255) DEFAULT NULL COMMENT '支付地址',
  `price` double NOT NULL COMMENT '金额',
  `type` int(11) NOT NULL COMMENT '支付方式'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `star_setting`
--

CREATE TABLE `star_setting` (
  `key` varchar(255) NOT NULL COMMENT '键',
  `val` varchar(255) DEFAULT NULL COMMENT '值'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='设置表';

--
-- 转存表中的数据 `star_setting`
--

INSERT INTO `star_setting` (`key`, `val`) VALUES
('close', '3'),
('jkstate', '0'),
('key', '62D7D0925BEFEAE8244D16E97D96F8CE'),
('lastheart', '0'),
('lastpay', '0'),
('notifyUrl', NULL),
('payQf', '1'),
('returnUrl', ''),
('wxpay', 'wxp://f2f0JvkZUxcgYDMhEwJNS1aPRde-mXFPx9mnMphyxMCtE4NkQum5KR-SeuFJ5TRWVM4W'),
('zfbpay', 'https://qr.alipay.com/fkx147655ykw2iocdmjx65c');

-- --------------------------------------------------------

--
-- 表的结构 `star_user`
--

CREATE TABLE `star_user` (
  `id` int(10) NOT NULL COMMENT 'ID',
  `user` varchar(30) NOT NULL COMMENT '用户名',
  `salt` varchar(50) NOT NULL COMMENT '盐',
  `pass` varchar(50) NOT NULL COMMENT '密码'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

--
-- 转存表中的数据 `star_user`
--

INSERT INTO `star_user` (`id`, `user`, `salt`, `pass`) VALUES
(1, 'admin', 'salt-kain-dev-8-star-pay', 'd14572d2a3a4758db29cebf44c867129');

--
-- 转储表的索引
--

--
-- 表的索引 `star_order`
--
ALTER TABLE `star_order`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `star_price`
--
ALTER TABLE `star_price`
  ADD PRIMARY KEY (`price`);

--
-- 表的索引 `star_qrcode`
--
ALTER TABLE `star_qrcode`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `star_setting`
--
ALTER TABLE `star_setting`
  ADD PRIMARY KEY (`key`);

--
-- 表的索引 `star_user`
--
ALTER TABLE `star_user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `star_order`
--
ALTER TABLE `star_order`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `star_user`
--
ALTER TABLE `star_user`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
