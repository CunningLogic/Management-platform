<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>DJI农业植保机用户中心</title>

    <link rel="stylesheet" type="text/css" href="/public/css/externals/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/public/css/common.css"/>
    <style type="text/css">
    {literal}
        .modal-evaluate {}
        .modal-evaluate .modal-header {
            border-bottom: none;
        }
        .modal-evaluate .form-button-group {
            padding-top: 20px;
        }
        .modal-evaluate .info-button {
        }
        .modal-evaluate .form {
            padding: 20px;
        }
    {/literal}
    </style>
</head>
<body>

    <div class="page-container">
        {include "left_side.tpl" nav="notice"}

        <div class="main-side">
            {include "top.tpl"}

            <div class="main-body">

                <div class="list-section manamgement-list">
                    <div class="list-section-head">
                       
                    </div>
                    <div class="list-section-body">
                        <table class="table table-striped table-without-border apply-table" align="center">
                            <thead>
                                <tr>
                                   <th>序号</th>
                                    <th>标题</th>
                                    <th>内容</th>
                                    <th>日期</th>  
                                </tr>
                            </thead>
                            <tbody>                                                        
                                {if isset($data)}
                                    {foreach from=$data key="mykey" item=activedata}
                                        <tr>
                                            <td>{$activedata.id|escape:"html"}</td>
                                            <td>{$activedata.title|escape:"html"}</td>
                                            <td>{$activedata.content|escape:"html"}</td>
                                            <td>{$activedata.updated_at|escape:"html"}</td>                                        
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                        <nav>
                            <ul class="pagination">
                                {if isset($page_count) && $page_count > 1}


                                    <li{if ($page - 1) < 1} class="disabled"{/if}>
                                        {if ($page - 1) >= 1}
                                            <a href="{$base_url}&page={$page - 1}" aria-label="Previous" >
                                                <span aria-hidden="true">上一页</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">上一页</span>
                                        {/if}
                                    </li>

                                    {for $p=1 to $page_count}
                                        <li{if $p == $page} class="active"{/if}>
                                            <a href="{$base_url}&page={$p}" >{$p}</a>
                                        </li>
                                    {/for}
                                    
                                    <li{if ($page + 1) > $page_count} class="disabled"{/if}>
                                        {if ($page + 1) <= $page_count}
                                            <a href="{$base_url}&page={$page+1}" aria-label="Next">
                                                <span aria-hidden="true">下一页</span>
                                            </a>
                                        {else}
                                            <span aria-hidden="true">下一页</span>
                                        {/if}
                                    </li>
                                {/if}
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-evaluate" id="evaluateModal" tabindex="-1" role="dialog" aria-labelledby="evaluateModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="/user/evaluate" class="form" id="evaluateForm">
                        <div class="form-group">
                            <label>维修总体满意度</label>
                            <div class="star">
                                <div class="icons">
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                </div>
                                <input type="hidden" name="totality" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>维修速度</label>
                            <div class="star">
                                <div class="icons">
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                </div>
                                <input type="hidden" name="speed" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>维修质量</label>
                            <div class="star">
                                <div class="icons">
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                </div>
                                <input type="hidden" name="quality" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>服务态度</label>
                            <div class="star">
                                <div class="icons">
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                    <i class="icon-star"></i>
                                </div>
                                <input type="hidden" name="attitude" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>其他</label>
                            <textarea class="form-control" rows="5" name="message"></textarea>
                        </div>
                        <div class="form-group form-button-group text-center">
                            <input type="hidden" name="caseno" value=""/>
                            <p class="info-button">
                                <button type="submit" class="button button-default">确定提交</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/public/js/externals/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="/public/js/externals/bootstrap.min.js"></script>
    <script type="text/javascript">
        (function() {
            var status = 0; // 0: unavailable
            var $evaluateModal = $('#evaluateModal');

            function updateStar($starGroup, selectedCount) {
                $starGroup.each(function(i) {
                    if (i <= selectedCount - 1) {
                        $(this).removeClass('icon-star-off');
                    } else {
                        $(this).addClass('icon-star-off');
                    }
                });
            }

            function initStars(data) {
                $evaluateModal.find('.star').each(function() {
                    var $icons = $(this).find('.icons'),
                        $input = $(this).find('input');
                    var $iconGroup = $icons.children('.icon-star');

                    var value = data && data[$input.attr('name')] ? data[$input.attr('name')] : 5;
                    updateStar($iconGroup, value);
                    $input.val(value);

                    $icons.on('click', '.icon-star', function() {
                        if (status == 0) {
                            return false;
                        }

                        var index = $iconGroup.index($(this)[0]);
                        updateStar($iconGroup, index + 1);
                        $input.val(index + 1);
                    });
                });
            }
            initStars();

            $evaluateModal.find('.star').each(function() {
                var $icons = $(this).find('.icons'),
                    $input = $(this).find('input').val(5);

                var $iconGroup = $icons.children('.icon-star');

                $icons.on('click', '.icon-star', function() {
                    if (status == 0) {
                        return false;
                    }

                    var index = $iconGroup.index($(this)[0]);
                    $iconGroup.each(function(i) {
                        if (i <= index) {
                            $(this).removeClass('icon-star-off');
                        } else {
                            $(this).addClass('icon-star-off');
                        }
                    });

                    $input.val(index + 1);
                });
            });

            var $form = $('#evaluateForm');
            $form.on('submit', function() {
                var url = $(this).attr('action');
                var params = {};
                $(this).find('[name]').each(function() {
                    var name = $(this).attr('name'),
                        value = $(this).val();

                    params[name] = value;
                });

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: params,
                    dataType: 'json',
                    success: function(resp) {
                        var status = resp['status'];
                        if (status == 200) {
                            location.href = location.href;
                            $evaluateModal.modal('hide');
                        }
                    },
                    error: function() {}
                });

                return false;
            });

            $evaluateModal.on('show.bs.modal', function(e) {
                var $button = $(e.relatedTarget);
                var data = $button.data('data');
                var caseno = $button.data('caseno');

                $form.find('[name=caseno]').val(caseno);
                if (!!data) {
                    status = 0;
                    $evaluateModal.find('.icon-star').css({ cursor: 'default' });
                    $form.find('[name=message]').attr('readonly', 'readonly').val(data['message']);
                    $form.find('.form-button-group').hide();
                } else {
                    status = 1;
                    $evaluateModal.find('.icon-star').removeAttr('style');
                    $form.find('[name=message]').removeAttr('readonly').val('');
                    $form.find('.form-button-group').show();
                }
                initStars(data);
            });
        })();
    </script>
    {include "footer.tpl"}
</body>
</html>