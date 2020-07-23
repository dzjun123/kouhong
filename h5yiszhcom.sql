-- Adminer 4.6.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `wn_rouge_achi`;
CREATE TABLE `wn_rouge_achi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL,
  `top_openid` varchar(50) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `get_price` decimal(18,2) DEFAULT '0.00' COMMENT '返现金额',
  `price` decimal(18,2) DEFAULT '0.00' COMMENT '充值金额',
  `re_ratio` int(11) DEFAULT NULL COMMENT '比例',
  `statu` int(11) DEFAULT '1',
  `info` text,
  `create_time` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_advert`;
CREATE TABLE `wn_rouge_advert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_distr`;
CREATE TABLE `wn_rouge_distr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `re_ratio` int(11) DEFAULT '0' COMMENT '返现比例',
  `img_url` varchar(255) DEFAULT NULL,
  `type` int(11) DEFAULT '1',
  `info` varchar(255) DEFAULT NULL,
  `statu` int(11) DEFAULT '1',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_game`;
CREATE TABLE `wn_rouge_game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `chance` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `ssz_speed` decimal(18,1) DEFAULT NULL,
  `nsz_speed` decimal(18,1) DEFAULT NULL,
  `init_number` int(11) DEFAULT NULL,
  `oper_number` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_getmoney`;
CREATE TABLE `wn_rouge_getmoney` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL,
  `orderfk` varchar(50) DEFAULT NULL COMMENT '实际付款订单号',
  `openid` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `statu` int(11) DEFAULT '1',
  `info` text,
  `create_time` int(11) DEFAULT NULL,
  `wxinfo` text,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_goods`;
CREATE TABLE `wn_rouge_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL COMMENT '品牌',
  `title` varchar(50) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `ori_price` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `goodsinfo` text,
  `imginfo` text,
  `hard` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_login_user`;
CREATE TABLE `wn_rouge_login_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` int(1) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `sex` int(1) DEFAULT '0',
  `address` varchar(255) DEFAULT NULL,
  `statu` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `lock` int(1) DEFAULT '0',
  `login_time` int(11) DEFAULT NULL,
  `login_ip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `wn_rouge_login_user` (`id`, `uniacid`, `username`, `password`, `level`, `name`, `phone`, `nickname`, `sex`, `address`, `statu`, `create_time`, `update_time`, `lock`, `login_time`, `login_ip`) VALUES
(1,	NULL,	'admin',	'25f9e794323b453885f5181f1b624d0b',	1,	'',	NULL,	NULL,	0,	NULL,	NULL,	1541398089,	NULL,	0,	NULL,	NULL);

DROP TABLE IF EXISTS `wn_rouge_order`;
CREATE TABLE `wn_rouge_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL,
  `from_id` varchar(20) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  `prize_id` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `courier_unit` varchar(50) DEFAULT NULL,
  `courier_no` varchar(50) DEFAULT NULL,
  `send_info` varchar(255) DEFAULT NULL,
  `put_time` int(11) DEFAULT NULL,
  `statu` int(11) DEFAULT '1',
  `info` text,
  `addtime` varchar(20) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_paylog`;
CREATE TABLE `wn_rouge_paylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `orderid` varchar(50) DEFAULT NULL,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `real_fee` decimal(18,2) DEFAULT NULL,
  `total_fee` decimal(18,2) DEFAULT NULL,
  `pay_info` text,
  `pay_statu` int(1) DEFAULT '0',
  `order_statu` int(1) DEFAULT NULL,
  `tz_zt` int(1) DEFAULT '0',
  `pay_fs` int(1) DEFAULT NULL,
  `orderno` varchar(50) DEFAULT NULL,
  `payinfo` text,
  `addtime` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `pay_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_plat`;
CREATE TABLE `wn_rouge_plat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contacts` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `statu` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_rule`;
CREATE TABLE `wn_rouge_rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_sendmsg_log`;
CREATE TABLE `wn_rouge_sendmsg_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `orderid` int(11) DEFAULT NULL,
  `data` text,
  `res` text,
  `addtime` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_share`;
CREATE TABLE `wn_rouge_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT '0' COMMENT '每天几次',
  `price` decimal(18,2) DEFAULT '0.00' COMMENT '每次金额',
  `statu` int(11) DEFAULT '1',
  `create_time` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_share_log`;
