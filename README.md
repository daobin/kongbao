# 空包网


## PHP源码 - 基于TP5框架进行开发

* 技术支持：[树上的程序猿](http://www.laidaobin.com/ "个人博客网站")

* 详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 命名规范

ThinkPHP5遵循PSR-2命名规范和PSR-4自动加载规范，并且注意如下规范：

### 目录和文件
* 目录不强制规范，驼峰和小写+下划线模式均支持；
* 类库、函数文件统一以`.php`为后缀；
* 类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
* 类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；

### 函数和类、属性命名
*   类的命名采用驼峰法，并且首字母大写，例如 `User`、`UserType`，默认不需要添加后缀，例如`UserController`应该直接命名为`User`；
*   函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 `get_client_ip`；
*   方法的命名使用驼峰法，并且首字母小写，例如 `getUserName`；
*   属性的命名使用驼峰法，并且首字母小写，例如 `tableName`、`instance`；
*   以双下划线“__”打头的函数或方法作为魔法方法，例如 `__call` 和 `__autoload`；

### 常量和配置
* 常量以大写字母和下划线命名，例如 `APP_PATH`和 `THINK_PATH`；
* 配置参数以小写字母和下划线命名，例如 `url_route_on` 和`url_convert`；

### 数据表和字段
* 数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 `think_user` 表和 `user_name`字段，不建议使用驼峰和中文作为数据表字段命名。

## 版权信息
本项目版权所有Copyright © 2018 by 树上的程序猿 (http://www.laidaobin.com/)

All Rights Reserved
