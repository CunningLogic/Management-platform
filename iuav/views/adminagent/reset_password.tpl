<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>密码重置 - DJI农业机代理商管理平台</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/login.css"/>
</head>
<body>
    <div class="container">

        <div class="logo-wrap">
            <a href="/adminagent/login" class="logo"></a>
        </div>

        <div class="form-wrap">

            <div class="row form login-form">
                <div class="form-head col-sm-offset-3 col-sm-9">
                    <h3>重置密码</h3>
                </div>

                <div class="form-body">
                    <form method="post" action="/apiadminagent/resetpassword" class="form-horizontal" id="resetPasswordForm">
                        <input type="hidden" name="code" value="{$code}" />
                        <input type="hidden" name="datetime" value="{$datetime}" />
                        {if $error}
                         <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                               <div class="alert alert-danger">{$error}</div>

                            </div>
                            <div class="col-sm-offset-4 col-sm-9">
                                <p class="help-block">
                                    <a href="/adminagent/getpassword">忘记密码？</a>
                                </p>
                            </div>
                         </div>
                        {else}
                        <div class="form-group">
                            <label class="col-sm-4 control-label">新密码</label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="newpassword" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">确认密码</label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" name="retpassword"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">

                                <div role="alert" id="result"></div>
                                <button type="submit" class="btn btn-default btn-block">提交</button>
                            </div>
                        </div>
                        {/if}
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/public/js/externals/jquery-1.10.1.min.js"></script>
    <script type="text/javascript">
        var $form = $('#resetPasswordForm');
        var $result = $('#result').hide();
        $form.on('submit', function() {
            var url = $(this).attr('action');
            var params = {
            };

            $form.find('input').each(function() {
                params[$(this).attr('name')] = $(this).val();
            });

            $result.removeAttr('class').addClass('alert').hide();

            $.ajax({
                type: 'POST',
                url: url,
                data: params,
                success: function(resp) {
                    var className = '',
                            msg = '';

                    if (resp['status'] == 200) {
                        className = 'alert-success';
                        msg = '重置密码成功，请<a href="/adminagent/login" class="alert-link">登陆</a>'

                        $form.find('input[name=email]').attr('disabled', true);
                        $form.find('[type=submit]').remove();
                    } else {
                        className = 'alert-danger';
                        msg = resp['extra']['msg'];
                    }

                    $result.addClass(className).html(msg).show();
                },
                error: function() {
                    $result.addClass('alert-danger').text('服务出错，请重试').show();
                }
            })

            return false;
        });
    </script>
</body>
</html>