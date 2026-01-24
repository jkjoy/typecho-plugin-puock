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
        <div class="row typecho-page-main manage-metas">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs clearfix">
                    <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments.php'); ?>"><?php _e('瞬间列表'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments-edit.php'); ?>"><?php _e('撰写瞬间'); ?></a></li>
                </ul>
            </div>

            <div class="col-mb-12" role="main">
                <?php
                    $prefix = $db->getPrefix();
                    $moments = $db->fetchAll(
                        $db->select()
                            ->from($prefix . 'moments')
                            ->where('rowStatus = ?', 'NORMAL')
                            ->order('pinned', Typecho_Db::SORT_DESC)
                            ->order('displayTs', Typecho_Db::SORT_DESC)
                    );
                ?>
                <form method="post" name="manage_moments" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些瞬间吗?'); ?>" href="<?php $security->index('/action/puock-moments?do=deleteMoment'); ?>"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="15"/>
                                <col width="80"/>
                                <col width=""/>
                                <col width="90"/>
                                <col width="70"/>
                                <col width="140"/>
                                <col width="140"/>
                                <col width="90"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th><?php _e('ID'); ?></th>
                                    <th><?php _e('内容'); ?></th>
                                    <th><?php _e('可见性'); ?></th>
                                    <th><?php _e('置顶'); ?></th>
                                    <th><?php _e('创建时间'); ?></th>
                                    <th><?php _e('更新时间'); ?></th>
                                    <th><?php _e('操作'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($moments)): ?>
                                <?php foreach ($moments as $moment): ?>
                                <tr id="moment-<?php echo (int)$moment['id']; ?>">
                                    <td><input type="checkbox" value="<?php echo (int)$moment['id']; ?>" name="id[]"/></td>
                                    <td><a href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments-edit.php&id=' . (int)$moment['id']); ?>" title="<?php _e('点击编辑'); ?>"><?php echo (int)$moment['id']; ?></a></td>
                                    <td><?php
                                        $preview = strip_tags((string)$moment['content']);
                                        $preview = Typecho_Common::subStr($preview, 0, 60, '...');
                                        echo htmlspecialchars($preview, ENT_QUOTES, $options->charset);
                                    ?></td>
                                    <td><?php echo htmlspecialchars((string)$moment['visibility'], ENT_QUOTES, $options->charset); ?></td>
                                    <td><?php echo ((int)$moment['pinned'] === 1) ? _t('是') : _t('否'); ?></td>
                                    <td><?php echo ((int)$moment['createdTs'] > 0) ? date('Y-m-d H:i', (int)$moment['createdTs']) : '-'; ?></td>
                                    <td><?php echo ((int)$moment['updatedTs'] > 0) ? date('Y-m-d H:i', (int)$moment['updatedTs']) : '-'; ?></td>
                                    <td>
                                        <div class="puock-action-cell">
                                            <a class="btn btn-xs puock-icon-btn" title="<?php _e('编辑'); ?>" href="<?php $options->adminUrl('extending.php?panel=Puock/manage-moments-edit.php&id=' . (int)$moment['id']); ?>"><i class="i-edit"></i><span class="sr-only"><?php _e('编辑'); ?></span></a>
                                            <a class="btn btn-xs operate-delete puock-icon-btn puock-danger-btn" title="<?php _e('删除'); ?>" lang="<?php _e('你确认要删除此瞬间吗?'); ?>" href="<?php $security->index('/action/puock-moments?do=deleteMoment&id=' . (int)$moment['id']); ?>"><i class="i-delete"></i><span class="sr-only"><?php _e('删除'); ?></span></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8"><h6 class="typecho-list-table-title"><?php _e('没有任何瞬间'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<style>
    .puock-action-cell { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .puock-icon-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .puock-danger-btn { color: #B94A48; }
    .puock-danger-btn:hover { background-color: #FBE3E4; }
    .puock-danger-btn:active, .puock-danger-btn.active { background-color: #FBC2C4; }
</style>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table');

        table.tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.operate-delete').click(function () {
            var t = $(this), href = t.attr('href');
            if (confirm(t.attr('lang'))) {
                window.location.href = href;
            }
            return false;
        });

        <?php if (isset($request->id)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
</script>
<?php include 'footer.php'; ?>
