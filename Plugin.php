<?php
 if (!defined('__TYPECHO_ROOT_DIR__')) exit;
 
 /**
  * Puock主题专用插件
  * 用于获取海报,分享二维码,赞赏,emoji等功能
  * @package Puock
  * @author 老孙博客
  * @version 1.0.0
  * @link https://www.imsun.org
  */
 class Puock_Plugin implements Typecho_Plugin_Interface
 {
     public static function activate()
     {
         // 注册路由时使用完整类名
         Helper::addRoute('share_reward_route_share', '/share/[cid]/', 'Puock_Action', 'share');
         Helper::addRoute('share_reward_route_share_index', '/index.php/share/[cid]/', 'Puock_Action', 'share');
         Helper::addRoute('share_reward_route_reward', '/reward/[cid]/', 'Puock_Action', 'reward');
         Helper::addRoute('share_reward_route_reward_index', '/index.php/reward/[cid]/', 'Puock_Action', 'reward');
         Helper::addRoute('share_reward_route_reward_nocid', '/reward/', 'Puock_Action', 'reward');
         Helper::addRoute('share_reward_route_reward_nocid_index', '/index.php/reward/', 'Puock_Action', 'reward');
         Helper::addRoute('share_reward_route_poster', '/poster/[cid]/', 'Puock_Action', 'poster');
         Helper::addRoute('share_reward_route_poster_index', '/index.php/poster/[cid]/', 'Puock_Action', 'poster');
         Helper::addRoute('share_reward_route_emoji', '/emoji/', 'Puock_Action', 'emoji');
         Helper::addRoute('share_reward_route_emoji_index', '/index.php/emoji/', 'Puock_Action', 'emoji');
         
         Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'renderFooter');
         
         return '插件激活成功，请配置二维码信息';
     }
 
     public static function deactivate()
     {
         Helper::removeRoute('share_reward_route_share');
         Helper::removeRoute('share_reward_route_share_index');
         Helper::removeRoute('share_reward_route_reward');
         Helper::removeRoute('share_reward_route_reward_index');
         Helper::removeRoute('share_reward_route_reward_nocid');
         Helper::removeRoute('share_reward_route_reward_nocid_index');
         Helper::removeRoute('share_reward_route_poster');
         Helper::removeRoute('share_reward_route_poster_index');
     }
    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 支付宝二维码
        $alipay_qr = new Typecho_Widget_Helper_Form_Element_Text(
            'alipay_qr',
            NULL,
            NULL,
            _t('支付宝收款二维码URL'),
            _t('请输入支付宝收款二维码的完整URL地址')
        );
        $form->addInput($alipay_qr);
        
        // 微信二维码
        $wechat_qr = new Typecho_Widget_Helper_Form_Element_Text(
            'wechat_qr',
            NULL,
            NULL,
            _t('微信收款二维码URL'),
            _t('请输入微信收款二维码的完整URL地址')
        );
        $form->addInput($wechat_qr);
        
        // 网站Logo
        $site_logo = new Typecho_Widget_Helper_Form_Element_Text(
            'site_logo',
            NULL,
            NULL,
            _t('网站Logo URL'),
            _t('用于海报生成的网站Logo，留空则使用默认Logo')
        );
        $form->addInput($site_logo);
    }

    // 个人用户的配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    // 在文章页脚添加分享按钮
    public static function renderFooter()
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $pluginUrl = $options->pluginUrl . '/Puock';
 
        // 只在文章页显示
        if (Typecho_Widget::widget('Widget_Archive')->is('single')) {
            $cid = Typecho_Widget::widget('Widget_Archive')->cid;
            $title = Typecho_Widget::widget('Widget_Archive')->title;
            $permalink = Typecho_Widget::widget('Widget_Archive')->permalink;
            
            // 生成分享按钮HTML
            $html = <<<HTML
 
HTML;
            echo $html;
        }
    }
}