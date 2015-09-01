<?php echo $this->fetch('header.tpl.html'); ?>
<div class="ch-container">
    <div class="row">

        <div class="row">
            <div class="col-md-12 center login-header">
                <h2>Welcome to Charisma</h2>
            </div>
            
        </div>

        <div class="row">
            <div class="well col-md-5 center login-box">
                <div class="alert alert-info">
                    Please login with your Username and Password.
                </div>
                <form class="form-horizontal" action="?m=default&c=default&g=backend&a=login" method="post">
                    <fieldset>
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user red"></i></span>
                            <input type="text" class="form-control" placeholder="Username" name="userName">
                        </div>
                        <div class="clearfix"></div><br>

                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock red"></i></span>
                            <input type="password" class="form-control" placeholder="Password" name="password">
                        </div>
                        <div class="clearfix"></div>

                        <div class="input-prepend">
                            <label class="remember" for="remember"><input type="checkbox" id="remember"> Remember me</label>
                        </div>
                        <div class="clearfix"></div>

                        <p class="center col-md-5">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </p>
                    </fieldset>
                </form>
            </div>
            
        </div>
    </div>

</div>
<?php echo $this->fetch('footer.tpl.html'); ?>