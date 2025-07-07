        <div class="p-flex-sbc">
            <?php if (!empty($pluginConfig->alipay_qr)): ?>
            <div class="mr10" id="reward-alipay">
                <img src="<?php echo $pluginConfig->alipay_qr; ?>" style="width: 140px" alt="支付宝赞赏" title="支付宝赞赏" data-bs-toggle="tooltip"/>
                <p class="mt10 text-center fs12"><i class="fab fa-alipay"></i>&nbsp;请使用支付宝扫一扫</p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($pluginConfig->wechat_qr)): ?>
            <div id="reward-wx">
                <img src="<?php echo $pluginConfig->wechat_qr; ?>" style="width: 140px" alt="微信赞赏" title="微信赞赏" data-bs-toggle="tooltip"/>
                <p class="mt10 text-center fs12"><i class="fab fa-weixin"></i>&nbsp;请使用微信扫一扫</p>
            </div>
            <?php endif; ?>
        </div>