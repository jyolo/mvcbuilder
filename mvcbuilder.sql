

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `jy_models_component`
-- ----------------------------
DROP TABLE IF EXISTS `jy_models_component`;
CREATE TABLE `jy_models_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `models_id` int(11) DEFAULT NULL,
  `component_name` varchar(200) DEFAULT NULL COMMENT '组件名称',
  `sorts` int(10) DEFAULT NULL COMMENT '排序',
  `intable` tinyint(1) DEFAULT '0' COMMENT '是否显示在table里面，1为显示 0 不显示',
  `insearch` tinyint(1) DEFAULT '0' COMMENT '是否为搜索项，1为显示 0 不显示',
  `setting` text COMMENT '组件的设置',
  `add_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模型组件表 一个模型有多个组件组成';

-- ----------------------------
-- Records of jy_models_component
-- ----------------------------

-- ----------------------------
-- Table structure for `jy_models`
-- ----------------------------
DROP TABLE IF EXISTS `jy_models`;
CREATE TABLE `jy_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) DEFAULT '0' COMMENT '模块id',
  `models_name` varchar(80) DEFAULT NULL COMMENT '模型名称',
  `table_name` varchar(80) DEFAULT NULL COMMENT '模型表的名称',
  `tpl_plan` varchar(20) DEFAULT NULL COMMENT '使用的模板类型',
  `primary_name` varchar(20) DEFAULT NULL COMMENT '表的主键',
  `primary_type` varchar(15) DEFAULT NULL COMMENT '主键的类型',
  `primary_length` varchar(10) DEFAULT NULL COMMENT '主键的长度',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0：正常，1 停用',
  `add_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL COMMENT '管理员id',
  `manager_name` varchar(30) DEFAULT NULL COMMENT '管理员名称',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模型表';

-- ----------------------------
-- Records of jy_models
-- ----------------------------
