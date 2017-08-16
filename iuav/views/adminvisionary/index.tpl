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
        {literal}
        <style type="text/css">        
        html,body{
            height: 100%;
            margin: 0px;
            padding: 0px;
            background: white;
            text-align: center;
        }
        .vi_wrapper{
            height:100%;
        }
        .vi_header{
            height: 120px;
            background-color:white;
        }
        .vi_content{
            height:85%;
            background: white;
            text-align: center;
        }
        .vi_top{
            text-align: center;
            background: #F3F3F3;
            margin: 20px 10px;  
            height: 90%;
        } 
        .headimg{
            margin-top: 20px;
            margin-left: 10%;
            float: left;
        } 
        .div_imgadd{
            width: 90%;
            margin-right: 5%;
            margin-left: 5%;
           
        }
        .addviimg{   
            margin-left: 80%;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .vi_content table{
            border: 1px #F2F2F2;
            width: 80%;
            margin-right: 10%;
            margin-left: 10%;
            border-collapse: collapse;
        }
        .vi_content table thead{        
            background: #F3F3F3;
        }
        .vi_content table tbody td,.vi_content table thead td{
            height: 40px;
            background:#F2F2F2;border:solid 1px #CCCCCC 
        }
        .vi_content table tbody td{
            background: white;
            height: 140px;
        }
        .vi_content table tbody td a{
            color: #6F7473;
            text-decoration: none;
        }
        .div_pagebar{
            text-align: center;
            margin-bottom: 50px;
            margin-top: 50px;
        }
        .account{
            float: left;
            margin-top: 35px;
            margin-left: 20px;
        }
        .loginout{
            float: left;
            margin-top: 58px;
            margin-left: -80px;
        }
        .statusselect{
          float: left;
          margin-top: 5px;
          margin-left: 15%;
          width: 50%;
          cursor: pointer;
        }
        .visaveimg{
            cursor: pointer;
        }
       
        </style>
        <script type="text/javascript">
          function changeStatus (Userid) {
           var mData = {
              id : Userid,
              status : $("#status"+Userid).val()
            };           
           $.ajax({
                url:"/adminvisionary/infostatus/",
                type:'post',
                dataType:'json',
                data : mData,
                success:function(data) {                     
                   if (data.status == '200') {
                      alert("Success");
                      window.location.reload();  
                   };              
                }
              });
          }
        </script>
        {/literal}
    </head>
    
    <body>
        <div class="vi_wrapper">
            <div class="vi_header" align="center">
                <div class="vi_top">
                   <a href="/admin/"><img src="/images/vi_head.png" class="headimg"></a>
                   <a href="/admin/"><span class="account">Edit Account</span></a>
                   <a href="/admin/logout/"><span class="loginout">Login out({$username})</span></a>
                </div>
            </div>
            <div class="vi_content">
                <div class="div_imgadd">
                    <a href="/adminvisionary/info/"><img src="/images/addVi.png" class="addviimg"/></a>
                </div>
                <div >
                    <table>
                        <thead>
                            <td>id</td>
                            <td>profile photo</td>
                            <td>name</td>
                            <td>status</td>
                            <td>publish at</td>
                        </thead>
                        <tbody>
                           {foreach from=$userList item=user}
                            <tr>
                                <td>{$user.id}</td>
                                <td><a href="/adminvisionary/info/?id={$user.id}"><img src="{$user.photo}/270x270" class="profile-s"/></a></td>
                                <td><a href="personinfo.html">{$user.name}</a></td>
                                <td>
                                    <select class="statusselect" name="status{$user.id}" id='status{$user.id}'>
                                        <option value="deleted"   {if $user.status eq 'deleted'} selected="selected" {/if} >Deleted</option>
                                        <option value="published"  {if $user.status eq 'published'} selected="selected" {/if} >Published</option>
                                        <option value="draft"  {if $user.status eq 'draft'} selected="selected" {/if} >Draft</option>
                                    </select>
                                    <img src="/images/visave.png" class="visaveimg" onclick="changeStatus('{$user.id}')">
                                </td>
                                <td>{$user.created_at}</td>
                            </tr>
                           {/foreach}
                        </tbody>
                    </table>
                    <div class="div_pagebar" style="display:inline-block">
                        <ul class="pagination">
                          <li><a href="/adminvisionary/index/?page=1">首页</a></li>
                          <li><a href="/adminvisionary/index/?page={$page+1}">下一页</a></li>
                        
                        </ul>
                        
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>