<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DJI农业植保机用户中心</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css"/>
    <style type="text/css">
    {literal}
        .feedback-form {
            padding: 40px 80px;
        }
        .title {
            color: #707473;
            font-size: 18px;
        }
        .button {
            display: block;
            width: 120px;
            margin: 0 auto;
            padding: 8px 16px;
        }
    {/literal}
    </style>
</head>
<body>

    <div class="page-container">
        {include "left_side.tpl" nav="feedback"}

        <div class="main-side">
            {include "top.tpl" name="大疆创新科技有限公司"}

            <div class="main-body">

                <div class="feedback-form">
                    <p class="title">您的宝贵意见</p>
                    <form method="post" action="/user/feedback/" class="row" id="feedbackForm">
                        <div class="form-group col-lg-4">
                            <select class="form-control" name="type">
                                <option value="">请选择意见类型</option>
                                <option value ="agent">对代理商的意见</option>
                                <option value ="product">对产品的建议</option>
                            </select>
                        </div>

                         <div class="form-group col-lg-12">
                             <label>标题</label>
                             <input type="text" class="form-control" name="title" />
                         </div>

                         <div class="form-group col-lg-12">
                             <label>正文</label>
                             <textarea rows="5" class="form-control" name="message" ></textarea>
                         </div>
                         <div class="form-group col-lg-12">
                             <button type="submit" class="button">提交</button>
                         </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/public/js/externals/jquery-1.10.1.min.js"></script>
    <script type="text/javascript">
        (function() {
            var $feedbackForm = $('#feedbackForm');
            $feedbackForm.on('submit', function() {
                var url = $(this).attr('action');
                var params = {
                    isajax: 1
                };

                var hasError = false;

                $(this).find('[name]').each(function() {
                    var name = $(this).attr('name'),
                        value = $(this).val();

                    if (value == '') {
                        $(this).closest('.form-group').addClass('has-error');
                        hasError = true;
                    } else {
                        $(this).closest('.form-group').removeClass('has-error');
                    }

                    params[name] = value;
                });

                if (hasError) {
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params,
                    dataType: 'json',
                    success: function(resp) {
                        var status = resp['status'];
                        if (status == 200) {
                            $feedbackForm.find('[name]').val('');
                            alert('感谢你的反馈');
                        } else {
                            alert(resp['extra']['msg']);
                        }
                    },
                    error: function() {
                        alert('系统出错，请重试');
                    }
                });

                return false;
            });
        })();
    </script>
    {include "footer.tpl"}
</body>
</html>