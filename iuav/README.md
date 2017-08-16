
```

数据库结构
//后台用户表
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(120) DEFAULT NULL COMMENT '手机号码',
  `authKey` varchar(100) NOT NULL DEFAULT '',
  `accessToken` varchar(100) NOT NULL DEFAULT '',
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `role_id` int(11) DEFAULT '0' COMMENT 'role表的上一级id',  
  `level` tinyint(2) DEFAULT '0',
  `upper_agent_id` int(11) DEFAULT '0' COMMENT 'users代理商表的上一级id',
  `remark` varchar(250) DEFAULT '' COMMENT '备注',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `google_auth` varchar(100) DEFAULT NULL COMMENT '谷歌身份验证器',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


//后台操作权限控制
CREATE TABLE `purview` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
  `description` varchar(100) NOT NULL COMMENT '描述',
  `redirect_url` varchar(250) NOT NULL COMMENT '链接地址', 
  `redirect_name` varchar(250) NOT NULL COMMENT '链接名称', 
  `method`  varchar(250) NOT NULL COMMENT '类名称和方法名', 
  `upper_purview_id` int(11) DEFAULT '0' COMMENT 'purview表的上一级id',  
  `limit` tinyint(2) DEFAULT '0' COMMENT '等级',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//角色表
CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
  `name` varchar(100) NOT NULL COMMENT '名称',
  `sort_order` int(11) DEFAULT '0' COMMENT '角色排序', 
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//角色和对应的路径关系表
CREATE TABLE `role_purview` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
  `role_id` int(11) DEFAULT '0' COMMENT 'role表的上一级id',  
  `purview_id` int(11) DEFAULT '0' COMMENT 'purview表的上一级id',   
  `sort_order` int(11) DEFAULT '0' COMMENT '角色排序', 
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;




//代理商表
CREATE TABLE `agro_agent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `authKey` varchar(100) NOT NULL DEFAULT '',
  `accessToken` varchar(100) NOT NULL DEFAULT '',
  `role` varchar(100) NOT NULL DEFAULT 'user',
  `upper_agent_id` int(11) DEFAULT '0' COMMENT '代理商表的上一级id',
  `agentname` varchar(255) DEFAULT '' COMMENT '代理商名称',
  `code` varchar(50) DEFAULT NULL COMMENT '代理商代号',
  `realname` varchar(255) DEFAULT '' COMMENT '负责人',
  `idcardtype` varchar(255) DEFAULT '' COMMENT '证件类型：身份证 护照 军官证等',  
  `photo`   varchar(150) DEFAULT '' COMMENT '身份证照地址',
  `phone` varchar(120) DEFAULT NULL COMMENT '手机号码',
  `email` varchar(120) DEFAULT NULL COMMENT '邮箱',
  `country` varchar(50) DEFAULT '' COMMENT '国家', 
  `province`  varchar(50) DEFAULT '' COMMENT '省份', 
  `city` varchar(50) DEFAULT '' COMMENT '城市',   
  `address` varchar(255) DEFAULT '' COMMENT ' 邮寄地址',
  `zipcode` varchar(255) DEFAULT '' COMMENT ' 邮编',
  `account` varchar(120) DEFAULT NULL COMMENT 'DJI账号',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid', 
  `staff` varchar(100) DEFAULT NULL COMMENT 'dji负责人员', 
  `status` varchar(100) DEFAULT NULL COMMENT '状态', 
  `is_policies` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否需要购买保险 0:不需要，1：需要',
  `oldcode` varchar(50) DEFAULT NULL COMMENT '历史代理商代号',
  `inside` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否内部账号 0:不是，1：是',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',  
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `account` (`account`),
   KEY `upper_agent_id` (`upper_agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

alter table agro_agent add (`is_policies` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否需要购买保险 0:不需要，1：需要');

alter table `agro_agent` drop column inside;  
alter table agro_agent add (`inside` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否内部账号 0:不是，1：是');

//农业无人机申请表
CREATE TABLE `agro_apply_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) DEFAULT '' COMMENT '当前激活id,用于传给保险公司',
  `agent_id` int(11) DEFAULT '0' COMMENT 'users代理商表的id',
  `upper_agent_id` int(11) DEFAULT '0' COMMENT 'users代理商表的上一级id',
  `user_type` varchar(20) DEFAULT '' COMMENT '用户类别：personal个人，company企业',
  `company_name` varchar(255) DEFAULT '' COMMENT ' 企业名称',
  `company_number` varchar(255) DEFAULT '' COMMENT ' 企业注册号',
  `realname` varchar(255) DEFAULT '' COMMENT '真实用户名',
  `idcardtype` varchar(255) DEFAULT '01' COMMENT '证件类型：01->身份证,03->护照 ,02->往来港澳通行证',
  `idcard` varchar(255) DEFAULT '' COMMENT '证件号码',
  `photo` varchar(150) DEFAULT '' COMMENT '身份证照地址',
  `phone` varchar(120) DEFAULT NULL COMMENT '手机号码',
  `country` varchar(50) DEFAULT '' COMMENT '国家',
  `province` varchar(50) DEFAULT '' COMMENT '省份',
  `city` varchar(50) DEFAULT '' COMMENT '城市',
  `area` varchar(50) DEFAULT '' COMMENT '区',
  `street` varchar(50) DEFAULT '' COMMENT '街道',
  `address` varchar(255) DEFAULT '' COMMENT ' 邮寄地址',
  `zipcode` varchar(255) DEFAULT '' COMMENT ' 邮编',
  `account` varchar(120) DEFAULT NULL COMMENT 'DJI账号',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid',
  `is_mall` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:不邮寄，1：邮寄',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `policies_no` varchar(50) DEFAULT '' COMMENT '保单号',
  `telephone` varchar(120) DEFAULT NULL COMMENT '固定电话',
  `is_policies` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否需要购买保险 0:不需要，1：需要',
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `agent_id` (`agent_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
alter table agro_apply_info add (`is_policies` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:不需要，1：需要');

//农业无人机申请激活设备表
CREATE TABLE `agro_active_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) DEFAULT '' COMMENT '当前激活id,用于传给保险公司',
  `apply_id` int(11) DEFAULT '0' COMMENT '农业无人机申请表',
  `agent_id` int(11) DEFAULT '0' COMMENT 'users代理商表的id',
  `upper_agent_id` int(11) DEFAULT '0' COMMENT 'users代理商表的上一级id',
  `account` varchar(120) DEFAULT NULL COMMENT 'DJI账号',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid',  
  `body_code` varchar(150) DEFAULT NULL COMMENT '机身码',
  `hardware_id` varchar(150) DEFAULT NULL COMMENT '硬件id',
  `activation` varchar(150) DEFAULT NULL COMMENT '激活码',
  `scan_date` varchar(30) DEFAULT NULL COMMENT '扫描日期',
  `type` varchar(20) DEFAULT NULL COMMENT '型号:mg-1;mg-2;mg-3',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `nickname` varchar(100) DEFAULT '' COMMENT '飞机名称',
  `flyer_id` int(11) DEFAULT '0' COMMENT 'flyer表的id',
  `team_id` int(11) DEFAULT '0' COMMENT 'team表的id',
  `locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未锁定,1:锁定',
  `is_notice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通知农机用户平台 0:未，1：已', 
  `locked_notice` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否通知app端 0:未，1：已', 
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `account` (`account`),
   KEY `agent_id` (`agent_id`),
   KEY `body_hardware` (`body_code`,`hardware_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
alter table agro_active_info add (`nickname` varchar(100) DEFAULT '' COMMENT '飞机名称');
alter table agro_active_info add (`flyer_id` int(11) DEFAULT '0' COMMENT 'flyer表的id');
alter table agro_active_info add (`team_id` int(11) DEFAULT '0' COMMENT 'team表的id');
alter table agro_active_info add (`locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未锁定,1:锁定');
alter table agro_active_info add (`is_notice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通知农机用户平台 0:未，1：已经');
alter table agro_active_info add (`locked_notice` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否通知app端 0:未，1：已');


//激活和飞手关系表 
CREATE TABLE `agro_active_flyer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active_id` int(11) DEFAULT '0' COMMENT 'active表的id', 
  `hardware_id` varchar(150) DEFAULT NULL COMMENT '硬件id', 
  `flyer_id` int(11) DEFAULT '0' COMMENT 'flyer表的id',
  `flyer_uid` varchar(120) DEFAULT NULL COMMENT 'flyer表的uid',
  `showed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0', 
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',  
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),  
   KEY `active_id` (`active_id`),
   KEY `flyer_id` (`flyer_id`),
   KEY `body_hardware` (`flyer_uid`,`hardware_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//农业无人机飞手flyer表
CREATE TABLE `agro_flyer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) DEFAULT '0' COMMENT 'agro_team表的id',
  `upper_uid` varchar(120) DEFAULT NULL COMMENT 'agro_active_ext表的上一级uid',
  `account` varchar(120) DEFAULT NULL COMMENT 'DJI账号',
  `nickname` varchar(250) DEFAULT NULL COMMENT '昵称',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid',   
  `realname` varchar(255) DEFAULT '' COMMENT '真实用户名',
  `idcard` varchar(255) DEFAULT '' COMMENT '证件号码',
  `phone` varchar(120) DEFAULT NULL COMMENT '手机号码',
  `avatar` varchar(250) DEFAULT NULL COMMENT '头像',
  `job_level` tinyint(1) DEFAULT '0' COMMENT '工种',
  `address` varchar(255) DEFAULT '' COMMENT '地址',
  `all_time` int(11) DEFAULT '0' COMMENT '总的作业时间',
  `all_area` decimal(10,2) DEFAULT '0.00' COMMENT '总的喷洒面积',
  `all_times` int(11) DEFAULT '0' COMMENT '总的作业次数',
  `showed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `uid_id` (`uid`,`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//农业无人机飞手team表
CREATE TABLE `agro_team` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upper_teamid` int(11) DEFAULT '0' COMMENT 'agro_team表的上一级id',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid',   
  `name` varchar(150) DEFAULT NULL COMMENT '团队名称', 
  `avatar` varchar(250) DEFAULT NULL COMMENT '头像',
  `showed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',  
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `uid_id` (`uid`,`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


//农业无人机作业任务表
CREATE TABLE `agro_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `upper_uid` varchar(120) DEFAULT NULL COMMENT 'agro_team表用户中心系统uuid',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid', 
  `team_id` int(11) DEFAULT '0' COMMENT 'agro_team表的id',  
  `name` varchar(150) DEFAULT NULL COMMENT '任务名称', 
  `date` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '此次任务所用的时间',
  `area` int(11) DEFAULT 0  COMMENT '作业面积',
  `type` varchar(150) DEFAULT NULL COMMENT '任务类型',
  `crop` varchar(150) DEFAULT NULL COMMENT '农作物',
  `crop_stage` varchar(150) DEFAULT NULL COMMENT '生育时期',
  `prevent` varchar(150) DEFAULT NULL COMMENT '防治对象',
  `setting` varchar(250) DEFAULT NULL COMMENT '农机设置参数实体',
  `key_point` text COMMENT '航点的关键点，如A,B点',
  `home` varchar(250) DEFAULT NULL COMMENT '多个返航点的坐标',
  `obstacle_point` text COMMENT '一个或多个障碍物的相关坐标点',
  `plan_edge_poit` text COMMENT '规划边缘航点',
  `edge_point` text COMMENT '边缘航点',
  `way_point` text COMMENT '航点',
  `lat` decimal(10,6) DEFAULT '0.000000' COMMENT '纬度',
  `lng` decimal(10,6) DEFAULT '0.000000' COMMENT '经度',
  `location` varchar(250) DEFAULT NULL COMMENT '执行本次任务的地点', 
  `battery_times`  tinyint(1) NOT NULL DEFAULT '0' COMMENT '更换电池次数',
  `interval` varchar(250) DEFAULT NULL COMMENT '自动作业时的作业间距',
  `app_type` varchar(12) DEFAULT NULL COMMENT 'Android or ios',
  `radar_height` double DEFAULT NULL,
  `spray_flow` double DEFAULT NULL,
  `work_speed` double DEFAULT NULL,
  `spray_width` double DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `uid_id` (`uid`,`deleted`),
   KEY `upper_uid` (`upper_uid`,`deleted`),
   KEY `team_id` (`team_id`,`deleted`),
   KEY `date` (`date`,`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//飞行记录聚合表
CREATE TABLE `agro_flight` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `flight_data_id` bigint(20) unsigned NOT NULL DEFAULT 0  COMMENT 'iuav_flight_data表的id',
  `upper_uid` varchar(120) DEFAULT NULL COMMENT 'agro_team表用户中心系统uuid',
  `uid` varchar(120) DEFAULT NULL COMMENT '用户中心系统uuid', 
  `team_id` int(10) unsigned DEFAULT NULL COMMENT '团队id',
  `version` smallint(5) unsigned DEFAULT NULL COMMENT '版本',
  `timestamp` bigint(20) unsigned DEFAULT NULL COMMENT '时间戳',
  `longi` double DEFAULT NULL COMMENT 'GPS经度',
  `lati` double DEFAULT NULL COMMENT 'GPS纬度',
  `location` varchar(250) DEFAULT NULL COMMENT 'GPS地点名称', 
  `product_sn` varchar(255) DEFAULT NULL COMMENT '飞控硬件id',
  `session_num` BIGINT  DEFAULT 0 COMMENT '一次起落都',
  `farm_delta_y` double DEFAULT NULL COMMENT '喷幅',
  `flight_version` varchar(255) DEFAULT 0 COMMENT '飞控版本号',
  `plant` tinyint(3) unsigned DEFAULT NULL COMMENT '农作物种类',
  `work_area` int(11) DEFAULT 0  COMMENT '作业面积',
  `work_time` int(11) DEFAULT 0  COMMENT '飞行时间',
  `start_end` varchar(255) DEFAULT NULL COMMENT '起落时间',
  `ext1` varchar(255) DEFAULT NULL COMMENT '扩展1',
  `ext2` bigint(20) DEFAULT NULL COMMENT '扩展2',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` int(11) unsigned NOT NULL COMMENT 'BCD码表示，格式：YYYYMMDD',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`),
   KEY `upper_uid` (`upper_uid`,`create_date`),
   KEY `uid` (`uid`,`create_date`),
   KEY `team_id` (`team_id`,`create_date`),
   KEY `timestamp` (`timestamp`),
   KEY `product_date` (`product_sn`,`create_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

//飞行记录详情表
CREATE TABLE `iuav_flight_data` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT DEFAULT 0,
  `team_id` int DEFAULT 0,
  `version` tinyint DEFAULT 1,
  `timestamp` BIGINT DEFAULT 0,
  `longi` double DEFAULT 0.0,
  `lati` double DEFAULT 0.0,
  `alti` float DEFAULT 0.0,
  `product_sn` varchar(16) DEFAULT "",
  `spray_flag` tinyint DEFAULT 0,
  `motor_status` tinyint DEFAULT 0,
  `radar_height` SMALLINT DEFAULT 0,
  `velocity_x` float DEFAULT 0.0,
  `velocity_y` float DEFAULT 0.0,
  `farm_delta_y` float DEFAULT 0.0,
  `farm_mode` tinyint DEFAULT 0,
  `pilot_num` BIGINT DEFAULT 0,
  `session_num` BIGINT  DEFAULT 0,
  `frame_index` int DEFAULT 0,
  `frame_flag` tinyint DEFAULT 0,
  `flight_version` varchar(255) DEFAULT 0,
  `plant` tinyint DEFAULT 0, 
  `create_time` int DEFAULT 0, 
  `work_area` int DEFAULT 0,
  `boss_id` varchar(125) DEFAULT '',
  `ext1` varchar(256) DEFAULT "",
  `ext2` bigint DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `team_id` (`team_id`),
  KEY `product_sn` (`product_sn`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//农业无人机保险表
CREATE TABLE `agro_policies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `apply_id` int(11) DEFAULT '0' COMMENT '农业无人机申请表',
  `order_id` varchar(100) DEFAULT '' COMMENT '当前激活id,用于传给保险公司',
  `query_id` varchar(125) DEFAULT '' COMMENT '保险公司查询id',
  `pol_no` varchar(125) DEFAULT '' COMMENT '保单号',
  `eff_tm` varchar(16) DEFAULT '' COMMENT '保险起保时间,格式为：YYYYMMDDHHMMSS',
  `exp_tm` varchar(16) DEFAULT '' COMMENT '保险结束时间,格式为：YYYYMMDDHHMMSS',  
  `input_tm` varchar(18) DEFAULT NULL COMMENT '创建日期',
  `amount` varchar(120) DEFAULT NULL COMMENT '保额(元)',  
  `premium` varchar(150) DEFAULT NULL COMMENT '保费(元)',
  `query_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-获取保单成功,0-失败',
  `query_desc` varchar(150) DEFAULT NULL COMMENT '描述', 
  `mark` tinyint(1) NOT NULL DEFAULT '0' COMMENT '财务标记',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `is_notice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通知农机用户平台 0:未，1：已经',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `order_apply_id` (`order_id`,`apply_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

alter table agro_policies add (`is_notice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通知农机用户平台 0:未，1：已经');


//工厂sn数据
CREATE TABLE `agro_sn_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `body_code` varchar(150) DEFAULT NULL COMMENT '机身码',
  `hardware_id` varchar(150) DEFAULT NULL COMMENT '硬件id',
  `activation` varchar(150) DEFAULT NULL COMMENT '激活码',
  `scan_date` varchar(30) DEFAULT NULL COMMENT '扫描日期',
  `type` varchar(20) DEFAULT NULL COMMENT '型号:mg-1;mg-2;mg-3',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `type_body_hardware` (`type`,`body_code`,`hardware_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//Mis PI 系统对接 代理和机身码的对应关系
CREATE TABLE `agro_agent_body` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `body_code` varchar(150) DEFAULT NULL COMMENT '机身码',
  `hardware_id` varchar(150) DEFAULT NULL COMMENT '硬件id',
  `agentname` varchar(255) DEFAULT '' COMMENT '代理商名称',
  `code` varchar(50) DEFAULT NULL COMMENT '代理商code',
  `email` varchar(120) DEFAULT NULL COMMENT '邮箱',
  `type` varchar(20) DEFAULT NULL COMMENT '型号:mg-1;mg-2;mg-3',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
   PRIMARY KEY (`id`),
   KEY `type_body_hardware` (`type`,`body_code`,`hardware_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

//代理商表
CREATE TABLE `agro_agent_mis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agentname` varchar(255) DEFAULT '' COMMENT '代理商名称',
  `code` varchar(50) DEFAULT NULL COMMENT '代理商代号',
  `realname` varchar(255) DEFAULT '' COMMENT '负责人',
  `idcard` varchar(255) DEFAULT '' COMMENT '身份证 护照 军官证等',  
  `phone` varchar(120) DEFAULT NULL COMMENT '手机号码',
  `email` varchar(120) DEFAULT NULL COMMENT '邮箱',
  `country` varchar(50) DEFAULT '' COMMENT '国家', 
  `province`  varchar(50) DEFAULT '' COMMENT '省份', 
  `city` varchar(50) DEFAULT '' COMMENT '城市',   
  `address` varchar(255) DEFAULT '' COMMENT ' 邮寄地址',
  `zipcode` varchar(255) DEFAULT '' COMMENT ' 邮编', 
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',  
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间', 
  `staff` varchar(120) DEFAULT NULL COMMENT 'dji负责人员', 
   PRIMARY KEY (`id`),
   KEY `email` (`email`),
   KEY `code` (`code`) 
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;




//通知
CREATE TABLE `agro_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT '代理：agent ;客户：client',
  `title` varchar(150) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `attachment` varchar(150) DEFAULT NULL COMMENT '附件',
  `description` varchar(150) DEFAULT NULL COMMENT '附件说明',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type_deleted` (`deleted`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

//地区对应街道表
CREATE TABLE `agro_street` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT, 
  `area_no` varchar(20) DEFAULT NULL COMMENT '区id',
  `name` varchar(200) DEFAULT NULL COMMENT '街道名称',
  `street_no` varchar(20) DEFAULT NULL COMMENT '街道名称',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',  
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `area_no_deleted` (`area_no`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

//国家、城市、县
CREATE TABLE `agro_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '名称',
  `aid` varchar(50) DEFAULT '' COMMENT '名称id ',
  `parent` varchar(50) DEFAULT '' COMMENT '上级id ',
  `type` varchar(50) DEFAULT '' COMMENT '类型 ',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ext1` varchar(100) DEFAULT '' COMMENT '扩展1',
  `ext2` varchar(100) DEFAULT '' COMMENT '扩展2',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

//留言表
CREATE TABLE `agro_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(255) DEFAULT '' COMMENT '用户dji邮箱',
  `user_id` varchar(255) DEFAULT '' COMMENT '用户中心id',
  `register_phone` varchar(255) DEFAULT '' COMMENT '手机号',
  `title` varchar(255) DEFAULT '' COMMENT '标题', 
  `type` varchar(255) DEFAULT '' COMMENT '类型',  
  `message` text COMMENT '留言内容',
  `status` varchar(100) NOT NULL DEFAULT 'noview' COMMENT '状态',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`),
   KEY `account` (`account`),
   KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



alter table agro_report add (`status` varchar(100) NOT NULL DEFAULT 'noview' COMMENT '状态');
//反馈处理记录表
CREATE TABLE `agro_report_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT '0' COMMENT 'agro_report表id',
  `message` text COMMENT '留言内容',
  `status` varchar(100) NOT NULL DEFAULT 'noview' COMMENT '状态',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `operator` varchar(100) DEFAULT NULL COMMENT '操作人员',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`),  
   KEY `report_id` (`report_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


//维修评价表
CREATE TABLE `agro_evaluate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caseno` varchar(255) DEFAULT '' COMMENT '维修订单号',
  `account` varchar(255) DEFAULT '' COMMENT '用户dji邮箱',
  `user_id` varchar(255) DEFAULT '' COMMENT '用户中心id',
  `register_phone` varchar(255) DEFAULT '' COMMENT '手机号',
  `totality` tinyint(1) NOT NULL DEFAULT '0' COMMENT '总体满意度', 
  `speed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '维修速度', 
  `quality` tinyint(1) NOT NULL DEFAULT '0' COMMENT '质量', 
  `attitude` tinyint(1) NOT NULL DEFAULT '0' COMMENT '态度', 
  `message` text COMMENT '留言内容',
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`),
   KEY `account` (`account`),
   KEY `user_id` (`user_id`),
   KEY `caseno` (`caseno`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

//购买农机的dji账号用户
CREATE TABLE `agro_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(255) DEFAULT '' COMMENT '用户dji邮箱',
  `user_id` varchar(255) DEFAULT '' COMMENT '用户中心id',
  `is_account` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通知用户中心 0:未，1：已', 
  `ip` varchar(100) DEFAULT '' COMMENT 'ip地址',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
   PRIMARY KEY (`id`),
   KEY `account` (`account`),
   KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8



CREATE TABLE `operation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` char(50) DEFAULT '',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT '0',
  `resource_id` int(11) DEFAULT '0',
  `resource_type` varchar(255) DEFAULT '',
  `resource_info` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;




CREATE TABLE `agro_total` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) DEFAULT '0' COMMENT 'agro_agent代理商表的id',
  `year` int(5) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `productType` varchar(25) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `total` int(11) DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_agent_id` (`date`,`agent_id`,`productType`) USING BTREE,
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8




```


Yii 2 Basic Project Template
============================

Yii 2 Basic Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
rapidly creating small projects.

The template contains the basic features including user login/logout and a contact page.
It includes all commonly used configurations that would allow you to focus on adding new
features to your application.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-basic/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-basic/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Install from an Archive File

Extract the archive file downloaded from [yiiframework.com](http://www.yiiframework.com/download/) to
a directory named `basic` that is directly under the Web root.

Set cookie validation key in `config/web.php` file to some random secret string:

```php
'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => '<secret random string goes here>',
],
```

You can then access the application through the following URL:

~~~
http://localhost/basic/web/
~~~


### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:

~~~
php composer.phar global require "fxp/composer-asset-plugin:~1.0.0"
php composer.phar create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
~~~

Now you should be able to access the application through the following URL, assuming `basic` is the directory
directly under the Web root.

~~~
http://localhost/basic/web/
~~~


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTE:** Yii won't create the database for you, this has to be done manually before you can access it.

Also check and edit the other files in the `config/` directory to customize your application.
