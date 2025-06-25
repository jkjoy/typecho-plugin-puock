<?php
header('Content-Type: text/html; charset=UTF-8');
?>
    <div class="post-poster">
        <div class="post-poster-main" id="post-poster-main">
            <div class="cover">
                <img crossOrigin="anonymous" src="<?php echo $cover; ?>" alt="poster">
            </div>
            <div class="content">
                <p class="title mt20 fs16"> </p>
                <p class="excerpt text-3line fs14 mt20 c-sub"> </p>
                <div class="info mt20">
                    <img class="qrcode" src="<?php echo $qrCodePath; ?>">
                    <?php if (!empty($pluginConfig->site_logo)): ?>
                    <img class="logo" src="<?php echo $pluginConfig->site_logo; ?>" alt="logo">
                    <?php endif; ?>
                </div>
                <p class="tip c-sub fs12 mt20 p-flex-center"><i class="fas fa-qrcode"></i>&nbsp;长按识别二维码查看文章内容</p>
            </div>
        </div>
    </div>
   
    <script>
        $(function () {
            const i = window.Puock.startLoading();
            html2canvas(document.querySelector("#post-poster-main"), {
                allowTaint: true,
                useCORS: true,
                backgroundColor:'#ffffff'
            }).then(canvas => {
                const el = $("#post-poster-main");
                el.show();
                el.html("<img class='result' src='" + canvas.toDataURL("image/png") + "' '>");
                window.Puock.stopLoading(i);
            }).catch(err => {
                console.error(err)
                window.Puock.toast("生成海报失败，请到Console查看错误信息", TYPE_DANGER);
            });
        })
    </script>
 