<!DOCTYPE html>
<html> 
    <head lang="en">
        <meta charset="UTF-8">
        <title>
            DJI/ DJIVisionary- DJI login page
        </title>
        <link href="/css/visionbase.css" rel="stylesheet" />
        <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>

        <script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script>
        {literal}
        <style type="text/css">        
        /*操作按钮样式*/      
        .div_btn{
            width: 80%;
            height: 50px;
            margin-top: 20px;
            /*border: 1px solid;*/
            text-align: center;
            margin-left: 10%;
            margin-right: 10%;
        }
        .uploadimg{
            float: left;
            margin-top: 2%;
            cursor: pointer;
        }
        .previewimg{
            float: right;
            margin-top: 2%;
            cursor: pointer;
        }
        /*end*/

        /*图片展示区域样式*/
        .div_imgarea{
            margin-right: 10%;
            margin-left: 10%;
            width: 80%;
            height: 80%;
            /*background: red;*/
            text-align: center;;
        }
        .div_imgarea table{
            width: 100%;
            /*border: 1px solid #ddd;*/
        }
        
        /*end*/
         .div_pagebar{
            text-align: center;
            margin-bottom: 50px;
            margin-top: 20px;
        }
        .statusselect{
          width: 12%;
        }
        </style>
        <script type="text/javascript">
        $(document).ready(function(){

            function deleteImage(id, callback) {
                $.ajax({
                    url: '/adminvisionary/imagestatus',
                    type: 'POST',
                    data: {
                        id: id,
                        status: 'deleted'
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data['status'] == 200) {
                            callback && callback();
                        }
                    },
                    error: function() {
                        console.log('删除出错...');
                    }
                });
            }

            $('.div_img').each(function() {
                var $this = $(this);

                var $delete = $this.find('.div_delete').hide(),
                        $edit = $this.find('.div_edit').hide();

                $this.hover(function() {
                    $delete.show();
                    $edit.show();
                }, function() {
                    $delete.hide();
                    $edit.hide();
                });

                $delete.find('a').click(function() {
                    deleteImage($(this).data('id'), function() {
                        $this.parent('td').remove();
                    });
                });
            });
        });
        function SelectAll(){
                var btnstatu=document.getElementById("allcheck").checked;
                var checkboxs=document.getElementsByName("imagebox");
                for(var i=0;i<checkboxs.length;i++){
                    var e=checkboxs[i];
                    e.checked=btnstatu;
                }
        }
        function Reverse(){
                var checkboxs=document.getElementsByName("imagebox");
                for(var i=0;i<checkboxs.length;i++){
                    var e=checkboxs[i];
                    e.checked=!e.checked;
                }
        }

        </script>
        {/literal}
    </head>
    <body>

        <div class="divmain">

            {include file="header.tpl" imageSrc="{if $userInfo }{$userInfo.photo}{/if}" title="Image"}

            {include file="menu.tpl" current="image"}

            <div class="container">
                <a href="/adminvisionary/image/?visi_user_id={$id}" class="btn btn-primary">Upload New Image</a>

                <div id="resource">
                    <div class="resources-list clearfix">
                        <ul>
                            {foreach from=$userList key="mykey" item=user}
                                <li>
                                    <div class="resource">
                                        <img src="{$user.zipurl}/270x270" />
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="checkbox" data-id="{$user.id}" />{$user.title}
                                        </label>
                                    </div>
                                    <p>{$user.updated_at}</p>

                                    <div class="operation">
                                        <a href="javascript:;" class="btn btn-default btn-sm trash" data-id="{$user.id}"><span class="glyphicon glyphicon-trash"></span></a>
                                        <a href="/adminvisionary/image/?visi_user_id={$id}&id={$user.id}" class="btn btn-default btn-sm pencil"><span class="glyphicon glyphicon-pencil"></span></a>
                                    </div>
                                </li>
                            {/foreach}
                        </ul>
                    </div>

                    <div class="row center-block resources-operation" id="resourceOperation" style="width: 360px;">
                        <div class="col-xs-4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="resourceRadio"/>Select All
                                </label>
                            </div>
                        </div>

                        <div class="col-xs-5">
                            <select class="form-control" name="operation">
                                <option value="deleted">Delete</option>
                                <option value="published">Publish</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-xs-3">
                            <button type="button" class="btn btn-primary btn-sm" action-type="submit">Submit</button>
                        </div>
                    </div>

                    <div class="div_pagebar center-block" style="width: 100%;">
                        <ul class="pagination">
                            <li><a href="/adminvisionary/index/?page=1">首页</a></li>
                            <li><a href="/adminvisionary/index/?page={$page+1}">下一页</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="/js/adminvisionary/Resource.js"></script>
        <script type="text/javascript">
            (function(window, $, undefined) {
                $('#resource').resList();
            })(window, jQuery);
        </script>
    </body>

</html>