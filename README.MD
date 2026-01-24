# 简介

`Puock` 插件,用于友情链接功能,根据`links`插件修改
不使用插件则功能不可用.

### 使用

下载本项目到 `/usr/plugins/` 目录下并解压

更改插件目录名为 `Puock`

增加瞬间 API 路由: /api/v1/memos 兼容memos接口

增加主题端可直接调用的 PHP 渲染/获取方法（不走 API）：

获取数组：$moments = Puock_Plugin::momentsFetch(20, 1, 'PUBLIC');
按 Puock 主题结构直接输出 HTML：<?php Puock_Plugin::momentsOutputPuock(20, 1, 'PUBLIC', 'markdown'); ?>
只拿 HTML 字符串：$html = Puock_Plugin::momentsOutputPuock(20, 1, 'PUBLIC', 'markdown', 'HTML');

需要自定义样式时，建议用 momentsFetch() 自己循环渲染。

### 主题地址

[Puock](https://github.com/jkjoy/Typecho-Theme-Puock)