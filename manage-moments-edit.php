<?php
/** 初始化组件 */
Typecho_Widget::widget('Widget_Init');

/** 注册一个初始化插件 */
Typecho_Plugin::factory('admin/common.php')->begin();

Typecho_Widget::widget('Widget_Options')->to($options);
Typecho_Widget::widget('Widget_User')->to($user);
Typecho_Widget::widget('Widget_Security')->to($security);
Typecho_Widget::widget('Widget_Menu')->to($menu);

/** 初始化上下文 */
$request = $options->request;
$response = $options->response;
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs clearfix">
                    <li><a href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments.php'); ?>"><?php _e('瞬间列表'); ?></a></li>
                    <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments-edit.php'); ?>"><?php _e('撰写瞬间'); ?></a></li>
                </ul>
            </div>
        </div>

        <?php
            $prefix = $db->getPrefix();
            $moment = null;
            $isEdit = false;
            $momentId = 0;

            if (isset($request->id)) {
                $momentId = (int)$request->id;
                if ($momentId > 0) {
                    $moment = $db->fetchRow($db->select()->from($prefix . 'moments')->where('id = ?', $momentId));
                    if ($moment) {
                        $isEdit = true;
                    }
                }
            }

            $contentValue = $moment ? (string)$moment['content'] : '';
            $visibilityValue = $moment ? (string)$moment['visibility'] : 'PUBLIC';
            $pinnedValue = $moment ? (int)$moment['pinned'] : 0;
        ?>

        <style>
            .puock-moment-editor {
                overflow-x: hidden;
            }
            .puock-moment-editor #wmd-button-bar,
            .puock-moment-editor #wmd-editarea,
            .puock-moment-editor #wmd-preview {
                max-width: 100%;
                box-sizing: border-box;
            }
            .puock-moment-editor .editor {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
            }
            .puock-moment-editor .wmd-button-row {
                display: flex;
                flex-wrap: wrap;
                height: auto;
                margin: 0;
                padding: 0;
                gap: 4px;
                flex: 1 1 320px;
            }
            .puock-moment-editor .wmd-button-row li {
                margin-right: 0;
            }
            .puock-moment-editor .wmd-edittab {
                float: none;
                flex: 0 0 auto;
                margin-top: 0;
            }
            .puock-moment-editor #wmd-button-bar {
                padding: 10px 12px;
                border: 1px solid #E9E9E6;
                border-bottom: none;
                background: #F9F9F6;
                border-radius: 6px 6px 0 0;
                margin-bottom: 0;
            }
            .puock-moment-editor textarea#text {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
            }
            .puock-moment-editor #wmd-preview {
                border: 1px solid #E9E9E6;
                border-top: none;
                border-radius: 0 0 6px 6px;
                padding: 12px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                overflow-x: auto;
            }
            .puock-moment-editor #wmd-preview pre,
            .puock-moment-editor #wmd-preview code {
                white-space: pre-wrap;
                word-break: break-word;
            }
            .puock-moment-editor .puock-submit-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
        </style>

        <div class="row typecho-page-main typecho-post-area puock-moment-editor" role="form">
            <form action="<?php $security->index('/action/puock-moments'); ?>" method="post" name="write_moment">
                <div class="col-mb-12" role="main">
                        <input type="hidden" name="do" value="<?php echo $isEdit ? 'updateMoment' : 'insertMoment'; ?>" />
                        <input type="hidden" name="id" value="<?php echo (int)$momentId; ?>" />

                        <p>
                            <label for="text" class="sr-only"><?php _e('瞬间内容'); ?></label>
                            <textarea style="height: <?php $options->editorSize(); ?>px" autocomplete="off" id="text"
                                      name="content" class="w-100 mono"><?php echo htmlspecialchars($contentValue, ENT_QUOTES, $options->charset); ?></textarea>
                        </p>

                        <div class="row">
                            <div class="col-mb-12 col-tb-6">
                                <section class="typecho-post-option" role="application">
                                    <label for="visibility" class="typecho-label"><?php _e('可见性'); ?></label>
                                    <p>
                                        <select class="w-100" name="visibility" id="visibility">
                                            <option value="PUBLIC" <?php echo $visibilityValue === 'PUBLIC' ? 'selected' : ''; ?>><?php _e('公开'); ?></option>
                                            <option value="PRIVATE" <?php echo $visibilityValue === 'PRIVATE' ? 'selected' : ''; ?>><?php _e('私密'); ?></option>
                                        </select>
                                    </p>
                                </section>
                            </div>
                            <div class="col-mb-12 col-tb-6">
                                <section class="typecho-post-option" role="application">
                                    <label for="pinned" class="typecho-label"><?php _e('置顶'); ?></label>
                                    <p>
                                        <select class="w-100" name="pinned" id="pinned">
                                            <option value="0" <?php echo $pinnedValue === 0 ? 'selected' : ''; ?>><?php _e('否'); ?></option>
                                            <option value="1" <?php echo $pinnedValue === 1 ? 'selected' : ''; ?>><?php _e('是'); ?></option>
                                        </select>
                                    </p>
                                </section>
                            </div>
                        </div>

                        <p class="submit clearfix">
                            <span class="left">
                                <a class="btn puock-submit-btn" href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments.php'); ?>"><i class="i-caret-left"></i><?php _e('返回列表'); ?></a>
                            </span>
                            <span class="right">
                                <button type="submit" class="btn primary puock-submit-btn"><?php echo $isEdit ? _t('更新瞬间') : _t('发布瞬间'); ?></button>
                            </span>
                        </p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<?php if (!empty($options)): ?>
