const isPyr = document.querySelector('[data-playerid]')?.getAttribute('data-playerid');
if (!isPyr) {
    var scriptUrl = 'https:\/\/www.youtube.com\/s\/player\/9d15588c\/www-widgetapi.vflset\/www-widgetapi.js'; try { var ttPolicy = window.trustedTypes.createPolicy("youtube-widget-api", { createScriptURL: function (x) { return x } }); scriptUrl = ttPolicy.createScriptURL(scriptUrl) } catch (e) { } var YT; if (!window["YT"]) YT = { loading: 0, loaded: 0 }; var YTConfig; if (!window["YTConfig"]) YTConfig = { "host": "https://www.youtube.com" };
    if (!YT.loading) {
        YT.loading = 1; (function () {
            var l = []; YT.ready = function (f) { if (YT.loaded) f(); else l.push(f) }; window.onYTReady = function () { YT.loaded = 1; var i = 0; for (; i < l.length; i++)try { l[i]() } catch (e) { } }; YT.setConfig = function (c) { var k; for (k in c) if (c.hasOwnProperty(k)) YTConfig[k] = c[k] }; var a = document.createElement("script"); a.type = "text/javascript"; a.id = "www-widgetapi-script"; a.src = scriptUrl; a.async = true; var c = document.currentScript; if (c) {
                var n = c.nonce || c.getAttribute("nonce"); if (n) a.setAttribute("nonce",
                    n)
            } var b = document.getElementsByTagName("script")[0]; b.parentNode.insertBefore(a, b)
        })()
    };
}

let adsConainers = document.querySelectorAll('[data-ad-id]');
let container = document.querySelector('[data-ad-id]');
const player = [];
let playerIndex = 0;


adsConainers = Array.from(adsConainers);

