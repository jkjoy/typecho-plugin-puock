# 简介

`Puock` 专属插件,用于生成海报,打赏,分享二维码,emoji表情等功能

### 使用

下载本项目到 `usr/plugins/` 目录下并解压

更改插件目录名为 `Puock`

### 会话总结（日期：2024-06-09）

1. 主要目的：
   - 解释 PHP 报错 `preg_replace_callback()` 参数类型弃用警告的原因，并给出修复建议。
2. 完成的主要任务：
   - 分析报错内容，定位为 PHP 8.1+ 版本兼容性问题。
   - 提供了代码级修复建议，确保传递给 `preg_replace_callback` 的第三个参数不为 null。
3. 关键决策和解决方案：
   - 明确不是插件冲突，而是 PHP 版本升级导致的兼容性问题。
   - 建议在 `/app/usr/plugins/Links/Plugin.php` 和 `/app/usr/plugins/Meting/Plugin.php` 的相关行，调用 `preg_replace_callback` 前将 `$subject` 置为字符串或数组，避免 null。
4. 使用的技术栈：
   - PHP 8.1+、Typecho 插件开发。
5. 修改了哪些文件：
   - 建议修改 `/app/usr/plugins/Links/Plugin.php` 和 `/app/usr/plugins/Meting/Plugin.php`（本次未直接修改，但已给出修改建议）。

