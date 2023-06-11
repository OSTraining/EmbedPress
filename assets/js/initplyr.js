/**
 * Note: This is complex initialization, but it is necessary for Gutenberg and Elementor compatibility. There are some known issues in Gutenberg that require this complex setup.
 */

// Event listener for when the DOM content is loaded
document.addEventListener('DOMContentLoaded', function () {

  // Select all embed wrappers with the class 'ep-embed-content-wraper'
  let embedWrappers = document.querySelectorAll('.ep-embed-content-wraper');

  // Initialize the player for each embed wrapper
  embedWrappers.forEach(wrapper => {
    initPlayer(wrapper);
  });

  // Mutation observer to detect any changes in the DOM
  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      const addedNodes = Array.from(mutation.addedNodes);
      addedNodes.forEach(node => {
        traverseAndInitPlayer(node);
      });
    });
  });

  // Start observing changes in the entire document body and its subtree
  observer.observe(document.body, { childList: true, subtree: true });

  // Recursive function to traverse the DOM and initialize the player for each embed wrapper
  function traverseAndInitPlayer(node) {
    if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('ep-embed-content-wraper')) {
      initPlayer(node);
    }

    if (node.hasChildNodes()) {
      node.childNodes.forEach(childNode => {
        traverseAndInitPlayer(childNode);
      });
    }
  }

  // Function to initialize the player for a given wrapper
  function initPlayer(wrapper) {
    const playerId = wrapper.getAttribute('data-playerid');

    // Check if the player has not been initialized for this wrapper
    if (playerId && !wrapper.classList.contains('plyr-initialized')) {
      const selector = `[data-playerid="${playerId}"] .ose-embedpress-responsive`;

      // Get the options for the player from the wrapper's data attribute
      let options = document.querySelector(`[data-playerid="${playerId}"]`).getAttribute('data-options');

      // Parse the options string into a JSON object
      options = JSON.parse(options);

      // Set the main color of the player
      document.querySelector(`[data-playerid="${playerId}"]`).style.setProperty('--plyr-color-main', options.player_color);

      // Set the poster thumbnail for the player
      document.querySelector(`[data-playerid="${playerId}"] iframe`).setAttribute('data-poster', options.poster_thumbnail);

      // Define the controls to be displayed
      const controls = [
        'play-large',
        options.restart ? 'restart' : '',
        options.rewind ? 'rewind' : '',
        'play',
        options.fast_forward ? 'fast-forward' : '',
        'progress',
        'current-time',
        'duration',
        'mute',
        'volume',
        'captions',
        'settings',
        options.pip ? 'pip' : '',
        'airplay',
        'download',
        'fullscreen'
      ].filter(control => control !== '');

      // Create a new Plyr player instance with the specified options and controls
      const player = new Plyr(selector, {
        controls: controls,
        seekTime: 10,
        ads: { enabled: false, publisherId: '', tagUrl: 'https://googleads.github.io/googleads-ima-html5/vsi/' },
        poster: options.poster_thumbnail,
        storage: {
          enabled: true,
          key: 'plyr_volume'
        },
        displayDuration: true,
        tooltips: { controls: options.player_tooltip, seek: options.player_tooltip },
        hideControls: options.hide_controls
      });

      // Mark the wrapper as initialized
      wrapper.classList.add('plyr-initialized');
    }

    // Check for the existence of the player's pip button at regular intervals
    const pipInterval = setInterval(() => {
      let playerPip = document.querySelector(`[data-playerid="${playerId}"] [data-plyr="pip"]`);
      if (playerPip) {
        clearInterval(pipInterval);
        const iframeSelector = document.querySelector(`[data-playerid="${playerId}"] iframe`);

        // Add click event listener to toggle the pip mode
        playerPip.addEventListener('click', () => {
          iframeSelector.classList.toggle('pip-mode');
        });
      }
    }, 200);

  }
  
});