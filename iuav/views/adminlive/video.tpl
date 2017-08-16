<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>直播后台</title>

        <link rel="stylesheet" href="/css/normalize.min.css" />
        <link rel="stylesheet" href="/css/style.css" />
        <style type="text/css">
            .header {
                position: relative;
            }

            .online-total-count {
                position: absolute;
                right: 20px;
                top: 30px;
            }

            .video-view {
                position: relative;
            }
            .video-view .video-view-online-count {
                position: absolute;
                left: 58px;
                bottom: -5px;

                color: #535353;
                font-size: 14px;
            }
            .video-view-player {
                position: relative;
            }
            .video-player-mask {
                position: absolute;
                left: 0;
                bottom: -4px;

                width: 85%;
                height: 25px;

                background-color: transparent;
            }

            #toggleImage {
                position: absolute;
                right: 20px;
                bottom: 0;
            }
        </style>
    </head>
    <body>
        <header class="header">
            <nav class="nav">
                <h2>直播讯道</h2>
                <a href='/adminlive/'>机位视频</a>
            </nav>

            <a href="javascript:;" class="btn btn-common" id="batchClose">关闭多路视角</a>
            <a href="javascript:;" class="btn btn-common" id="closeOutdoor">关闭外景机位(4-9)</a>
            <a href="javascript:;" class="btn btn-common" id="closeIndoor">关闭内景机位(1-3)</a>
            <a href="javascript:;" class="btn btn-common" id="toggleImage">打开图片展示</a>
            <a href="javascript:;" class="btn btn-emergency" id="emergency">应急视频按钮</a>

            <div class="online-total-count">
                <span>总在线人数:</span>
                <span>0</span>
            </div>
        </header>

        <div class="container">
            <div class="live-videos">

                <div class="video-views" id="videoViews"></div>
            </div>
        </div>

        <script id="tmpl_videoView" type="x-text/template">
        {literal}
            <div class="video-view">
                <h3 class="video-view-name">{{data.title}}</h3>

                <div class="video-view-player" id="v_{{data.id}}"></div>

                <div class="video-view-selector">
                    <ul>
                        {{each list as value i}}
                            <li>
                                <a href="javascript:;" class="seat-btn btn-channel" data-source="{{list[i].videoSource.id}}">{{list[i].title}}</a>
                            </li>
                        {{/each}}
                        <li>
                            <a href="javascript:;" class="seat-btn btn-refresh">刷新</a>
                        </li>
                    </ul>
                    {{if !data.mainShot}}
                        <a href="javascript:;" class="seat-btn btn-close">关闭</a>
                    {{else}}
                        <span class="status">视频播放中</span>
                    {{/if}}
                </div>

                <span class="video-view-online-count">在线人数: <span>0</span></span>
            </div>
        {/literal}
        </script>

        <script type="text/javascript" src="/js/externals/hlsplayer/flowplayer-3.2.12.min.js"></script>
        <script type="text/javascript" src="/js/externals/hlsplayer/flowplayer.ipad-3.2.12.min.js"></script>

        <script type="text/javascript" src="/js/externals/jquery-1.10.1.min.js"></script>
        <script type="text/javascript" src="/js/externals/template.js"></script>

        <script type="text/javascript" src="/js/adminlive/VideoSource.js"></script>
        <script type="text/javascript" src="/js/adminlive/ToggleButton.js"></script>
        <script type="text/javascript" src="/js/adminlive/VideoPlayer.js"></script>
        <script type="text/javascript" src="/js/adminlive/VideoViewHandler.js"></script>
        <script type="text/javascript">
        {literal}
            (function(window, $, undefined) {
        {/literal}
                var videoSources = {$videosUrl};
                var videoData = {$rooms};
        {literal}
                var convertVideoSourceData = function(videoSources) {
                    var videoSourcesDataArr = [];

                    var liveVideoSourcesDataArr = [],
                        recordVideoSourcesDataArr = [];

                    for (var i = 0; i < videoSources.length; i++) {
                        var vs = videoSources[i];
                        var vsType = vs['type'];

                        if (vsType == 0) {
                            liveVideoSourcesDataArr.push(new VideoSourceData(vs['id'], vs));
                        } else if (vsType == 1) {
                            var title = '录' + (recordVideoSourcesDataArr.length + 1);
                            recordVideoSourcesDataArr.push(new VideoSourceData(title, vs));
                        }
                    }

                    return videoSourcesDataArr.concat(liveVideoSourcesDataArr, recordVideoSourcesDataArr);
                };


                var videoViewHandler = new VideoViewHandler(videoData, convertVideoSourceData(videoSources));
                videoViewHandler.init();

                var viewsButton = new ToggleButton('batchClose', '多路视角'),
                    outdoorSeatsButton = new ToggleButton('closeOutdoor', '外景机位(4-9)'),
                    indoorSeatsButton = new ToggleButton('closeIndoor', '内景机位(1-3)'),
                    toggleImageButton = new ToggleButton('toggleImage', '图片展示');

                var viewsButtonClosed = true;

                for (var i = 0; i < videoData.length; i++) {
                    if (!videoData[i]['closed']) {
                        viewsButtonClosed = false;
                        break;
                    }
                }

                viewsButton.setClosed(viewsButtonClosed);
                viewsButton.setCallback(function(button) {
                    videoViewHandler.close(['3', '4', '5', '6'], button.closed, function() {
                        button.update();
                    });
                });

                outdoorSeatsButton.setCallback(function(button) {
                    toggleSeats([4,5,6,7,8,9], button.closed, function() {
                        button.update();
                    });
                });

                indoorSeatsButton.setCallback(function(button) {
                    toggleSeats([1,2,3], button.closed, function() {
                        button.update();
                    });
                });


                function toggleSeats(arr, closed, callback) {
                    var idToSourceMap = videoViewHandler.getIdToSourceMap();
                    var ids = [];

                    for (var k in idToSourceMap) {
                        if (k === '1') {
                            continue;
                        }

                        var source = parseInt(idToSourceMap[k]);
                        if (arr.indexOf(source) > -1) {
                            ids.push(k);
                        }
                    }

                    videoViewHandler.close(ids, closed, callback);
                }

                toggleImageButton.setClosed(!videoData[0]['closed']);
                toggleImageButton.setCallback(function(button) {
                    videoViewHandler.close(['1', '2', '3', '4', '5', '6'], !button.closed, function() {
                        button.update();

                        viewsButton.setClosed(!button.closed);
                    });
                });


                $('#emergency').click(function() {
                    videoViewHandler.switchChannel('1', '10');
                    videoViewHandler.close(['2', '3', '4', '5', '6'], true);

                    viewsButton.setClosed(true);
                });

                var $totalOnlineNum = $('.online-total-count span:last-child');

                function loadOnlineCount() {
                    $.ajax({
                        url: 'http://live.dji.com/livestat/statCurrentOnlineNum',
                        type: 'GET',
                        success: function(data) {
                            $totalOnlineNum.text(data['totalNum']);
                            videoViewHandler.updateOnlineNum(data['items']);
                        },
                        error: function() {
                            console.log('出错');
                        }
                    })
                }

                loadOnlineCount();
                setInterval(loadOnlineCount, 5000);
            })(window, jQuery);
        {/literal}
        </script>
    </body>
</html>