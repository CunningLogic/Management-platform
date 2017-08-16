<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>忘记密码 - DJI农业机代理商管理平台</title>

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
                    <h3>忘记密码</h3>
                </div>

                <div class="form-body">
                    <form method="post" action="/apiadminagent/getpassword" class="form-horizontal" id="forgetPasswordForm">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="email"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <div role="alert" id="result"></div>

                                <button type="submit" class="btn btn-default btn-block">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/public/js/externals/jquery-1.10.1.min.js"></script>
    <script type="text/javascript">
        var $form = $('#forgetPasswordForm');
        var $result = $('#result').hide();

        $form.on('submit', function() {
            var url = $(this).attr('action');
            var params = {
                email: $(this).find('input[name=email]').val()
            };

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
                        msg = '密码重置的链接已经发到您邮箱 ' + params.email;

                        $form.find('input[name=email]').attr('disabled', true);
                        $form.find('[type=submit]').remove();
                    } else {
                        className = 'alert-danger';
                        msg = resp['extra']['msg'];
                    }

                    $result.addClass(className).text(msg).show();
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