<?php

session_start();
ob_start();

$SES_Child_ID   = $_SESSION['SES_Child_ID'];

$game_id = $_GET['game_id'];
$generatedGameName = $_GET['generatedGameName'];
// $game_id = $_GET['game_id'];
// echo 'dirname';
// echo getcwd('../');

$rootPath = $_SERVER['DOCUMENT_ROOT'];
$thisPath = dirname($_SERVER['PHP_SELF']);
$onlyPath = str_replace($rootPath, '', $thisPath);
// echo $thisPath;

$template_name =  explode("/", trim($onlyPath))[2];


// echo $game_id;
?>

<!DOCTYPE html>
<html lang="en-us">

<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Unity WebGL Player | Dlearners VAKT</title>
  <link rel="shortcut icon" href="TemplateData/favicon.ico">
  <link rel="stylesheet" href="TemplateData/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');

    #unity-container {
      width: 100%;
      height: 100%;
    }

    .keyboard-fullscreen-layout {
      color: #00000038;
      width: 98%;
      position: absolute;
      bottom: 0;
      display: flex;
      justify-content: flex-end;
      margin-block-start: 10px;
      margin-block-end: 10px;
      margin-inline-start: 0px;
      margin-inline-end: 0px;
      font-size: 20px;
      /* text-shadow: 1.2px 1.2px white; */
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body>
  <div id="unity-container" class="unity-desktop">
    <canvas id="unity-canvas" width=960 height=480></canvas>
    <div id="unity-loading-bar">
      <div id="unity-logo"></div>
      <div id="unity-progress-bar-empty">
        <div id="unity-progress-bar-full"></div>
      </div>
    </div>
    <div id="unity-mobile-warning">
      WebGL builds are not supported on mobile devices.
    </div>
    <!-- <div id="unity-footer">
      <div id="unity-webgl-logo"></div>
      <div id="unity-fullscreen-button"></div>
      <div id="unity-build-title">Dlearners VAKT</div>
    </div> -->

    <p class="keyboard-fullscreen-layout">Press F11 to view in fullscreen</p>

  </div>
  <script>
    var buildUrl = "Build";
    let child_data = '0';

    var loaderUrl = buildUrl + "/VAKT_game.loader.js";
    var config = {
      dataUrl: buildUrl + "/VAKT_game.data.unityweb",
      frameworkUrl: buildUrl + "/VAKT_game.framework.js.unityweb",
      codeUrl: buildUrl + "/VAKT_game.wasm.unityweb",
      streamingAssetsUrl: "StreamingAssets",
      companyName: "Dlearners",
      productName: "Dlearners VAKT",
      productVersion: "1.0.0",
    };

    var container = document.querySelector("#unity-container");
    var canvas = document.querySelector("#unity-canvas");
    var loadingBar = document.querySelector("#unity-loading-bar");
    var progressBarFull = document.querySelector("#unity-progress-bar-full");
    var fullscreenButton = document.querySelector("#unity-fullscreen-button");
    var mobileWarning = document.querySelector("#unity-mobile-warning");

    // By default Unity keeps WebGL canvas render target size matched with
    // the DOM size of the canvas element (scaled by window.devicePixelRatio)
    // Set this to false if you want to decouple this synchronization from
    // happening inside the engine, and you would instead like to size up
    // the canvas DOM size and WebGL render target sizes yourself.
    // config.matchWebGLToCanvasSize = false;

    if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
      container.className = "unity-mobile";
      // Avoid draining fillrate performance on mobile devices,
      // and default/override low DPI mode on mobile browsers.
      config.devicePixelRatio = 1;
      mobileWarning.style.display = "block";
      setTimeout(() => {
        mobileWarning.style.display = "none";
      }, 5000);
    } else {
      canvas.style.width = "100%";
      canvas.style.height = "100%";
    }
    loadingBar.style.display = "block";

    var script = document.createElement("script");
    script.src = loaderUrl;
    // script.onload = () => {
    //   createUnityInstance(canvas, config, (progress) => {
    //     progressBarFull.style.width = 100 * progress + "%";
    //   }).then((unityInstance) => {
    //     loadingBar.style.display = "none";
    //     // fullscreenButton.onclick = () => {
    //     //   unityInstance.SetFullscreen(1);
    //     // };
    //   }).catch((message) => {
    //     alert(message);
    //   });
    // };
    script.onload = () => {

      window.gameInstance = createUnityInstance(canvas, config, (progress) => {

        progressBarFull.style.width = 100 * progress + "%";

      }).then((unityInstance) => {

        window.gameInstance = unityInstance;

        loadingBar.style.display = "none";

        // fullscreenButton.onclick = () => {

        //   unityInstance.SetFullscreen(1);

        // };

      }).catch((message) => {
        alert(message);
      });

    };

    function OnAppReady() {

      console.log('on app ready crash');
      let gameID = '<?php echo $game_id; ?>';
      let _template_name = '<?php echo $template_name; ?>';
      console.log(gameID);
      if (gameID == 0) {

        let generatedGameName = '<?php echo $generatedGameName; ?>'

        console.log('generatedGameName: ' + generatedGameName);

        let accessLink
        if (generatedGameName != '') {
          _template_name = generatedGameName
          accessLink = '../../../../../Game_Generator/create_and_update_file.php'
          console.log('accessLink');
        } else {
          accessLink = '../../../../create_and_update_file.php'
        }


        console.log(accessLink);

        let encodedJSON = '';

        let getEncodedPreviewData = function() {
          $.get(accessLink, {
            template_name: _template_name
          }, function(response) {
            // console.log('create_and_update_file.php response');
            // console.log(response)
            encodedJSON = response
            console.log(encodedJSON);

            gameInstance.SendMessage('GameID', 'JS_getMode', atob(encodedJSON)) // call this for only on preview
          })
        }
        getEncodedPreviewData()

      } else if (gameID > 0) {

        if ('<?php echo $SES_Child_ID; ?>' == '') {
          child_data = '0'
        } else {
          child_data = '<?php echo $SES_Child_ID; ?>'
        }

        let jsonFormat = {
          child_id: child_data,
          game_id: gameID
        }

        console.log(jsonFormat);
        console.log(JSON.stringify(jsonFormat));
        gameInstance.SendMessage('GameID', 'JS_getID', JSON.stringify(jsonFormat)) // call this only for live

      }
    }

    let keyboard_fullscreen_layout = document.querySelector('.keyboard-fullscreen-layout')

    // checks only when the user changes their mode to fullscreen (like pressing f11)
    window.matchMedia('(display-mode: fullscreen)')
      .addListener(({
        matches
      }) => {
        if (matches) {
          keyboard_fullscreen_layout.textContent = ''
          console.log('fullscreen');
          // Apply fullscreenmode mode related changes
        } else {
          keyboard_fullscreen_layout.textContent = 'Press F11 to view in fullscreen'
          console.log('no fullscreen');
          // Remove fullscreenmode mode related changes
        }
      });


    // checking window is in full screen on window load
    if ((window.fullScreen) ||
      (window.innerWidth == screen.width && window.innerHeight == screen.height)) {

      console.log('window is in full screen');
      keyboard_fullscreen_layout.textContent = ''

    } else {
      keyboard_fullscreen_layout.textContent = 'Press F11 to view in fullscreen'
    }

    function closeApplication() { // Working
      console.log("untiy closing crash!");
      window.close()
    }


    document.body.appendChild(script);
  </script>
</body>

</html>