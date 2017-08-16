<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>代理商管理平台</title>

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
                    <p class="title">您对我们产品的宝贵意见</p>
                    <form class="row">
                        <div class="form-group col-lg-4">
                            <select class="form-control">
                                <option>请选择意见类型</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-12">
                            <textarea rows="5" class="form-control"></textarea>
                        </div>
                        <div class="form-group col-lg-12">
                            <button type="button" class="button">提交</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript" data-main="/public/js/index.js?v=1" src="/public/js/externals/require-2.1.11.min.js"></script>
    <script type="text/javascript">

    </script>
    {include "footer.tpl"}
</body>
</html>