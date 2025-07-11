<div class="min-width-modal">
    <!-- 登录表单 -->
<form id="front-login-form" action="/index.php/ajaxlogin/" method="post">
    <div class="mb15">
        <label for="_front_login_username" class="form-label">用户名/邮箱</label>
        <input type="text" name="name" class="form-control form-control-sm" id="_front_login_username"
               required placeholder="请输入用户名或邮箱">
    </div>
    <div class="mb15">
        <label for="_front_login_password" class="form-label">密码</label>
        <input type="password" name="password" class="form-control form-control-sm" required
               id="_front_login_password" placeholder="请输入密码">
    </div>
    <div class="mb15 d-flex justify-content-center wh100">
        <button class="btn btn-ssm btn-primary mr5" type="submit">
            <i class="fa fa-right-to-bracket"></i> 立即登录
        </button>
    </div>
    <!-- 关键字段 -->
    <input type="hidden" name="referer" value="<?php $this->options->siteUrl(); ?>">
    <input type="hidden" name="_" value="<?php echo $this->request->get('_'); ?>">
</form>
</div>
