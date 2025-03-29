<div id="netflix-player-container" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1001;">
    <!-- Player Header -->
    <div class="player-header" style="position: absolute; top: 0; left: 0; right: 0; padding: 20px; display: flex; justify-content: space-between; z-index: 1002; background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%;">
        <div class="player-title" style="color: #fff; font-weight: bold; font-size: 1.2rem;">Now Playing</div>
        <button id="close-player" class="btn" style="color: #fff; background: none; border: none;">
      <i class="fas fa-times"></i>
    </button>
    </div>

    <!-- Player Controls -->
    <div class="player-controls" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; z-index: 1002; background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%;">
        <div class="progress-container" style="position: relative; height: 4px; background-color: rgba(255,255,255,0.1); cursor: pointer; margin-bottom: 15px;">
            <div class="buffer-bar" style="position: absolute; height: 100%; background-color: rgba(255,255,255,0.3); width: 0%;"></div>
            <div class="progress-bar" style="position: absolute; height: 100%; background-color: #E50914; width: 0%;"></div>
        </div>

        <div class="controls-row" style="display: flex; align-items: center; justify-content: space-between;">
            <div class="left-controls" style="display: flex; align-items: center;">
                <button id="play-pause" class="btn" style="color: #fff; background: none; border: none; font-size: 1.5rem;">
          <i class="fas fa-pause"></i>
        </button>
                <button id="volume" class="btn" style="color: #fff; background: none; border: none; margin-left: 15px;">
          <i class="fas fa-volume-up"></i>
        </button>
                <span id="time-display" style="color: #fff; margin-left: 15px; font-size: 0.9rem;">00:00 / 00:00</span>
            </div>

            <div class="right-controls">
                <button id="fullscreen" class="btn" style="color: #fff; background: none; border: none;">
          <i class="fas fa-expand"></i>
        </button>
            </div>
        </div>
    </div>
</div>

<video id="video" controls="false" width="640" height="360" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: #000; object-fit: contain;"></video>

<script>
        function playMedia(mediaId) {
        $.ajax({
            url: '/api/player.php',
            dataType: 'json',
            method: 'POST',
            data: {
                'action': 'getStream',
                'id': mediaId
            },
            success: async function(data) {
                if (!data.success) {
                    if (data.message !== undefined) {
                        new Notify({
                            status: 'error',
                            text: data.message,
                        });
                    }
                    return;
                }

                // Show player elements
                $('#netflix-player-container, #video').removeClass('d-none');
                $('#video').removeAttr('controls');

                const video = $('#video')[0];
                const videoSrc = data.data.url;
                const progressBar = $('.progress-bar');
                const bufferBar = $('.buffer-bar');

                // Initialize HLS playback
                if (Hls.isSupported()) {
                    const hls = new Hls({
                        autoStartLoad: true,
                        startLevel: -1,
                        maxBufferLength: 30,
                        maxBufferSize: 50 * 1000 * 1000,
                        maxBufferHole: 0.5,
                    });

                    hls.loadSource(videoSrc);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, function() {
                        video.play();
                    });

                    // Update buffer bar for HLS
                    hls.on(Hls.Events.BUFFER_UPDATED, function(event, data) {
                        if (video.duration > 0) {
                            const bufferedEnd = data.end;
                            const bufferPercent = (bufferedEnd / video.duration) * 100;
                            bufferBar.css('width', bufferPercent + '%');
                        }
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = videoSrc;
                    video.addEventListener('loadedmetadata', function() {
                        video.play();
                    });

                    // Update buffer bar for native HLS
                    video.addEventListener('progress', function() {
                        if (video.buffered.length > 0 && video.duration > 0) {
                            const bufferedEnd = video.buffered.end(video.buffered.length - 1);
                            const bufferPercent = (bufferedEnd / video.duration) * 100;
                            bufferBar.css('width', bufferPercent + '%');
                        }
                    });
                }

                // Player controls functionality
                $('#play-pause').on('click', function() {
                    if (video.paused) {
                        video.play();
                        $(this).html('<i class="fas fa-pause"></i>');
                    } else {
                        video.pause();
                        $(this).html('<i class="fas fa-play"></i>');
                    }
                });

                $('#fullscreen').on('click', function() {
                    video.requestFullscreen ? .() ||
                        video.webkitRequestFullscreen ? .() ||
                        video.msRequestFullscreen ? .();
                });

                $('#close-player').on('click', function() {
                    video.pause();
                    $('#netflix-player-container, #video').addClass('d-none');
                    progressBar.css('width', '0%');
                    bufferBar.css('width', '0%');
                    $('#time-display').text('00:00 / 00:00');
                });

                video.addEventListener('timeupdate', function() {
                    if (video.duration > 0) {
                        const playedPercent = (video.currentTime / video.duration) * 100;
                        progressBar.css('width', playedPercent + '%');

                        const currentMinutes = Math.floor(video.currentTime / 60);
                        const currentSeconds = Math.floor(video.currentTime % 60);
                        const totalMinutes = Math.floor(video.duration / 60);
                        const totalSeconds = Math.floor(video.duration % 60);

                        $('#time-display').text(
                            `${currentMinutes.toString().padStart(2, '0')}:${currentSeconds.toString().padStart(2, '0')} / ` +
                            `${totalMinutes.toString().padStart(2, '0')}:${totalSeconds.toString().padStart(2, '0')}`
                        );
                    }
                });

                $('.progress-container').on('click', function(e) {
                    const offset = $(this).offset();
                    const clickPosition = e.pageX - offset.left;
                    const percentage = clickPosition / $(this).width();
                    video.currentTime = video.duration * percentage;
                });

                $('#volume').on('click', function() {
                    video.muted = !video.muted;
                    $(this).html(video.muted ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>');
                });

                // Controls visibility logic
                const hideControls = () => $('.player-header, .player-controls').fadeOut();
                let controlsTimeout = setTimeout(hideControls, 3000);

                $('#netflix-player-container, #video').on('mousemove', () => {
                    $('.player-header, .player-controls').fadeIn();
                    clearTimeout(controlsTimeout);
                    controlsTimeout = setTimeout(hideControls, 3000);
                });
            }
        });
    }
</script>