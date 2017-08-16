<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>直播后台</title>

        <link rel="stylesheet" href="/css/normalize.min.css" />
        <link rel="stylesheet" href="/css/style.css" />
        <style type="text/css">
            .video-view {
                position: relative;
            }
            .video-view .video-view-player {
                width: 320px;
                height: 180px;
            }
            .video-view .video-src-edit-btn {
                position: absolute;
                right: 20px;
                bottom: 20px;
            }

            .video-src-edit-wrap {
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <header class="header">
            <nav class="nav">
                <h2>机位视频</h2>
                <a href='/adminlive/video'>直播讯道</a>
            </nav>
        </header>

        <div class="container">
            <div class="live-videos">
                <div class="video-views" id="videoViews"></div>
            </div>

            <div id="player"></div>
        </div>

        <div class="layer" id="layer">
            <div class="layer-mask"></div>

            <div class="layer-content">
                <div class="video-src-edit-wrap">
                    <h3 class="title"></h3>
                    <div class="form">
                        <div class="form-item">
                            <label>url地址</label>
                            <div class="form-item-inputs" input-name="url" input-placeholder="url地址"></div>
                        </div>
                        <div class="form-item">
                            <label>低码率视频url地址</label>
                            <div class="form-item-inputs" input-name="low_url" input-placeholder="低码率视频url地址"></div>
                        </div>
                        <div class="form-item">
                            <label>截屏图片地址</label>
                            <input type="text" class="form-input" name="screenshot" placeholder="截屏图片地址"/>
                        </div>
                        <div class="form-item">
                            <label for="live_checkbox">是否直播</label>
                            <input type="checkbox" id="live_checkbox" name="type" class="checkbox"/>
                        </div>
                        <div class="form-item">
                            <input type="hidden" name="id" />
                            <a href="javascript:;" class="btn btn-primary" action="confirm">确定</a>
                            <a href="javascript:;" class="btn btn-common" action="cancel">取消</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script id="tmpl_videoView" type="x-text/template">
            <div class="video-view">
                <h3 class="video-view-name">{literal}{{data.title}}{/literal}</h3>

                <div class="video-view-player" id="{literal}v_{{data.videoSource.id}}{/literal}"></div>

                <a href="javascript:;" class="video-src-edit-btn">修改数据源</a>
            </div>
        </script>

        <script type="text/javascript" src="/js/externals/hlsplayer/flowplayer-3.2.12.min.js"></script>
        <script type="text/javascript" src="/js/externals/hlsplayer/flowplayer.ipad-3.2.12.min.js"></script>

        <script type="text/javascript" src="/js/externals/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="/js/externals/template.js"></script>

        <script type="text/javascript" src="/js/adminlive/VideoSource.js"></script>
        <script type="text/javascript" src="/js/adminlive/VideoPlayer.js"></script>
        <script type="text/javascript" src="/js/adminlive/VideoSrcLayer.js"></script>
        <script type="text/javascript" src="/js/adminlive/VideoSourceHandler.js"></script>
        <script type="text/javascript">
        {literal}
            (function(window, $, undefined) {
        {/literal}
                var videoData = {$videos};
        {literal}
                var convertVideoSourceData = function(videoData) {
                    var videoSourceDataArr = [];

                    for (var i = 0; i < videoData.length; i++) {
                        var data = videoData[i];

                        var id = data['id'],
                            id_int = parseInt(id),
                            title = (id_int > 9 ? '录' + (id_int - 9) : id + '号') + '机位';

                        videoSourceDataArr.push(new VideoSourceData(title, data));
                    }

                    return videoSourceDataArr;
                };

                var videoSourceHandler = new VideoSourceHandler({
                    id: 'videoViews',
                    data: convertVideoSourceData(videoData),
                    templateId: 'tmpl_videoView'
                });
                videoSourceHandler.init();

            })(window, jQuery);
        {/literal}
        </script>
    </body>
</html>