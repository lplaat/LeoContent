<style>
    body {
        background-color: #f8f9fa;
        margin: 0;
    }

    #netflix-player-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1001;
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }

    #netflix-player-container.hidden, #video-player.hidden {
        display: none;
        opacity: 0;
    }

    #video-player {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        background: #000;
        object-fit: contain; /* Keeps aspect ratio */
    }

    .player-overlay {
        position: absolute;
        left: 0;
        right: 0;
        padding: 1.5rem 2rem;
        z-index: 1002;
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }

    .player-overlay.hidden {
            opacity: 0;
            pointer-events: none; /* Prevent interaction when hidden */
    }


    .player-header {
        top: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
    }

    .player-title {
        color: #fff;
        font-weight: bold;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .player-controls {
        bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
    }

    .progress-container {
        position: relative;
        height: 6px; /* Slightly thicker */
        background-color: rgba(255, 255, 255, 0.2);
        cursor: pointer;
        margin-bottom: 1rem;
        border-radius: 3px;
        transition: height 0.1s ease-in-out;
    }
    .progress-container:hover {
        height: 8px; /* Enlarge slightly on hover */
    }
    .progress-container:hover .progress-bar {
            box-shadow: 0 0 5px #e50914; /* Glow effect */
    }


    .buffer-bar,
    .progress-bar {
        position: absolute;
        top:0;
        left: 0;
        height: 100%;
        border-radius: 3px;
        transition: width 0.1s linear; /* Smooth progress update */
    }

    .buffer-bar {
        background-color: rgba(255, 255, 255, 0.4);
        z-index: 1;
    }

    .progress-bar {
        background-color: #E50914; /* Netflix Red */
        z-index: 2;
    }

    .controls-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .left-controls, .right-controls {
        display: flex;
        align-items: center;
    }

    .player-btn {
        color: #fff;
        background: none;
        border: none;
        font-size: 1.4rem; /* Slightly larger icons */
        padding: 0.5rem;
        margin: 0 0.5rem; /* Consistent spacing */
        opacity: 0.85;
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
    }

    .player-btn:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .player-btn:focus {
        outline: none;
        box-shadow: none;
    }
    /* Spinner Icon */
    .player-btn .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }


    #time-display {
        color: #fff;
        margin-left: 1rem;
        font-size: 0.95rem;
        min-width: 100px; /* Prevent layout shift */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }
</style>

<div id="netflix-player-container" class="hidden">
    <div class="player-header player-overlay d-flex justify-content-between align-items-center">
            <div class="player-title">Now Playing</div>
            <button id="close-player" class="player-btn"><i class="fas fa-times"></i></button>
    </div>

    <div class="player-controls player-overlay">
        <div class="progress-container">
            <div class="buffer-bar" style="width: 0%;"></div>
            <div class="progress-bar" style="width: 0%;"></div>
        </div>

        <div class="controls-row">
            <div class="left-controls">
                <button id="play-pause" class="player-btn"><i class="fas fa-play"></i></button>
                <button id="volume" class="player-btn"><i class="fas fa-volume-up"></i></button>
                <span id="time-display">00:00 / 00:00</span>
            </div>

            <div class="right-controls">
                <button id="fullscreen" class="player-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
    </div>
</div>

<video id="video-player" class="hidden" preload="metadata" playsinline></video>

