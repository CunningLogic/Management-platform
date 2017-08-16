
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

            {include file="header.tpl" title="{if $id == ""}New{else}Modify{/if} Image"}

            <div class="container">
                <form method="POST" action="/adminvisionary/uploadimage/" enctype="multipart/form-data" class="create-resource-form" id="form">
                    <input type="hidden" name='visi_user_id' value="{$visi_user_id}" />
                    <input type="hidden" name='id' value="{$id}" />

                    <div class="upload-image">
                        <img src="{if $imageInfo }{$imageInfo.zipurl}/270x270{else}/images/model.png{/if}" id="uploadImage"/>
                    </div>

                    <div class="form-group">
                        <label>Choose Image:</label>
                        <input type="file" class="form-control" name="picture" id="picture"/>
                        <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                    </div>

                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" class="form-control" name="title" value="{if $imageInfo}{$imageInfo.title}{/if}"/>
                        <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                    </div>

                    <div class="form-group">
                        <label>Location:</label>
                        <input type="text" class="form-control" name="location" value="{if $imageInfo}{$imageInfo.location}{/if}"/>
                        <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                    </div>

                    <div class="form-group">
                        <label>DJI Gear:</label>
                        <textarea class="form-control" rows="4" name="dji_gear">{if $imageInfo}{$imageInfo.dji_gear}{/if}</textarea>
                        <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                    </div>

                    <div class="form-group">
                        <label>Exif Info:</label>
                        <textarea class="form-control" rows="4" name="exif_info">{if $imageInfo}{$imageInfo.exif_info}{/if}</textarea>
                        <span class="glyphicon glyphicon-remove form-control-feedback" aria-hidden="true"></span>
                    </div>

                    <div class="form-group">
                        <label>Date:</label>
                        <p>{if $imageInfo}{$imageInfo.created_at}{else}{$date}{/if}</p>
                    </div>

                    <div class="form-group center-block" style="text-align: center;">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="/adminvisionary/" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <script type="text/javascript">
        {literal}
            (function(window, $, undefined) {

                function UploadPreview($fileInput, $preview) {
                    this.$fileInput = $fileInput;
                    this.$preview = $preview;

                    this._bindEvent();
                }
                UploadPreview.prototype = {
                    _bindEvent: function() {
                        var _this = this;
                        _this.$fileInput.on('change', function() {
                            _this._handleFile.call(_this, this);
                        });
                    },

                    _handleFile: function(file) {
                        var _this = this;

                        var files = file.files,
                            image = new Image();

                        if (files.length == 0) {
                            this.$preview.empty();
                            return;
                        }

                        if (window.URL) {
                            image.src = window.URL.createObjectURL(files[0]);
                            image.onload = function() {
                                window.URL.revokeObjectURL(this.src);
                            };
                            this._displayImage(image);
                        } else if (window.FileReader) {
                            var reader = new FileReader();
                            reader.readAsDataURL(files[0]);
                            reader.onload = function(e) {
                                image.src = this.result;
                                _this._displayImage.call(_this, image);
                            };
                        } else {
                            var nfile = document.selection.createRange().text;
                            document.selection.empty();
                            image.src = nfile;
                            this._displayImage(image);
                        }
                    },

                    _displayImage: function(image) {
                        this.$preview.empty().append(image);
                    }
                };

                new UploadPreview($('form input[type=file]'), $('.upload-image'));

                var $form = $('#form');
            {/literal}
                var id = '{$id}';
            {literal}

                $form.on('submit', function() {
                    var $inputs = $(this).find('input[name], textarea[name]');
                    var allowPost = true;

                    $inputs.each(function() {
                        $(this).parents('.form-group').removeClass('has-error has-feedback');

                        if (id !='' && $(this).attr('name') == 'picture') {
                            return true;
                        }

                        if (!$(this).is(':hidden') && !$(this).val()) {
                            $(this).parents('.form-group').addClass('has-error has-feedback');
                            allowPost = false;
                        }
                    });

                    return allowPost;
                });

                $('#uploadImage').click(function() {
                    $('#picture').click();
                })
            })(window, jQuery);
            {/literal}
        </script>
    </body>
</html>