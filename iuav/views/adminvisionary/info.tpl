<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>
        DJI/ DJIVisionary- DJI login page
    </title>
    <link href="/css/visionbase.css" rel="stylesheet"/>
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>

    <script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <style type="text/css">
        {literal}
        /* --- */
        .info-form {
            width: 680px;
            margin: 0 auto;
        }
        .info-form label {
            font-family: Open Sans;
            font-size: 15px;
        }
        .info-form .info-basic {
            margin-bottom: 20px;
        }


        {/literal}
    </style>
    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            $(".divmenu ul li").click(function () {
                $(this).css("color", "green");
            });
            $("#btn_add").click(function () {
                var objs = $(".trsociallink");
                var inputs = $(".trsociallink input");
                // var obj1=objs[0].style.display;
                var showcount = 0, hidden = 0;
                for (var i = 0; i < objs.length; i++) {
                    if (objs[i].style.display == "block") {
                        if (inputs[i].value == "") {
                            alert("please wirite");
                        }
                        showcount++;
                    } else {
                        hidden++;
                    }
                }
                if (showcount < 10) {
                    objs[showcount].style.display = "block";
                }
            });
        });
        {/literal}
    </script>
</head>
<body>
<div class="divmain">
    {include file="header.tpl" imageSrc="{if $userList}{$userList.photo}{/if}" title="{if $userList}{$userList.name}'s INFO{/if}"}

    {include file="menu.tpl" current="info"}

    <div class="container">

        <div class="info-form">
            <form method="POST" action="/adminvisionary/uploadinfo/" enctype="multipart/form-data">
                <input type="hidden" name="id" value="{$id}"/>

                <div class="info-basic row">
                    <div class="col-md-5">
                        <img src="{if $userList}{$userList.photo}/270x270{else}/images/infopage.png{/if}" class="profile"/>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" value="{if $userList}{$userList.name}{/if}"/>
                        </div>
                        <div class="form-group">
                            <label>Profile Photo</label>
                            <input type="file" name="picture"/>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="elite" value="1" {if $userList && $userList.elite eq "1"} checked {/if}> Elite
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Blo (Maximum 200 words)</label>
                    <textarea class="form-control" rows="5" maxlength="200" name="blo">{if $userList}{$userList.blo}{/if}</textarea>
                </div>

                <div class="form-group">
                    <label>DJI Gear (Maximum 100 words)</label>
                    <textarea class="form-control" rows="3" maxlength="100" name="dji_gear">{if $userList}{$userList.dji_gear}{/if}</textarea>
                </div>

                <div class="form-group">
                    <label>Highlight Quote (Maximum 120 words)</label>
                    <textarea class="form-control" rows="4" maxlength="120" name="quote">{if $userList}{$userList.quote}{/if}</textarea>
                </div>

                <div class="form-group row">
                    <label class="col-md-4">Date</label>
                    <span class="col-md-8">{if $userList}{$userList.created_at}{else}{$date}{/if}</span>
                </div>

                <div class="form-group center-block" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/adminvisionary/" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>