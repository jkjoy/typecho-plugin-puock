<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 插件动作处理类
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
        $db = Typecho_Db::get();
        $options = Helper::options();
        $row = $db->fetchRow($db->select()->from('table.contents')->where('cid = ?', $cid)->limit(1));
        if ($row && $row['type'] === 'post') {
            // 兼容所有固定链接结构
            $permalink = Typecho_Router::url('post', $row, $options->index);
            return $permalink;
        }
        return '';
    }

    // 分享页面
    public function share()
    {
        $cid = $this->request->get('cid');
        $permalink = $this->getPermalinkByCid($cid);

        if (empty($permalink)) {
            throw new Exception('未获取到文章链接，cid: ' . $cid);
        }

        $qrCodePath = $this->generateQrCode($permalink, $cid);

        $this->renderTemplate('share', [
            'post' => (object)['permalink' => $permalink],
            'qrCodePath' => $qrCodePath,
            'pluginConfig' => $this->pluginConfig
        ]);
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
        $cid = $this->request->get('cid');
        $post = $this->widget('Widget_Archive@post_' . $cid, "cid={$cid}");
        
        // 生成文章二维码
        $qrCodePath = $this->generateQrCode($post->permalink, $cid);
        
        // 获取文章封面
        $cover = $this->getPostCover($post);
        
        // 渲染海报模板
        $this->renderTemplate('poster', [
            'post' => $post,
            'qrCodePath' => $qrCodePath,
            'cover' => $cover,
            'pluginConfig' => $this->pluginConfig
        ]);
    }
    
    // 生成二维码
    private function generateQrCode($url, $cid)
    {
        $cacheDir = defined('__TYPECHO_ROOT_DIR__')
            ? __TYPECHO_ROOT_DIR__ . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR
            : dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR;

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $qrCodeFile = $cacheDir . 'qrcode_' . $cid . '.png';
        $qrCodeUrl = rtrim($this->options->siteUrl, '/') . '/usr/cache/qrcodes/qrcode_' . $cid . '.png';

        // 如果二维码已存在，直接返回本地图片URL（长期有效）
        if (file_exists($qrCodeFile)) {
            return $qrCodeUrl;
        }

        // 请求新API生成二维码并保存
        $apiUrl = 'https://api.pwmqr.com/qrcode/create/?url=' . urlencode($url) . '&down=1';
        $qrCodeData = @file_get_contents($apiUrl);

        if ($qrCodeData !== false) {
            file_put_contents($qrCodeFile, $qrCodeData);
            return $qrCodeUrl;
        } else {
            // 兜底：直接返回API图片URL，不再尝试保存
            return $apiUrl;
        }
    }
    
    // 获取文章封面
    private function getPostCover($post)
    {
        // 1. 优先使用自定义字段 cover
        if (isset($post->fields) && isset($post->fields->cover) && !empty($post->fields->cover)) {
            $cover = $post->fields->cover;
        } else {
            // 2. 尝试从内容中提取第一张图片（增强正则兼容性）
            $cover = '';
            if (!empty($post->content)) {
                if (preg_match('/<img[^>]+src=["\']?([^"\' >]+)["\' >]/i', $post->content, $matches)) {
                    $cover = $matches[1];
                }
            }
        }
        // 3. 如果没有找到图片，使用默认图片
        if (empty($cover)) {
            $cover = $this->options->themeUrl . '/assets/img/cover.png';
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
    
    // 实现接口要求的方法
    public function action() {}
}