const getYTVideoId = (url) => {
    // Check if the input is a string
    if (typeof url !== 'string') {
        return false;
    }

    const regex = /(?:youtube\.com\/(?:[^\/]+\/[^\/]+\/|(?:v|e(?:mbed)?)\/|[^#]*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
    const match = url.match(regex);

    if (match && match[1]) {
        return match[1];
    } 
    return false;
}


const adInitialization = (adContainer, index) => {

    const adAtts = JSON.parse(atob(adContainer.getAttribute('data-ad-attrs')));

    const blockId = adAtts.clientId;
    const blockIdMD5 = adContainer.getAttribute('data-ad-id');
    const adStartAfter = adAtts.adStart * 1000;
    const adContent = adAtts.adContent;
    const adVideo = adContainer.querySelector('.ep-ad');
    const adSource = adAtts.adSource;
    const adVideos = [];
    const srcUrl = adAtts.url || adAtts.embedpress_embeded_link;

    addWrapperForYoutube(adContainer, srcUrl, adAtts);

    // let adVideo = adContainer.querySelector('#ad-' + blockId + ' .ep-ad');
    adVideos.push(adVideo);

    const adTemplate = adContainer.querySelector('.main-ad-template');
    const progressBar = adContainer.querySelector('.progress-bar');
    const skipButton = adContainer.querySelector('.skip-ad-button');
    const adRunningTime = adContainer.querySelector('.ad-running-time');

    const adMask = adContainer;

    const playerId = adContainer.querySelector('[data-playerid]')?.getAttribute('data-playerid');

    let playbackInitiated = false;

    if (skipButton && adSource !== 'video') {
        skipButton.style.display = 'inline-block';
    }

    adMask?.addEventListener('click', function () {

        adContainer.classList.remove('ad-mask');

        // console.log(playerInit.length)

        if (playerInit.length > 0) {
            playerInit[playerId]?.play();
        }

        if (getYTVideoId(srcUrl)) {
            console.log(index);
            player[index]?.playVideo();
        }

        if (!playbackInitiated) {
            setTimeout(() => {
                if (adSource !== 'image') {
                    adContainer.querySelector('.ep-embed-content-wraper').classList.add('hidden');
                }
                adTemplate?.classList.add('ad-running');
                if (adVideo && adSource === 'video') {
                    adVideo.muted = false;
                    adVideo.play();
                }
            }, adStartAfter);

            playbackInitiated = true;
        }
    });

    adVideo?.addEventListener('timeupdate', () => {
        const currentTime = adVideo?.currentTime;
        const videoDuration = adVideo?.duration;

        if (currentTime <= videoDuration) {
            const remainingTime = Math.max(0, videoDuration - currentTime); // Ensure it's not negative
            adRunningTime.innerText = Math.floor(remainingTime / 60) + ':' + (Math.floor(remainingTime) % 60).toString().padStart(2, '0');
        }

        if (!isNaN(currentTime) && !isNaN(videoDuration)) {
            const progress = (currentTime / videoDuration) * 100;
            progressBar.style.width = progress + '%';

            if (currentTime >= 3) {
                // Show the skip button after 3 seconds
                skipButton.style.display = 'inline-block';
            }
        }
    });


    // Add a click event listener to the skip button
    skipButton?.addEventListener('click', () => {
        adTemplate.remove();
        if (playerInit.length > 0) {
            playerInit[playerId]?.play();

        }
        if (getYTVideoId(srcUrl)) {
            player[index]?.playVideo();
        }
        adContainer.querySelector('.ep-embed-content-wraper').classList.remove('hidden');
    });

    // Add an event listener to check for video end
    adVideo?.addEventListener('play', () => {
        if (playerInit.length > 0) {
            playerInit[playerId]?.stop();
        }
    });

    // Add an event listener to check for video end
    adVideo?.addEventListener('ended', () => {
        // Remove the main ad template from the DOM when the video ends
        adTemplate.remove();
        adContainer.querySelector('.ep-embed-content-wraper').classList.remove('hidden');
    });

    playerIndex++;

}

const addWrapperForYoutube = (adContainer, srcUrl, adAtts) => {
    const youtubeIframe = adContainer.querySelector(`.ose-youtube iframe`);
    if (youtubeIframe && getYTVideoId(srcUrl)) {

        const divWrapper = document.createElement('div');
        divWrapper.className = 'ad-youtube-video';
        youtubeIframe.setAttribute('width', adAtts.width);
        youtubeIframe.setAttribute('height', adAtts.height);
        youtubeIframe.parentNode.replaceChild(divWrapper, youtubeIframe);
        divWrapper.appendChild(youtubeIframe);
    }
}



function onYouTubeIframeAPIReady(iframe, srcUrl, adVideo, index) {
    // Find the iframe by its src attribute

    if (iframe && getYTVideoId(srcUrl) !== null) {
        console.log(index);
        player[index] = new YT.Player(iframe, {
            videoId: getYTVideoId(srcUrl),

            events: {
                'onReady': (event) => onPlayerReady(event, adVideo),
            }
        });

    }

}

// This function is called when the player is ready
function onPlayerReady(event, adVideo) {
    adVideo?.addEventListener('ended', function () {
        event.target.playVideo();
    });

    adVideo?.addEventListener('play', function () {
        event.target.pauseVideo();
    });
}


window.onload = function () {
    let yVideos = setInterval(() => {
        var youtubeVideos = document.querySelectorAll('.ad-youtube-video');
        if (youtubeVideos.length > 0) {
            clearInterval(yVideos);

            youtubeVideos.forEach((yVideo, index) => {
                const srcUrl = yVideo.querySelector('iframe').getAttribute('src');
                const adVideo = yVideo.closest('.ad-mask').querySelector('.ep-ad');
                onYouTubeIframeAPIReady(yVideo, srcUrl, adVideo, index);
            });
        }
    }, 100);
};


if (adsConainers.length > 0) {
    let ytIndex = 0;
    adsConainers.forEach((adContainer, epAdIndex) => {
        
        adContainer.setAttribute('data-ad-index', epAdIndex);
        adInitialization(adContainer, ytIndex);
        if(getYTVideoId(adContainer.querySelector('iframe')?.getAttribute('src'))){
            ytIndex++;
        }
    });
}