<script src="<?php $options->adminStaticUrl('js', 'hyperdown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'pagedown.js'); ?>"></script>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
<script>
$(document).ready(function () {
    var textarea = $('#text');
    if (!textarea.length || typeof Markdown === 'undefined' || typeof HyperDown === 'undefined') {
        return;
    }

    $('<div class="editor" id="wmd-button-bar" />').insertBefore(textarea.parent());
    var preview = $('<div id="wmd-preview" class="wmd-hidetab" />').insertAfter('#wmd-button-bar');

    var options = {};
    options.strings = {
        bold: '<?php _e('加粗'); ?> <strong> Ctrl+B',
        italic: '<?php _e('斜体'); ?> <em> Ctrl+I',
        link: '<?php _e('链接'); ?> <a> Ctrl+L',
        quote: '<?php _e('引用'); ?> <blockquote> Ctrl+Q',
        code: '<?php _e('代码'); ?> <pre><code> Ctrl+K',
        image: '<?php _e('图片'); ?> <img> Ctrl+G',
        olist: '<?php _e('数字列表'); ?> <ol> Ctrl+O',
        ulist: '<?php _e('普通列表'); ?> <ul> Ctrl+U',
        heading: '<?php _e('标题'); ?> <h1>/<h2> Ctrl+H',
        hr: '<?php _e('分割线'); ?> <hr> Ctrl+R',
        undo: '<?php _e('撤销'); ?> - Ctrl+Z',
        redo: '<?php _e('重做'); ?> - Ctrl+Y',
        redomac: '<?php _e('重做'); ?> - Ctrl+Shift+Z',
        fullscreen: '<?php _e('全屏'); ?> - Ctrl+J',
        exitFullscreen: '<?php _e('退出全屏'); ?> - Ctrl+E',
        fullscreenUnsupport: '<?php _e('此浏览器不支持全屏操作'); ?>'
    };

    var converter = new HyperDown(),
        editor = new Markdown.Editor(converter, '', options);

    converter.enableHtml(true);
    converter.enableLine(true);

    editor.hooks.set('insertImageDialog', function () {
        if ($('.wmd-prompt-dialog.puock-image-dialog').length) {
            $('.wmd-prompt-background.puock-image-bg').remove();
            return true;
        }

        var selection = (typeof textarea.getSelection === 'function') ? textarea.getSelection() : null;
        var alt = selection && selection.text ? selection.text : '';
        alt = (alt || '').replace(/\r?\n/g, ' ').replace(/\]/g, '\\]');

        var background = $('.wmd-prompt-background').last();
        if (!background.length) {
            background = $('<div class="wmd-prompt-background"></div>').appendTo(document.body);
        }
        background.addClass('puock-image-bg').css({
            position: 'fixed',
            left: 0,
            top: 0,
            width: '100%',
            height: '100%',
            zIndex: 1000
        });

        var dialog = $(
            '<div class="wmd-prompt-dialog puock-image-dialog">' +
                '<p><b><?php _e('插入图片'); ?></b></p>' +
                '<p><?php _e('请输入图片地址'); ?></p>' +
                '<form>' +
                    '<input type="text" class="text" autocomplete="off" placeholder="https://example.com/a.png" />' +
                    '<button type="submit" class="btn primary"><?php _e('确定'); ?></button>' +
                    '<button type="button" class="btn cancel"><?php _e('取消'); ?></button>' +
                '</form>' +
            '</div>'
        ).appendTo(document.body);

        function closeDialog() {
            dialog.remove();
            $('.wmd-prompt-background.puock-image-bg').remove();
        }

        background.on('click', closeDialog);
        dialog.find('button.cancel').on('click', closeDialog);

        dialog.on('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeDialog();
            }
        });

        dialog.find('form').on('submit', function (e) {
            e.preventDefault();
            var url = $.trim(dialog.find('input[type=text]').val() || '');
            if (!url) {
                closeDialog();
                return;
            }

            var markdown = '![' + alt + '](' + url + ')';
            if (typeof textarea.replaceSelection === 'function') {
                textarea.replaceSelection(markdown);
            } else {
                textarea.val((textarea.val() || '') + '\n' + markdown + '\n');
            }

            textarea.trigger('input');
            closeDialog();
        });

        var input = dialog.find('input[type=text]');
        input.focus();
        if (input[0]) {
            input[0].setSelectionRange(0, input.val().length);
        }

        return true;
    });

    if (typeof scrollableEditor === 'function') {
        reloadScroll = scrollableEditor(textarea, preview);
    }

    if (typeof DOMPurify !== 'undefined') {
        converter.hook('makeHtml', function (html) {
            html = html.replace('<p><!--more--></p>', '<!--more-->');

            html = html.replace(/<(iframe|embed)\\s+([^>]*)>/ig, function (all, tag, src) {
                if (src[src.length - 1] == '/') {
                    src = src.substring(0, src.length - 1);
                }
                return '<div class="embed"><strong>' + tag + '</strong> : ' + $.trim(src) + '</div>';
            });

            return DOMPurify.sanitize(html, {USE_PROFILES: {html: true}});
        });
    }

    editor.hooks.chain('onPreviewRefresh', function () {
        var images = $('img', preview), count = images.length;
        if (typeof reloadScroll !== 'function') {
            return;
        }

        if (count == 0) {
            reloadScroll(true);
        } else {
            images.bind('load error', function () {
                count--;
                if (count == 0) {
                    reloadScroll(true);
                }
            });
        }
    });

    editor.hooks.chain('commandExecuted', function () {
        textarea.trigger('input');
    });

    editor.run();

    $('.editor').prepend('<div class="wmd-edittab"><a href="#wmd-editarea" class="active"><?php _e('撰写'); ?></a><a href="#wmd-preview"><?php _e('预览'); ?></a></div>');
    $(textarea.parent()).attr('id', 'wmd-editarea');

    $('.wmd-edittab a').click(function () {
        $('.wmd-edittab a').removeClass('active');
        $(this).addClass('active');
        $('#wmd-editarea, #wmd-preview').addClass('wmd-hidetab');

        var selectedTab = $(this).attr('href');
        $(selectedTab).removeClass('wmd-hidetab');

        if (selectedTab == '#wmd-preview') {
            $('#wmd-button-row').addClass('wmd-visualhide');
        } else {
            $('#wmd-button-row').removeClass('wmd-visualhide');
        }

        $('#wmd-preview').outerHeight($('#wmd-editarea').innerHeight());
        return false;
    });
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
