<?php
 if (!defined('__TYPECHO_ROOT_DIR__')) exit;
 
 /**
  * Puock主题专用插件
  * 集成友情链接管理
  * @package Puock
  * @author 老孙博客
  * @version 1.4.0
  * @link https://www.imsun.org
  */
 class Puock_Plugin implements Typecho_Plugin_Interface
 {
     public static function activate()
     {
         // 安装友情链接数据表
         $info = Puock_Plugin::linksInstall();

         // 注册友情链接功能
         Helper::addPanel(3, 'Puock/manage-links.php', _t('友情链接'), _t('管理友情链接'), 'administrator');
         Helper::addAction('puock-links', 'Puock_Action');
         Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('Puock_Plugin', 'parse');
         Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('Puock_Plugin', 'parse');
         Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = array('Puock_Plugin', 'parse');
         Typecho_Plugin::factory('Widget_Archive')->callLinks = array('Puock_Plugin', 'output_str');

         return _t('插件激活成功，请配置二维码信息。') . $info;
     }
 
     public static function deactivate()
     {
         // 移除友情链接功能
         Helper::removeAction('puock-links');
         Helper::removePanel(3, 'Puock/manage-links.php');
     }
    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 添加样式用于美化配置页面
        echo '<style>
            .typecho-option {
                position: relative;
                padding: 20px;
                margin-bottom: 15px;
                background: #ffffff;
                border: 1px solid #e8e8e8;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
            .typecho-option:hover {
                border-color: #667eea;
                box-shadow: 0 2px 12px rgba(102, 126, 234, 0.15);
            }
            .typecho-option label.typecho-label {
                font-weight: 600;
                color: #333;
                font-size: 14px;
                margin-bottom: 8px;
                display: block;
            }
            .typecho-option .description {
                color: #999;
                font-size: 12px;
                margin-top: 6px;
                line-height: 1.6;
            }
            .typecho-option input[type="text"],
            .typecho-option textarea {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 13px;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }
            .typecho-option input[type="text"]:focus,
            .typecho-option textarea:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                outline: none;
            }
            .section-title {
                margin: 30px 0 20px 0;
                padding: 15px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 16px;
                font-weight: bold;
                border-radius: 4px;
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            }
            .section-title:first-child {
                margin-top: 0;
            }
            .section-description {
                margin: -10px 0 20px 0;
                padding: 15px 20px;
                background: #f9f9f9;
                border-left: 4px solid #667eea;
                color: #666;
                font-size: 13px;
                line-height: 1.8;
                border-radius: 0 4px 4px 0;
            }
        </style>';

        // ========== 友情链接配置 ==========
        echo '<div class="section-title">友情链接配置</div>';
        echo '<div class="section-description">
            <strong>管理友链：</strong>点击【管理】→【友情链接】进入管理页面<br>
            友情链接功能已集成到本插件中，支持拖拽排序、分类管理、邮箱头像等功能。
        </div>';

        // ========== 友情链接输出配置（原先为隐藏项） ==========
        echo '<div class="section-title">友情链接输出配置</div>';
        echo '<div class="section-description">
            以下配置用于自定义 <code>&lt;links&gt;</code> 标签或 <code>Puock_Plugin::output()</code> 输出的 HTML 模板。<br>
            可用变量：<code>{lid}</code> <code>{name}</code> <code>{url}</code> <code>{sort}</code> <code>{title}</code> <code>{description}</code> <code>{image}</code> <code>{user}</code> <code>{size}</code>
        </div>';

        $pattern_text = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_text',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener">{name}</a></li>',
            _t('文字模式模板（SHOW_TEXT）'),
            _t('用于纯文字友链输出，支持以上变量替换。')
        );
        $form->addInput($pattern_text);

        $pattern_img = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_img',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /></a></li>',
            _t('图片模式模板（SHOW_IMG）'),
            _t('用于图片友链输出，{image} 会在未设置图片时自动回退到默认图片或邮箱头像。')
        );
        $form->addInput($pattern_img);

        $pattern_mix = new Typecho_Widget_Helper_Form_Element_Textarea(
            'pattern_mix',
            null,
            '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /><span>{name}</span></a></li>',
            _t('图文混合模板（SHOW_MIX）'),
            _t('用于图文混排输出，适合带头像+名称的展示样式。')
        );
        $form->addInput($pattern_mix);

        $dsize = new Typecho_Widget_Helper_Form_Element_Text(
            'dsize',
            null,
            '32',
            _t('默认头像尺寸'),
            _t('当未传入 size 参数时使用；用于邮箱头像或图片宽高（单位：px）。')
        );
        $dsize->addRule('isInteger', _t('默认头像尺寸必须为整数'));
        $form->addInput($dsize);
    }
        
    // 个人用户的配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    // ========== Links 友情链接功能方法 ==========

    /**
     * 安装友情链接数据表
     */
    public static function linksInstall()
    {
        $installDb = Typecho_Db::get();
        $type = explode('_', $installDb->getAdapterName());
        $type = array_pop($type);
        $prefix = $installDb->getPrefix();
        $scripts = file_get_contents('usr/plugins/Puock/sql/' . $type . '.sql');
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return _t('建立友情链接数据表，插件启用成功');
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('Mysql' == $type && (1050 == $code || '42S01' == $code)) ||
                ('SQLite' == $type && ('HY000' == $code || 1 == $code))
            ) {
                try {
                    $script = 'SELECT `lid`, `name`, `url`, `sort`, `email`, `image`, `description`, `user`, `state`, `order` from `' . $prefix . 'links`';
                    $installDb->query($script, Typecho_Db::READ);
                    return _t('检测到友情链接数据表，友情链接插件启用成功');
                } catch (Typecho_Db_Exception $e) {
                    $code = $e->getCode();
                    if (('Mysql' == $type && (1054 == $code || '42S22' == $code)) ||
                        ('SQLite' == $type && ('HY000' == $code || 1 == $code))
                    ) {
                        return Puock_Plugin::linksUpdate($installDb, $type, $prefix);
                    }
                    throw new Typecho_Plugin_Exception(_t('数据表检测失败，友情链接插件启用失败。错误号：') . $code);
                }
            } else {
                throw new Typecho_Plugin_Exception(_t('数据表建立失败，友情链接插件启用失败。错误号：') . $code);
            }
        }
    }

    /**
     * 更新友情链接数据表
     */
    public static function linksUpdate($installDb, $type, $prefix)
    {
        $scripts = file_get_contents('usr/plugins/Puock/Update_' . $type . '.sql');
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return _t('检测到旧版本友情链接数据表，升级成功');
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('Mysql' == $type && (1060 == $code || '42S21' == $code))) {
                return _t('友情链接数据表已经存在，插件启用成功');
            }
            throw new Typecho_Plugin_Exception(_t('友情链接插件启用失败。错误号：') . $code);
        }
    }

    /**
     * 友情链接表单生成
     */
    public static function linksForm($action = null)
    {
        /** 构建表格 */
        $options = Typecho_Widget::widget('Widget_Options');
        $form = new Typecho_Widget_Helper_Form(
            Helper::security()->getIndex('/action/puock-links'),
            Typecho_Widget_Helper_Form::POST_METHOD
        );

        /** 友链名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Text('name', null, null, _t('友链名称*'));
        $form->addInput($name);

        /** 友链地址 */
        $url = new Typecho_Widget_Helper_Form_Element_Text('url', null, "http://", _t('友链地址*'));
        $form->addInput($url);

        /** 是否首页显示（值：home / 空） */
        $sort = new Typecho_Widget_Helper_Form_Element_Select(
            'sort',
            array('' => _t('否'), 'home' => _t('是')),
            '',
            _t('是否首页显示'),
            _t('选择“是”时在首页显示，选择“否”则不显示')
        );
        $form->addInput($sort);

        /** 友链邮箱 */
        $email = new Typecho_Widget_Helper_Form_Element_Text('email', null, null, _t('友链邮箱'), _t('填写友链邮箱'));
        $form->addInput($email);

        /** 友链图片 */
        $image = new Typecho_Widget_Helper_Form_Element_Text('image', null, null, _t('友链图片'),  _t('需要以http://或https://开头，留空表示没有友链图片'));
        $form->addInput($image);

        /** 友链描述 */
        $description =  new Typecho_Widget_Helper_Form_Element_Textarea('description', null, null, _t('友链描述'));
        $form->addInput($description);

        /** 自定义数据 */
        $user = new Typecho_Widget_Helper_Form_Element_Text('user', null, null, _t('自定义数据'), _t('该项用于用户自定义数据扩展'));
        $form->addInput($user);

        /** 友链状态 */
        $list = array('0' => '禁用', '1' => '启用');
        $state = new Typecho_Widget_Helper_Form_Element_Radio('state', $list, '1', '友链状态');
        $form->addInput($state);

        /** 友链动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 友链主键 */
        $lid = new Typecho_Widget_Helper_Form_Element_Hidden('lid');
        $form->addInput($lid);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $request = Typecho_Request::getInstance();

        if (isset($request->lid) && 'insert' != $action) {
            /** 更新模式 */
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $request->lid));
            if (!$link) {
                throw new Typecho_Widget_Exception(_t('友链不存在'), 404);
            }

            $name->value($link['name']);
            $url->value($link['url']);
            $sort->value($link['sort'] === 'home' ? 'home' : '');
            $email->value($link['email']);
            $image->value($link['image']);
            $description->value($link['description']);
            $user->value($link['user']);
            $state->value($link['state']);
            $do->value('update');
            $lid->value($link['lid']);
            $submit->value(_t('编辑友链'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加友链'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $name->addRule('required', _t('必须填写友链名称'));
            $url->addRule('required', _t('必须填写友链地址'));
            $url->addRule('url', _t('不是一个合法的链接地址'));
            $email->addRule('email', _t('不是一个合法的邮箱地址'));
            $image->addRule('url', _t('不是一个合法的图片地址'));
            $name->addRule('maxLength', _t('友链名称最多包含50个字符'), 50);
            $url->addRule('maxLength', _t('友链地址最多包含200个字符'), 200);
            $sort->addRule('maxLength', _t('是否首页显示'), 50);
            $email->addRule('maxLength', _t('友链邮箱最多包含50个字符'), 50);
            $image->addRule('maxLength', _t('友链图片最多包含200个字符'), 200);
            $description->addRule('maxLength', _t('友链描述最多包含200个字符'), 200);
            $user->addRule('maxLength', _t('自定义数据最多包含200个字符'), 200);
        }
        if ('update' == $action) {
            $lid->addRule('required', _t('友链主键不存在'));
            $lid->addRule(array(new Puock_Plugin, 'LinkExists'), _t('友链不存在'));
        }
        return $form;
    }

    /**
     * 检查友链是否存在
     */
    public static function LinkExists($lid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $link = $db->fetchRow($db->select()->from($prefix . 'links')->where('lid = ?', $lid)->limit(1));
        return $link ? true : false;
    }

    /**
     * 控制友链输出格式
     */
    public static function output_str($widget, array $params)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        $settings = $options->plugin('Puock');
        if (!isset($options->plugins['activated']['Puock'])) {
            return _t('Puock插件未激活');
        }

        $defaultPatternText = '<li><a href="{url}" title="{title}" target="_blank" rel="noopener">{name}</a></li>';
        $defaultPatternImg = '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /></a></li>';
        $defaultPatternMix = '<li><a href="{url}" title="{title}" target="_blank" rel="noopener"><img src="{image}" alt="{name}" width="{size}" height="{size}" /><span>{name}</span></a></li>';
        $defaultSize = isset($settings->dsize) ? intval($settings->dsize) : 32;
        if ($defaultSize <= 0) {
            $defaultSize = 32;
        }

        //验证默认参数
        $pattern = !empty($params[0]) && is_string($params[0]) ? $params[0] : 'SHOW_TEXT';
        $links_num = !empty($params[1]) && is_numeric($params[1]) ? $params[1] : 0;
        $sort = !empty($params[2]) && is_string($params[2]) ? $params[2] : null;
        $size = !empty($params[3]) && is_numeric($params[3]) ? intval($params[3]) : $defaultSize;
        if ($size <= 0) {
            $size = $defaultSize;
        }
        $mode = isset($params[4]) ? $params[4] : 'FUNC';
        if ($pattern == 'SHOW_TEXT') {
            $pattern = (isset($settings->pattern_text) ? $settings->pattern_text : $defaultPatternText) . "\n";
        } elseif ($pattern == 'SHOW_IMG') {
            $pattern = (isset($settings->pattern_img) ? $settings->pattern_img : $defaultPatternImg) . "\n";
        } elseif ($pattern == 'SHOW_MIX') {
            $pattern = (isset($settings->pattern_mix) ? $settings->pattern_mix : $defaultPatternMix) . "\n";
        }
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $nopic_url = Typecho_Common::url('usr/plugins/Puock/img/nopic.png', $options->siteUrl);
        $sql = $db->select()->from($prefix . 'links');
        if ($sort) {
            $sql = $sql->where('sort=?', $sort);
        }
        $sql = $sql->order($prefix . 'links.order', Typecho_Db::SORT_ASC);
        $links_num = intval($links_num);
        if ($links_num > 0) {
            $sql = $sql->limit($links_num);
        }
        $links = $db->fetchAll($sql);
        $str = "";
        foreach ($links as $link) {
            if ($link['image'] == null) {
                $link['image'] = $nopic_url;
                if ($link['email'] != null) {
                    $link['image'] = 'https://cn.cravatar.com/avatar/' . md5($link['email']) . '?s=' . $size . '&d=mm';
                }
            }
            if ($link['state'] == 1) {
                $str .= str_replace(
                    array('{lid}', '{name}', '{url}', '{sort}', '{title}', '{description}', '{image}', '{user}', '{size}'),
                    array($link['lid'], $link['name'], $link['url'], $link['sort'], $link['description'], $link['description'], $link['image'], $link['user'], $size),
                    $pattern
                );
            }
        }

        if ($mode == 'HTML') {
            return $str;
        } else {
            echo $str;
        }
    }

    /**
     * 友链输出方法
     */
    public static function output($pattern = 'SHOW_TEXT', $links_num = 0, $sort = null, $size = 32, $mode = '')
    {
        return Puock_Plugin::output_str('', array($pattern, $links_num, $sort, $size, $mode));
    }

    /**
     * 解析回调
     */
    public static function parseCallback($matches)
    {
        return Puock_Plugin::output_str('', array($matches[4], $matches[1], $matches[2], $matches[3], 'HTML'));
    }

    /**
     * 内容解析
     */
    public static function parse($text, $widget, $lastResult)
    {
        $text = empty($lastResult) ? $text : $lastResult;

        if ($widget instanceof Widget_Archive || $widget instanceof Widget_Abstract_Comments) {
            return preg_replace_callback("/<links\s*(\d*)\s*(\w*)\s*(\d*)>\s*(.*?)\s*<\/links>/is", array('Puock_Plugin', 'parseCallback'), $text?$text:'');
        } else {
            return $text;
        }
    }
}
