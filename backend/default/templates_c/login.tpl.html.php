<?php echo $this->fetch('header.html'); ?>
<div id="top"> </div>
<form id="login" name="login" action=?g=backend&a=login" method="post">
    <div id="center">
        <div id="center_left"></div>
        <div id="center_middle">
            <div class="user">
                <label>用户名：
                    <input type="text" name="username" id="user" />
                </label>
            </div>
            <div class="user">
                <label>密　码：
                    <input type="password" name="password" id="pwd" />
                </label>
            </div>
            <div class="chknumber">
                <label>验证码：
                    <input name="chknumber" type="text" id="chknumber" maxlength="4" class="chknumber_input" />
                </label>
                <img src="./public/assets/images/checkcode.png" id="safecode" />
            </div>
        </div>
        <div id="center_middle_right"></div>
        <div id="center_submit">
            <div class="button"> <img src="./public/assets/images/dl.gif" width="57" height="20" onclick="form_submit()" > </div>
            <div class="button"> <img src="./public/assets/images/cz.gif" width="57" height="20" onclick="form_reset()"> </div>
        </div>
        <div id="center_right"></div>
    </div>
</form>
<?php echo $this->fetch('footer.html'); ?>