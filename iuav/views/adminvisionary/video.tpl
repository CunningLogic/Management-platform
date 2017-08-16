
<!DOCTYPE html>
<html> 
    <head lang="en">
        <meta charset="UTF-8">
        <title>
            DJI/ DJIVisionary- DJI login page
        </title>
        <link href="/css/visionbase.css" rel="stylesheet"/>
        <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

        <script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script>
        <style type="text/css">
        {literal}
            .create-resource-form {
                width: 780px;
                margin: 0 auto;
            }
            .create-resource-form label {
                font-family: Open Sans;
                font-size: 15px;
            }
            .upload-image {
                height: 240px;
                line-height: 240px;
                margin-bottom: 20px;
                background-color: #C9CBCA;
                vertical-align: middle;
                text-align: center;
            }
            .upload-image img {
                max-width: 100%;
                max-height: 100%;
            }
        {/literal}
        </style>
    </head>
    
    <body>
        <div class="divmain">
            {include file="header.tpl" title="New Video" id="{$visi_user_id}"}

            <div class="container">
                <form method="POST" id="uploadForm" class="create-resource-form">
                    <input type="hidden" name='visi_user_id' value="{$visi_user_id}" />
                    <input type="hidden" name="id" value="{$id}" />
                    <input type="hidden" name="watermark" value="{$watermark}" />

                    <div class="form-group">
                        <label>Choose Video:</label>
                        <input type="file" class="form-control" name="video_file" id="videoFile"/>
                    </div>

                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" class="form-control" name="title"/>
                    </div>

                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" class="form-control" name="location"/>
                    </div>

                    <div class="form-group">
                        <label>DJI Gear:</label>
                        <textarea class="form-control" rows="4" name="dji_gear"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Exif Info:</label>
                        <textarea class="form-control" rows="4" name="exif_info"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Date:</label>
                        <p>{$date}</p>
                    </div>

                    <div class="form-group center-block" style="text-align: center;">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="/adminvisionary/" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <script type="text/javascript" src="/js/externals/ajaxfileupload.js"></script>
        <script type="text/javascript">

            (function($) {

            {literal}
                function loadInitData(mData, callback) {
                    $.ajax({
                        url: "/adminvisionary/uploadinitvideo",
                        type: 'post',
                        dataType: 'json',
                        data : mData,
                        success: function(data) {
                            if (data['status'] == 0) {
                                var _data = {};
                                _data['visi_user_id'] = mData['visi_user_id'];
                                _data['upload_token'] = data['upload_token'];
                                _data['upload_url'] = data['upload_url'];
                                _data['watermark'] = mData['watermark'];

                                callback && callback(_data);
                            }
                        },
                        error: function() {
                            throw new Error('Error when load init data..');
                        }
                    });
                }

                function uploadVideo(mData) {
                    $.ajaxFileUpload({
                        url: mData['upload_url'],
                        secureuri: true,
                        fileElementId: 'videoFile',
                        dataType: 'text',
                        data: {
                            watermark: mData['watermark'],
                            upload_token: mData['upload_token'],
                            file_md5: '',
                            file_size: 0
                        },
                        success: function(data) {
                            location.href = '/adminvisionary/videoindex/?id=' + mData['visi_user_id'];
                        },
                        error: function(data, status, e) {
                            console.log('error');
                            console.log(data);
                            console.log(e);
                        }
                    })
                }

                var $form = $('#uploadForm');

                $form.on('submit', function() {
                    var $inputs = $(this).find('input[name], textarea[name]');
                    var mData = {};
                    var $emptyInputs = [];

                    $inputs.each(function() {
                        var name = $(this).attr('name'),
                            value = $(this).val();

                        $(this).parents('.form-group').removeClass('has-error');

                        if (!$(this).is(':hidden') && !value) {
                            $emptyInputs.push($(this));
                        }
                    });

                    if ($emptyInputs.length > 0) {
                        $.each($emptyInputs, function() {
                            $(this).parents('.form-group').addClass('has-error');
                        });
                        return false;
                    }


                    mData['upload'] = !!mData['video_file'] ? 1 : 0;
                    delete mData['video_file'];

                    loadInitData(mData, function(data) {
                        uploadVideo(data);
                    });

                    return false;
                });
            {/literal}
            })(jQuery);

        </script>
    </body>
</html>