<script>
    $(document).ready(function() {
        const playerContainer = $('#netflix-player-container');
        const video = $('#video-player')[0];
        const progressBar = $('.progress-bar');
        const bufferBar = $('.buffer-bar');
        const timeDisplay = $('#time-display');
        const playPauseBtn = $('#play-pause');
        const volumeBtn = $('#volume');
        const fullscreenBtn = $('#fullscreen');
        const closeBtn = $('#close-player');
        const progressContainer = $('.progress-container');
        const playerOverlays = $('.player-overlay');

        let hls = null;
        let controlsTimeout = null;

        function formatTime(timeInSeconds) {
            if (isNaN(timeInSeconds) || timeInSeconds < 0) {
                return '00:00';
            }
            const minutes = Math.floor(timeInSeconds / 60);
            const seconds = Math.floor(timeInSeconds % 60);
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function showControls() {
            playerOverlays.removeClass('hidden');
            playerContainer.css('cursor', 'default');
            clearTimeout(controlsTimeout);

            if (!video.paused && !video.ended) {
                controlsTimeout = setTimeout(hideControls, 3000);
            }
        }

        function hideControls() {
            if (!video.paused && !video.ended && !playerContainer.is(':focus-within')) {
                playerOverlays.addClass('hidden');
                playerContainer.css('cursor', 'none');
            }
        }

        function disableBodyScroll() {
            $('body').css('overflow', 'hidden');
        }

        function enableBodyScroll() {
            $('body').css('overflow', 'auto');
        }

        window.playMedia = function(mediaId) { // Make it globally accessible if called from outside this script
            console.log("Requesting stream via AJAX for:", mediaId);

            $.ajax({
                url: '/api/player.php',
                dataType: 'json',
                method: 'POST',
                data: {
                    'action': 'getStream',
                    'id': mediaId
                },
                success: function(data) {
                    handleStreamData(data);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    alert('Error fetching video stream. Please check the console for details.');
                    closePlayer();
                }
            });
        }

        function handleStreamData(data) {
            if (!data || !data.success || !data.data || !data.data.url) {
                const errorMessage = data && data.message ? data.message : 'Invalid data received from server.';
                alert(`Error: ${errorMessage}`);
                console.error("Stream Error:", errorMessage, data);
                closePlayer();
                return;
            }

            disableBodyScroll();
            playerContainer.removeClass('hidden');
            $(video).removeClass('hidden');

            const videoSrc = data.data.url;
            console.log("Stream URL received:", videoSrc);

            if (hls) {
                console.log("Destroying previous HLS instance.");
                hls.destroy();
                hls = null;
            }
            detachVideoListeners();

            if (videoSrc.toLowerCase().includes('.m3u8') && Hls.isSupported()) {
                console.log("Using HLS.js for playback.");
                hls = new Hls({
                    maxBufferLength: 30,
                    maxBufferSize: 60 * 1000 * 1000,
                    enableWorker: true,
                });

                hls.on(Hls.Events.BUFFER_APPENDED, updateBufferBar);
                hls.on(Hls.Events.ERROR, handleHlsError);
                hls.on(Hls.Events.MANIFEST_PARSED, handleManifestParsed);

                hls.loadSource(videoSrc);
                hls.attachMedia(video);

            } else if (video.canPlayType('application/vnd.apple.mpegurl') && videoSrc.toLowerCase().includes('.m3u8')) {
                console.log("Using native HLS playback.");
                video.src = videoSrc;
                video.addEventListener('progress', updateBufferBar);

            } else if (video.canPlayType('video/mp4') && videoSrc.toLowerCase().includes('.mp4')) {
                console.log("Using native MP4 playback.");
                video.src = videoSrc;
                video.addEventListener('progress', updateBufferBar);

            } else {
                console.error("Video format not supported or URL type mismatch:", videoSrc);
                alert('This video format is not supported or the URL is invalid.');
                closePlayer();
                return;
            }

            attachVideoListeners();

            if (!hls) {
                showControls();
                handleWaiting();

                video.load();
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.error("Error starting native playback:", error);
                        handlePause();
                    });
                }
            } else {
                showControls();
                handleWaiting();
            }
        }

        function handleManifestParsed() {
            console.log("HLS Manifest parsed, attempting to play.");
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => {
                    console.error("Error starting playback after manifest parsed:", error);
                    handlePause();
                });
            }
        }

        function handleHlsError(event, data) {
            console.error('HLS Error:', data);
            if (data.fatal) {
                switch(data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        console.warn("HLS Network error - trying to recover:", data.details);
                        if(data.details !== 'manifestLoadError') {
                            hls.startLoad();
                        } else {
                            alert("Failed to load video manifest. Please check the URL or network connection.");
                            closePlayer();
                        }
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        console.warn("HLS Media error - trying to recover:", data.details);
                        hls.recoverMediaError();
                        break;
                    default:
                        console.error("Unrecoverable HLS error - destroying HLS instance.");
                        alert("A critical error occurred playing the video.");
                        closePlayer();
                        break;
                }
            } else {
                console.warn("Non-fatal HLS error:", data);
            }
        }

        function attachVideoListeners() {
            video.addEventListener('timeupdate', updateTimeAndProgress);
            video.addEventListener('ended', handleVideoEnd);
            video.addEventListener('play', handlePlay);
            video.addEventListener('pause', handlePause);
            video.addEventListener('waiting', handleWaiting);
            video.addEventListener('playing', handlePlaying);
            video.addEventListener('loadedmetadata', updateTimeAndProgress);
        }

        function detachVideoListeners() {
            video.removeEventListener('timeupdate', updateTimeAndProgress);
            video.removeEventListener('progress', updateBufferBar);
            video.removeEventListener('ended', handleVideoEnd);
            video.removeEventListener('play', handlePlay);
            video.removeEventListener('pause', handlePause);
            video.removeEventListener('waiting', handleWaiting);
            video.removeEventListener('playing', handlePlaying);
            video.removeEventListener('loadedmetadata', updateTimeAndProgress);
        }

        function updateBufferBar() {
            if (video.buffered && video.buffered.length > 0 && video.duration > 0 && isFinite(video.duration)) {
                let bufferedEnd = 0;
                try {
                    for (let i = 0; i < video.buffered.length; i++) {
                        if (video.buffered.start(i) <= video.currentTime && video.buffered.end(i) >= video.currentTime) {
                            bufferedEnd = video.buffered.end(i);
                            break;
                        }
                    }
                    if (bufferedEnd === 0 && video.buffered.length > 0) {
                        bufferedEnd = video.buffered.end(video.buffered.length - 1);
                    }
                } catch (e) {
                    console.warn("Error accessing video.buffered details:", e);
                    return;
                }
                const bufferPercent = (bufferedEnd / video.duration) * 100;
                bufferBar.css('width', Math.min(bufferPercent, 100) + '%');
            }
        }

        playPauseBtn.on('click', function() {
            if (video.paused) {
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.error("Manual play failed:", error);
                    });
                }
            } else {
                video.pause();
            }
        });

        closeBtn.on('click', closePlayer);

        function closePlayer() {
            console.log("Closing player...");
            video.pause();
            clearTimeout(controlsTimeout);

            if (hls) {
                console.log("Destroying HLS instance.");
                hls.destroy();
                hls = null;
            }

            detachVideoListeners();

            video.src = "";
            video.removeAttribute('src');
            video.load();

            playerContainer.addClass('hidden');
            $(video).addClass('hidden');

            progressBar.css('width', '0%');
            bufferBar.css('width', '0%');
            timeDisplay.text('00:00 / 00:00');
            playPauseBtn.find('i').removeClass('fa-pause fa-spin fa-spinner').addClass('fa-play');
            volumeBtn.find('i').removeClass('fa-volume-mute').addClass('fa-volume-up');
            updateFullscreenIcon(false);
            playerContainer.css('cursor', 'default');

            enableBodyScroll();
        }

        function handlePlay() {
            console.log("Video play event triggered");
            playPauseBtn.find('i').removeClass('fa-play fa-spin fa-spinner').addClass('fa-pause');
            showControls();
        }

        function handlePause() {
            console.log("Video pause event triggered");
            if (!video.ended) {
                playPauseBtn.find('i').removeClass('fa-pause fa-spin fa-spinner').addClass('fa-play');
                showControls();
            }
        }

        function handleWaiting() {
            console.log("Video waiting (buffering) event triggered");
            playPauseBtn.find('i').removeClass('fa-play fa-pause').addClass('fa-spin fa-spinner');
            showControls();
        }

        function handlePlaying() {
            console.log("Video playing (after wait/seek) event triggered");
            if(!video.ended) {
                playPauseBtn.find('i').removeClass('fa-spin fa-spinner fa-play').addClass('fa-pause');
                showControls();
            }
        }

        function handleVideoEnd() {
            console.log("Video ended event triggered");
            playPauseBtn.find('i').removeClass('fa-pause fa-spin fa-spinner').addClass('fa-play');
            progressBar.css('width', '100%');
            if (video.duration > 0 && isFinite(video.duration)) {
                timeDisplay.text( `${formatTime(video.duration)} / ${formatTime(video.duration)}`);
            }
            showControls();
        }

        function updateTimeAndProgress() {
            if (video.duration > 0 && isFinite(video.duration)) {
                const playedPercent = (video.currentTime / video.duration) * 100;
                progressBar.css('width', playedPercent + '%');
                timeDisplay.text(
                    `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`
                );
                updateBufferBar();
            } else if (!isFinite(video.duration) && video.currentTime > 0) {
                timeDisplay.text(`${formatTime(video.currentTime)} / Live`);
            } else {
                timeDisplay.text(`00:00 / --:--`);
            }
        }

        progressContainer.on('click', function(e) {
            if (video.duration > 0 && isFinite(video.duration)) {
                const rect = this.getBoundingClientRect();
                const clickPosition = e.clientX - rect.left;
                const clampedPosition = Math.max(0, Math.min(clickPosition, rect.width));
                const percentage = clampedPosition / rect.width;

                video.currentTime = video.duration * percentage;
                progressBar.css('width', (percentage * 100) + '%');
                updateTimeAndProgress();
            } else {
                console.log("Cannot seek: Video duration unknown or infinite.");
            }
        });

        volumeBtn.on('click', function() {
            video.muted = !video.muted;
            $(this).find('i').toggleClass('fa-volume-up fa-volume-mute');
        });

        function updateFullscreenIcon(isFullscreen) {
            fullscreenBtn.find('i').toggleClass('fa-expand', !isFullscreen).toggleClass('fa-compress', isFullscreen);
        }

        fullscreenBtn.on('click', function() {
            if (!document.fullscreenElement) {
                playerContainer[0].requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        document.addEventListener('fullscreenchange', () => {
            const isFullscreen = !!document.fullscreenElement;
            updateFullscreenIcon(isFullscreen);
        });

        playerContainer.on('mousemove', showControls);
        playerContainer.on('focusin', showControls);
        playerContainer.on('focusout', () => {
            if (!video.paused && !video.ended) {
                clearTimeout(controlsTimeout);
                controlsTimeout = setTimeout(hideControls, 300);
            }
        });

        closePlayer();
    });
</script>