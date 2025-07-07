<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$options = Typecho_Widget::widget('Widget_Options');

// 定义表情数据数组
$smileys = [
    [':?:', 'doubt.png', '疑问', '疑问'],
    [':razz:', 'razz.png', '调皮', '调皮'],
    [':sad:', 'sad.png', '难过', '难过'],
    [':evil:', 'evil.png', '抠鼻', '抠鼻'],
    [':naughty:', 'naughty.png', '顽皮', '顽皮'],
    [':!:', 'scare.png', '吓', '吓'],
    [':smile:', 'smile.png', '微笑', '微笑'],
    [':oops:', 'oops.png', '憨笑', '憨笑'],
    [':neutral:', 'neutral.png', '亲亲', '亲亲'],
    [':cry:', 'cry.png', '大哭', '大哭'],
    [':mrgreen:', 'mrgreen.png', '呲牙', '呲牙'],
    [':grin:', 'grin.png', '坏笑', '坏笑'],
    [':eek:', 'eek.png', '惊讶', '惊讶'],
    [':shock:', 'shock.png', '发呆', '发呆'],
    [':???:', 'bz.png', '撇嘴', '撇嘴'],
    [':cool:', 'cool.png', '酷', '酷'],
    [':lol:', 'lol.png', '偷笑', '偷笑'],
    [':mad:', 'mad.png', '咒骂', '咒骂'],
    [':twisted:', 'twisted.png', '发怒', '发怒'],
    [':roll:', 'roll.png', '白眼', '白眼'],
    [':wink:', 'wink.png', '鼓掌', '鼓掌'],
    [':idea:', 'idea.png', '想法', '想法'],
    [':despise:', 'despise.png', '蔑视', '蔑视'],
    [':celebrate:', 'celebrate.png', '庆祝', '庆祝'],
    [':watermelon:', 'watermelon.png', '西瓜', '西瓜'],
    [':xmas:', 'xmas.png', '圣诞', '圣诞'],
    [':warn:', 'warn.png', '警告', '警告'],
    [':rainbow:', 'rainbow.png', '彩虹', '彩虹'],
    [':loveyou:', 'loveyou.png', '爱你', '爱你'],
    [':love:', 'love.png', '爱', '爱'],
    [':beer:', 'beer.png', '啤酒', '啤酒']
];
?>

<div id="smiley" class="animate bounce" style="max-width: 290px">
    <?php foreach ($smileys as $smiley): ?>
    <div class="smiley-item">
        <img data-id="<?php echo $smiley[0]; ?>" 
             src="<?php echo $options->themeUrl; ?>/assets/img/smiley/<?php echo $smiley[1]; ?>" 
             class="smiley-img" 
             alt="<?php echo $smiley[0]; ?>-<?php echo $smiley[2]; ?>" 
             title="<?php echo $smiley[3]; ?>"/>
    </div>
    <?php endforeach; ?>
</div>