CREATE TABLE `wn_rouge_share_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `num` int(11) DEFAULT '0' COMMENT '每天几次',
  `price` decimal(18,2) DEFAULT '0.00' COMMENT '每次金额',
  `statu` int(11) DEFAULT '1',
  `create_time` int(11) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_system`;
CREATE TABLE `wn_rouge_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `chance` int(11) DEFAULT '1',
  `title` varchar(50) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL COMMENT '首图',
  `loading_url` varchar(255) DEFAULT NULL,
  `qrcode_url` varchar(255) DEFAULT NULL,
  `music_url` varchar(255) DEFAULT NULL COMMENT '音乐链接',
  `explain_title` varchar(50) DEFAULT NULL,
  `explain_info` varchar(255) DEFAULT NULL COMMENT '说明',
  `notice_title` varchar(50) DEFAULT NULL,
  `notice_info` varchar(255) DEFAULT NULL COMMENT '系统公告',
  `is_notice` int(1) DEFAULT '0',
  `one_init` int(11) DEFAULT '0' COMMENT '第一关初始数量',
  `one_init_s` int(11) DEFAULT '30' COMMENT '第一关初始时间',
  `two_init` int(11) DEFAULT '0' COMMENT '第二关初始数量',
  `two_init_s` int(11) DEFAULT '40' COMMENT '第二关初始时间',
  `three_init` int(11) DEFAULT '2' COMMENT '第三关初始数量',
  `three_init_s` int(11) DEFAULT '45' COMMENT '第三关初始时间',
  `statu` int(11) DEFAULT '0',
  `addtime` varchar(20) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_system_wx`;
CREATE TABLE `wn_rouge_system_wx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `appid` varchar(50) DEFAULT NULL,
  `appsecret` varchar(255) DEFAULT NULL,
  `token` varchar(50) DEFAULT NULL,
  `encodingaeskey` varchar(255) DEFAULT NULL,
  `gh_id` varchar(255) DEFAULT NULL,
  `mch_id` varchar(20) DEFAULT NULL,
  `partnerkey` varchar(100) DEFAULT NULL,
  `tpl_msg_id_putgoods` varchar(50) DEFAULT NULL COMMENT '发货模板消息',
  `statu` int(11) DEFAULT NULL,
  `accesstoken` varchar(255) DEFAULT NULL,
  `expires_in` int(11) DEFAULT '0',
  `gx_time` int(11) DEFAULT '0',
  `is_get_cash` int(1) DEFAULT '0' COMMENT '发放方式   自动/主动',
  `is_put_mode` int(1) DEFAULT '0' COMMENT '红包/企业付款',
  `addtime` varchar(20) DEFAULT NULL,
  `wishing` varchar(255) DEFAULT NULL,
  `apiclient_cert` varchar(255) DEFAULT NULL,
  `apiclient_key` varchar(255) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_user`;
CREATE TABLE `wn_rouge_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `header_url` varchar(255) DEFAULT NULL,
  `top_openid` varchar(50) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `sex` int(1) DEFAULT '0',
  `address` varchar(255) DEFAULT NULL,
  `balance` decimal(18,2) DEFAULT '0.00',
  `bonus` decimal(18,2) DEFAULT '0.00',
  `y_bonus` decimal(18,2) DEFAULT '0.00',
  `statu` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `distr_url` varchar(255) DEFAULT NULL,
  `distr_img_url` varchar(255) DEFAULT NULL,
  `distr_qrcode_url` varchar(255) DEFAULT NULL,
  `type` int(11) DEFAULT '0',
  `wx_info` varchar(255) DEFAULT NULL,
  `addtime` varchar(20) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_user_consume`;
CREATE TABLE `wn_rouge_user_consume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `addtime` varchar(20) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_user_game_log`;
CREATE TABLE `wn_rouge_user_game_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `level` varchar(255) DEFAULT '0',
  `results` varchar(10) DEFAULT NULL,
  `addtime` varchar(20) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `wn_rouge_user_prize`;
CREATE TABLE `wn_rouge_user_prize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `ori_price` decimal(11,0) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `goodsinfo` text,
  `statu` int(1) DEFAULT '1',
  `update_time` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2018-11-12 08:42:02
