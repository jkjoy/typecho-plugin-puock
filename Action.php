<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 插件动作处理类
 * @package Puock
 * @author jkjoy
 */
class Puock_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $options;
    private $pluginConfig;
    
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->options = $this->widget('Widget_Options');
        $this->pluginConfig = Typecho_Widget::widget('Widget_Options')->plugin('Puock');
    }
    
    // 通过cid查数据库获取文章链接
    private function getPermalinkByCid($cid) {
        try {
            // 使用 Typecho 的数据库接口
            $db = Typecho_Db::get();
            
            // 获取文章数据
            $select = $db->select('cid', 'title', 'slug', 'created', 'type', 'status')
                ->from('table.contents')
                ->where('cid = ?', $cid)
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->limit(1);
                
            $row = $db->fetchRow($select);
            
            if (!$row) {
                error_log('Puock Plugin - 未找到文章或文章未发布：' . $cid);
                return '';
            }
            
            // 使用 Typecho 的路由生成器获取链接
            try {
                $routes = Typecho_Router::get('post');
                $routeExists = !empty($routes);
                
                if ($routeExists) {
                    $permalink = Typecho_Router::url('post', $row, Helper::options()->index);
                    error_log('Puock Plugin - 成功生成文章链接：' . $permalink);
                    return $permalink;
                } else {
                    error_log('Puock Plugin - 路由规则不存在，尝试使用默认格式');
                    $options = Helper::options();
                    return Typecho_Common::url('/archives/' . $cid, $options->index);
                }
            } catch (Exception $e) {
                error_log('Puock Plugin - 获取文章链接失败：' . $e->getMessage());
                error_log('Puock Plugin - 错误堆栈：' . $e->getTraceAsString());
                return '';
            }
        } catch (Exception $e) {
            error_log('Puock Plugin - 获取文章链接失败：' . $e->getMessage());
            return '';
        }
    }

    // 分享页面
    public function share()
    {
        try {
            $cid = $this->request->get('cid');
            if (empty($cid)) {
                throw new Exception('缺少文章ID参数');
            }

            error_log('Puock Plugin - 分享页面：尝试获取文章 ' . $cid);
            
            // 先验证文章是否存在
            $db = Typecho_Db::get();
            $post = $db->fetchRow($db->select()
                ->from('table.contents')
                ->where('cid = ? AND type = ?', $cid, 'post')
                ->where('status = ?', 'publish')
                ->limit(1));

            if (!$post) {
                error_log('Puock Plugin - 分享页面：文章不存在或未发布 ' . $cid);
                throw new Exception('文章不存在或未发布');
            }

            $permalink = $this->getPermalinkByCid($cid);
            if (empty($permalink)) {
                error_log('Puock Plugin - 分享页面：无法生成文章链接 ' . $cid);
                throw new Exception('无法生成文章链接');
            }

            error_log('Puock Plugin - 分享页面：生成文章链接成功 ' . $permalink);
            $qrCodePath = $this->generateQrCode($permalink, $cid);

        $this->renderTemplate('share', [
            'post' => (object)['permalink' => $permalink],
            'qrCodePath' => $qrCodePath,
            'pluginConfig' => $this->pluginConfig
        ]);
    } catch (Exception $e) {
            error_log('Puock Plugin - 分享页面错误: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // 赞赏页面
    public function reward()
    {
        $cid = $this->request->get('cid');
        $post = null;
        if ($cid) {
            $post = $this->widget('Widget_Archive@post_' . $cid, "cid={$cid}");
        }
        // 渲染赞赏模板
        $this->renderTemplate('reward', [
            'post' => $post,
            'pluginConfig' => $this->pluginConfig
        ]);
    }

    //emoji
    public function emoji()
    {
        // 渲染赞赏模板
        $this->renderTemplate('emoji', []);
    }

    //login
    public function login()
    {
        // 渲染登录模板
        $this->renderTemplate('login', []);
    }

    // AJAX 登录
    public function ajaxlogin()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($this->request->isPost()) {
            $name = $this->request->filter('trim')->name;
            $password = $this->request->password;
            $referer = $this->request->referer ? $this->request->referer : '/';
            if (!$name || !$password) {
                echo json_encode(['success' => false, 'msg' => '用户名或密码不能为空']);
                exit;
            }
            $user = Typecho_Widget::widget('Widget_User');
            try {
                $user->login($name, $password, $this->request->remember ? 1 : 0);
                // 修复：手动判断是否登录成功
                if ($user->hasLogin()) {
                    echo json_encode(['success' => true, 'msg' => '登录成功', 'redirect' => $referer]);
                } else {
                    echo json_encode(['success' => false, 'msg' => '用户名或密码错误']);
                }
            } catch (Typecho_Exception $e) {
                echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'msg' => '请求方式错误']);
        }
        exit;
    }

    // 海报页面
    public function poster()
    {
        try {
            $cid = $this->request->get('cid');
            if (empty($cid)) {
                throw new Exception('缺少文章ID参数');
            }

            // 获取文章数据
            error_log('Puock Plugin - 尝试获取文章，CID：' . $cid);
            
            // 先尝试通过数据库直接验证文章是否存在
            $db = Typecho_Db::get();
            $row = $db->fetchRow($db->select()->from('table.contents')
                ->where('cid = ? AND type = ?', $cid, 'post')
                ->where('status = ?', 'publish'));
                
            if (!$row) {
                error_log('Puock Plugin - 数据库中未找到文章：' . $cid);
                throw new Exception('文章不存在或未发布：' . $cid);
            }
            
            error_log('Puock Plugin - 数据库中找到文章：' . json_encode($row));
            
            // 获取文章详细数据
            try {
                $options = Helper::options();
                // 构建完整的文章数据
                $post = (object) array_merge($row, array(
                    'permalink' => Typecho_Router::url('post', $row, $options->index),
                    'fields' => array(),
                    'categories' => array(),
                    'title' => $row['title'],
                    'cid' => $row['cid'],
                    'content' => $row['text'],
                    'status' => $row['status'],
                    'type' => $row['type']
                ));

                // 获取文章自定义字段
                $fields = $db->fetchAll($db->select()->from('table.fields')
                    ->where('cid = ?', $cid));
                if ($fields) {
                    foreach ($fields as $field) {
                        $post->fields[$field['name']] = $field['str_value'];
                    }
                }
                
                error_log('Puock Plugin - 已构建文章数据：' . json_encode($post));
            } catch (Exception $e) {
                error_log('Puock Plugin - 构建文章数据失败：' . $e->getMessage());
                throw new Exception('无法构建文章数据：' . $cid);
            }

            // 调试信息
            error_log('Puock Plugin - 成功获取文章信息：');
            error_log('文章ID：' . $post->cid);
            error_log('文章标题：' . $post->title);
            error_log('文章链接：' . $post->permalink);
            error_log('文章状态：' . $post->status);
            error_log('文章类型：' . $post->type);
            
            // 生成文章二维码
            $qrCodePath = $this->generateQrCode($post->permalink, $cid);
            
            // 获取文章封面
            $cover = $this->getPostCover($post);
            error_log('获取到的封面图片URL：' . $cover);
            
            // 渲染海报模板
            $this->renderTemplate('poster', [
                'post' => $post,
                'qrCodePath' => $qrCodePath,
                'cover' => $cover,
                'pluginConfig' => $this->pluginConfig
            ]);
        } catch (Exception $e) {
            error_log('生成海报错误: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // 生成二维码
    private function generateQrCode($url, $cid)
    {
        // 引入phpqrcode库
        require_once __DIR__ . '/phpqrcode.php';

        $cacheDir = defined('__TYPECHO_ROOT_DIR__')
            ? __TYPECHO_ROOT_DIR__ . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR
            : dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR;

        if (!file_exists($cacheDir)) {
            if (!@mkdir($cacheDir, 0755, true)) {
                throw new Exception('无法创建二维码缓存目录：' . $cacheDir);
            }
        }

        $qrCodeFile = $cacheDir . 'qrcode_' . $cid . '.png';
        $qrCodeUrl = rtrim($this->options->siteUrl, '/') . '/usr/cache/qrcodes/qrcode_' . $cid . '.png';

        // 如果二维码已存在，直接返回本地图片URL
        if (file_exists($qrCodeFile)) {
            return $qrCodeUrl;
        }

        try {
            // 使用phpqrcode生成二维码
            QRcode::png($url, $qrCodeFile, QR_ECLEVEL_L, 10, 2);
            
            if (file_exists($qrCodeFile)) {
                return $qrCodeUrl;
            } else {
                throw new Exception('二维码生成失败');
            }
        } catch (Exception $e) {
            // 记录错误日志
            error_log('二维码生成错误: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // 获取文章封面
    private function getPostCover($post)
    {
        $cover = '';
        
        // 1. 优先使用自定义字段 cover
        if (!empty($post->fields)) {
            // 获取所有自定义字段
            $fields = $post->fields;
            if (is_array($fields)) {
                if (isset($fields['cover'])) {
                    $cover = $fields['cover'];
                }
            } else if (is_object($fields) && isset($fields->cover)) {
                $cover = $fields->cover;
            }
        }

        // 2. 如果没有自定义字段，尝试从文章内容中提取第一张图片
        if (empty($cover) && !empty($post->content)) {
            // 使用更严格的图片匹配规则
            if (preg_match('/<img[\s]+[^>]*?src[\s]?=[\s\'"]*((https?:\/\/|\/)[^\'"\s>]+)[\s\'"][^>]*>/i', $post->content, $matches)) {
                $cover = $matches[1];
            }
        }

        // 3. 如果没有找到图片，使用默认图片
        if (empty($cover)) {
            $cover = $this->options->themeUrl . '/assets/img/cover.png';
            
            // 输出调试信息到日志
            error_log('Puock Plugin - 未找到文章封面，使用默认图片。文章ID：' . $post->cid);
            if (!empty($post->fields)) {
                error_log('Puock Plugin - 文章自定义字段：' . print_r($post->fields, true));
            }
            error_log('Puock Plugin - 文章内容前100字符：' . substr($post->content, 0, 100));
        }

        return $cover;
    }
    
    // 渲染模板
    private function renderTemplate($name, $data = [])
    {
        $templateFile = __DIR__ . '/views/' . $name . '.php';
        if (file_exists($templateFile)) {
            extract($data);
            include $templateFile;
        } else {
            throw new Typecho_Exception('模板文件不存在: ' . $templateFile);
        }
    }
    
    // 清空二维码缓存目录
    public function cleanQrCodeCache()
    {
        // 检查用户权限
        $user = Typecho_Widget::widget('Widget_User');
        if (!$user->hasLogin() || !$user->pass('administrator', true)) {
            // 返回未授权错误
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'msg' => '您没有权限执行此操作'
            ]);
            exit;
        }

        try {
            // 获取二维码缓存目录路径
            $cacheDir = defined('__TYPECHO_ROOT_DIR__')
                ? __TYPECHO_ROOT_DIR__ . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR
                : dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR;
            
            // 检查目录是否存在
            if (!file_exists($cacheDir)) {
                echo json_encode(['success' => true, 'msg' => '缓存目录不存在，无需清理']);
                exit;
            }
            
            // 遍历并删除目录中的所有文件
            $files = glob($cacheDir . '*.png');
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    if (unlink($file)) {
                        $count++;
                    }
                }
            }
            
            // 记录日志
            error_log('Puock Plugin - 清理二维码缓存：已删除 ' . $count . ' 个文件');
            
            // 返回结果
            echo json_encode([
                'success' => true, 
                'msg' => '成功清理二维码缓存，共删除 ' . $count . ' 个文件'
            ]);
        } catch (Exception $e) {
            error_log('Puock Plugin - 清理二维码缓存失败: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'msg' => '清理缓存失败：' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========== Links 友链管理功能 ==========

    public function insertLink()
    {
        if (Puock_Plugin::linksForm('insert')->validate()) {
            $this->response->goBack();
        }
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        /** 取出数据 */
        $link = $this->request->from('email', 'image', 'url', 'state');

        /** 过滤XSS */
        $link['name'] = $this->request->filter('xss')->name;
        $link['sort'] = $this->request->filter('xss')->sort;
        $link['description'] = $this->request->filter('xss')->description;
        $link['user'] = $this->request->filter('xss')->user;
        $link['order'] = $db->fetchObject($db->select(array('MAX(order)' => 'maxOrder'))->from($prefix . 'links'))->maxOrder + 1;

        /** 插入数据 */
        $link_lid = $db->query($db->insert($prefix . 'links')->rows($link));

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight('link-' . $link_lid);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t(
            '友链 <a href="%s">%s</a> 已经被增加',
            $link['url'],
            $link['name']
        ), null, 'success');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Puock%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function updateLink()
    {
        if (Puock_Plugin::linksForm('update')->validate()) {
            $this->response->goBack();
        }

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        /** 取出数据 */
        $link = $this->request->from('email', 'image', 'url', 'state');
        $link_lid = $this->request->from('lid');

        /** 过滤XSS */
        $link['name'] = $this->request->filter('xss')->name;
        $link['sort'] = $this->request->filter('xss')->sort;
        $link['description'] = $this->request->filter('xss')->description;
        $link['user'] = $this->request->filter('xss')->user;

        /** 更新数据 */
        $db->query($db->update($prefix . 'links')->rows($link)->where('lid = ?', $link_lid));

        /** 设置高亮 */
        $this->widget('Widget_Notice')->highlight('link-' . $link_lid);

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t(
            '友链 <a href="%s">%s</a> 已经被更新',
            $link['url'],
            $link['name']
        ), null, 'success');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Puock%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function deleteLink()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        $lids = $this->request->filter('int')->getArray('lid');
        $deleteCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($db->query($db->delete($prefix . 'links')->where('lid = ?', $lid))) {
                    $deleteCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $deleteCount > 0 ? _t('友链已经删除') : _t('没有友链被删除'),
            null,
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Puock%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function enableLink()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        $lids = $this->request->filter('int')->getArray('lid');
        $enableCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($db->query($db->update($prefix . 'links')->rows(array('state' => '1'))->where('lid = ?', $lid))) {
                    $enableCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $enableCount > 0 ? _t('友链已经启用') : _t('没有友链被启用'),
            null,
            $enableCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Puock%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function prohibitLink()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        $lids = $this->request->filter('int')->getArray('lid');
        $prohibitCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($db->query($db->update($prefix . 'links')->rows(array('state' => '0'))->where('lid = ?', $lid))) {
                    $prohibitCount++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set(
            $prohibitCount > 0 ? _t('友链已经禁用') : _t('没有友链被禁用'),
            null,
            $prohibitCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Puock%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function sortLink()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();

        $links = $this->request->filter('int')->getArray('lid');
        if ($links && is_array($links)) {
            foreach ($links as $sort => $lid) {
                $db->query($db->update($prefix . 'links')->rows(array('order' => $sort + 1))->where('lid = ?', $lid));
            }
        }
    }

    public function emailLogo()
    {
        /* 邮箱头像解API接口 by 懵仙兔兔 */
        $type = $this->request->type;
        $email = $this->request->email;

        if ($email == null || $email == '') {
            $this->response->throwJson('请提交邮箱链接 [email=abc@abc.com]');
            exit;
        } else if ($type == null || $type == '' || ($type != 'txt' && $type != 'json')) {
            $this->response->throwJson('请提交type类型 [type=txt, type=json]');
            exit;
        } else {
            $f = str_replace('@qq.com', '', $email);
            $email = $f . '@qq.com';
            if (is_numeric($f) && strlen($f) < 11 && strlen($f) > 4) {
                stream_context_set_default([
                    'ssl' => [
                        'verify_host' => false,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
                $geturl = 'https://s.p.qq.com/pub/get_face?img_type=3&uin=' . $f;
                $headers = get_headers($geturl, TRUE);
                if ($headers) {
                    $g = $headers['Location'];
                    $g = str_replace("http:", "https:", $g);
                } else {
                    $g = 'https://q.qlogo.cn/g?b=qq&nk=' . $f . '&s=100';
                }
            } else {
                $g = 'https://cdn.helingqi.com/wavatar/' . md5($email) . '?d=mm';
            }
            $r = array('url' => $g);
            if ($type == 'txt') {
                $this->response->throwJson($g);
                exit;
            } else if ($type == 'json') {
                $this->response->throwJson(json_encode($r));
                exit;
            }
        }
    }

    // 实现接口要求的方法
    public function action() {
        // Links 友链管理动作
        $user = Typecho_Widget::widget('Widget_User');
        if ($this->request->is('do=insert') ||
            $this->request->is('do=update') ||
            $this->request->is('do=delete') ||
            $this->request->is('do=enable') ||
            $this->request->is('do=prohibit') ||
            $this->request->is('do=sort')) {
            Helper::security()->protect();
            $user->pass('administrator');

            $this->on($this->request->is('do=insert'))->insertLink();
            $this->on($this->request->is('do=update'))->updateLink();
            $this->on($this->request->is('do=delete'))->deleteLink();
            $this->on($this->request->is('do=enable'))->enableLink();
            $this->on($this->request->is('do=prohibit'))->prohibitLink();
            $this->on($this->request->is('do=sort'))->sortLink();

            $this->response->redirect($this->options->adminUrl);
        }

        $this->on($this->request->is('do=email-logo'))->emailLogo();
        $this->on($this->request->is('do=cleanQrCodeCache'))->cleanQrCodeCache();
    }
}