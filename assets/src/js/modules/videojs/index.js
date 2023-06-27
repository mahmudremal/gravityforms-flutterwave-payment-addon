/**
 * VideoJS file uploading plugin. https://collab-project.github.io/videojs-record/
 * 
 * @package Future WordPress Inc.
 */


// import 'video.js/dist/video-js.min.css';
import videojs from 'video.js';
import RecordRTC from 'recordrtc';
// import { __ } from '@wordpress/i18n'; // sprintf, _n

// import 'videojs-record/dist/css/videojs.record.css';
import Record from 'videojs-record/dist/videojs.record.js';
import { toast } from 'toast-notification-alert';
 
// import '../../sass/8-frontend/_videojs.scss';
 
 
 ( function ( $ ) {
   /**
    * VideoJS assets
    * <!-- load css -->
    * <link rel="stylesheet" href="//unpkg.com/videojs-record/dist/css/videojs.record.min.css">
    * <!-- load script -->
    * <script src="//unpkg.com/videojs-record/dist/videojs.record.min.js"></script>
    * 
    * <video id="myVideo" playsinline class="video-js vjs-default-skin"></video>
    */
   class FWPProject_VideoJS {
    constructor() {
      this.ajaxUrl     = fwpSiteConfig?.ajaxUrl ?? '';
      this.ajaxNonce   = fwpSiteConfig?.ajax_nonce ?? '';
      this.buildPath   = fwpSiteConfig?.buildPath ?? '';
      this.selector    = 'fwp-videojs-record-field'; // is ID.
      this.i18n        = {
      sureToSubmit: fwpSiteConfig?.sureToSubmit ?? 'Want to submit it? Canbe retaken.'
      };
      this.options     = {
        // video.js options
        controls: true,
        bigPlayButton: false,
        loop: false,
        fluid: false,
        width: 320,
        height: 240,
        plugins: {
          // videojs-record plugin options
          record: {
            image: false,
            audio: false,
            video: true,
            maxLength: 5,
            displayMilliseconds: true,
            debug: true
          }
        }
      };
      this.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
      this.isEdge = /Edge/.test(navigator.userAgent);
      this.isOpera = !!window.opera || navigator.userAgent.indexOf('OPR/') !== -1;
    
      // console.log( __( 'VideoJS init...', 'woocommerce-checkout-video-snippet' ) );
      
      this.options = {
        controls: true,
        width: 320,
        height: 240,
        fluid: false,
        // bigPlayButton: true,
        controlBar: {
            volumePanel: false,
            fullscreenToggle: false
        },
        plugins: {
            record: {
                image: false,
                audio: true,
                video: true,
                maxLength: 30,
                // displayMilliseconds: false,
                debug: true
            }
        }
      };

      this.initFunctions();
      this.init();
    }
    init() {
      const thisClass = this;var theInterval;
      theInterval = setInterval( () => {
        // || typeof videojs.Record === 'function'
        if( typeof videojs === 'function' ) {
          clearInterval( theInterval );thisClass.initVideoJS();thisClass.initialize();
        // } else {console.log( 'fail' );
        }
      }, 5000 );
    }
    initialize() {
      const thisClass = this;var theInterval, selector;
      thisClass.videoPlayers = [];thisClass.videoRecorders = [];
      selector = 'fwp-videojs-playing-field';
      // theInterval = setInterval( () => {}, 5000 );
      document.querySelectorAll( '.' + selector ).forEach( ( e, i ) => {
        if( ! e.id ) {e.id = selector + '-' + i;}
        thisClass.videoPlayers.push( { id: e.id, i: i, player: videojs( e.id )} );
      } );
      // document.querySelectorAll( 'fwp-videojs-record-field' ).forEach( ( e, i ) => {
      //   if( ! e.id ) {e.id = 'fwp-videojs-record-field-' + i;}
      //   thisClass.videoRecorders.push( { id: e.id, i: i, recorder: videojs( e.id )} );
      // } );
    }
    initFunctions() {
      const thisClass = this;var css, js, csses, jses;
      csses = [ '//cdnjs.cloudflare.com/ajax/libs/video.js/7.5.5/video-js.min.css', '//cdnjs.cloudflare.com/ajax/libs/videojs-record/3.8.0/css/videojs.record.min.css' ];jses = [];
      if( typeof videojs === 'undefined' ) {
        jses = [ '//cdnjs.cloudflare.com/ajax/libs/video.js/7.5.5/video.min.js', '//unpkg.com/recordrtc/RecordRTC.js', '//unpkg.com/webrtc-adapter/out/adapter.js', '//cdnjs.cloudflare.com/ajax/libs/videojs-record/3.8.0/videojs.record.js' ];
      }
      csses.forEach( ( src ) => {
        css = document.createElement( 'link' );css.rel = 'stylesheet';css.href = src;document.head.appendChild( css );
      } );
      jses.forEach( ( src ) => {
        js = document.createElement( 'script' );js.language = 'text/javascript';js.src = src;document.head.appendChild( js );
      } );
      if( typeof __ === 'undefined' ) {
        function __( text, domain ) {
          return text;
        }
      }
    }
    initVideoJS() {
      const thisClass = this;var player, options, video, recorder, message;
      if( ! document.getElementById( this.selector ) ) {return;}
      thisClass.beforeInit();
      thisClass.player = videojs( this.selector, thisClass.options, function() {
          // print version information at startup
          // message = 'Using video.js ' + videojs.VERSION + ' with videojs-record ' + videojs.getPluginVersion('record');
          // videojs.log( message );
      });
      thisClass.afterInit();

      // let player = videojs( this.selector, this.options, function() {
      //   // print version information at startup
      //   var msg = 'Using video.js ' + videojs.VERSION + ' with videojs-record ' + videojs.getPluginVersion('record');
      //   videojs.log(msg);
      //   console.log("videojs-record is ready!");
      // });
    }
    beforeInit() {
    if( typeof applyVideoWorkaround !== 'undefined' ) {
      applyVideoWorkaround();
    }
    }
    afterInit() {
    this.initHooks();
    }
    initHooks() {
      const thisClass = this;var player, options, video, recorder, message;
      thisClass.player.on('deviceError', function() {
        message = 'device error:', thisClass.player.deviceErrorCode;
        toast.show({title: message, position: 'bottomright', type: 'warn'});// console.warn( message );
      });
      thisClass.player.on('error', function(element, error) {
        toast.show({title: error, position: 'bottomright', type: 'alert'});// console.error( error );
      });
      // user clicked the record button and started recording
      thisClass.player.on('startRecord', function() {
        // console.log('started recording!');
      });
      // snapshot is available
      thisClass.player.on('finishRecord', function() {
        // the blob object contains the image data that
        // can be downloaded by the user, stored on server etc.
        // console.log('snapshot ready: ', thisClass.player.recordedData );
        if( confirm( thisClass.i18n.sureToSubmit ) ) {
          // new Promise( ( resolve, reject ) => {
          //   resolve( thisClass.fileToConvert( thisClass.player.recordedData ) );
          // } ).then( ( file ) => {
            // console.log( file );
            var info, file = thisClass.player.recordedData;
            // file = await fetch( data );
            info = {
              name: file.name,
              size: file.size, // prototype
              type: file.type
              // ...file
            };
            // window.dropZoneField.addFile( info );
            
              var formdata = new FormData();
              formdata.append( 'blobFile', file );
              formdata.append( 'blobInfo', JSON.stringify( info ) );
              formdata.append( 'action', 'gravityformsflutterwaveaddons/project/filesystem/upload' );
              formdata.append( '_nonce', thisClass.ajaxNonce );
              thisClass.send( formdata );
          // } );
        }
      });
    }
    __( text, domain ) {
      return ( typeof __ !== 'undefined' ) ? __( text, domain ) : text;
    }
    textIcon( text, icon ) {
      const thisClass = this;
      if( thisClass.buildPath != '' ) {
        return '<img src="' + thisClass.buildPath + '/icons/' + icon + '" alt="' + text + '" title="' + text + '" />';
      } else {
        return text;
      }
    }
    async fileToConvert( file, isText = true ) {
      const thisClass = this;
    //   // thisClass.convertBlobToBase64 = (blob) => new Promise((resolve, reject) => {
    //   //   const reader = new FileReader;
    //   //   reader.onerror = reject;
    //   //   reader.onload = () => {
    //   //       resolve(reader.result);
    //   //   };
    //   //   reader.readAsDataURL(blob);
    //   // });
    //   // thisClass.convertBase64ToBlob = (text) => new Promise((resolve, reject) => {
    //   //   const base64Response = await fetch(`data:image/jpeg;base64,${text}`);
    //   //   return await base64Response.blob();
    //   // });
      if( isText !== false ) {
    //     // return await thisClass.convertBlobToBase64( file );
    //     const reader = new FileReader;
    //     reader.onload = () => {
    //       return reader.result;
    //     };
    //     reader.readAsDataURL(blob);
    //     return reader.result;
    //   } else {
    //     // return await thisClass.convertBase64ToBlob( file );
  //  `data:image/jpeg;base64,${file}`
        var base64Response = await fetch( file );
        return await base64Response.blob();
      }
    }
    send( data ) {
      const thisClass = this;var progress;
      progress = document.querySelector( '#order_review' );
      if( progress ) {progress.dataset.processing = true;progress.dataset.msg = 'Uploading...';}
      $.ajax({
        url: thisClass.ajaxUrl,
        type: "POST",
        data: data,    
        cache: false,
        contentType: false,
        processData: false,
        success: function( json ) {
          thisClass.handleSuccess( json );
          if( progress ) {progress.dataset.processing = false;}
        },
        error: function( err ) {
          if( progress ) {progress.dataset.processing = false;}
          thisClass.handleError( err );
        },
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          // var xhr = $.ajaxSettings.xhr(); // https://coderwall.com/p/je3uww/get-progress-of-an-ajax-request
          xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = evt.loaded / evt.total;
              progress.dataset.msg = 'Uploading (' + ( percentComplete * 100 ).toFixed( 0 ) + '%)...';
            }
          }, false);
          xhr.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = evt.loaded / evt.total;
              progress.dataset.msg = 'Uploading (' + ( percentComplete * 100 ).toFixed( 0 ) + '%)...';
            }
          }, false);
          return xhr;
        }
      } );
    }
    handleSuccess( json ) {
      const thisClass = this;var e, message;
      // console.log( json );
      message = ( json.data.message ) ? json.data.message : json.data;
      if( json.success ) {
        if( json.data.dropZone && window.dropZoneField ) {
          e = json.data.dropZone;
          window.dropZoneField.displayExistingFile( e, e.full_url );
        }
        toast.show({title: message, position: 'bottomright', type: 'info'});
      } else {
        toast.show({title: message, position: 'bottomright', type: 'warn'});
      }
    }
    handleError( err ) {
      const thisClass = this;
      // console.log( err.responseText );
      toast.show({title: err.responseText, position: 'bottomright', type: 'warn'});
    }
    applyAudioWorkaround() {
      const thisClass = this;
      if ( thisClass.isSafari || thisClass.isEdge) {
        if ( thisClass.isSafari && window.MediaRecorder !== undefined) {
          // this version of Safari has MediaRecorder
          // but use the only supported mime type
          thisClass.options.plugins.record.audioMimeType = 'audio/mp4';
        } else {
          // support recording in safari 11/12
          // see https://github.com/collab-project/videojs-record/issues/295
          thisClass.options.plugins.record.audioRecorderType = StereoAudioRecorder;
          thisClass.options.plugins.record.audioSampleRate = 44100;
          thisClass.options.plugins.record.audioBufferSize = 4096;
          thisClass.options.plugins.record.audioChannels = 2;
        }

        console.log('applied audio workarounds for this browser');
      }
    }
    
    applyVideoWorkaround() {
      const thisClass = this;
      // use correct video mimetype for opera
      if (thisClass.isOpera) {
        thisClass.options.plugins.record.videoMimeType = 'video/webm\;codecs=vp8'; // or vp9
      }
    }
    
    applyScreenWorkaround() {
      const thisClass = this;
      // Polyfill in Firefox.
      // See https://blog.mozilla.org/webrtc/getdisplaymedia-now-available-in-adapter-js/
      if (adapter.browserDetails.browser == 'firefox') {
          adapter.browserShim.shimGetDisplayMedia(window, 'screen');
      }
    }
   }
   new FWPProject_VideoJS();
 } )( jQuery );
 // ( typeof jQuery !== 'undefined' ) ? jQuery : false
 
 
 
 /**
  * Php implimentations.
  * 
  * $ds = DIRECTORY_SEPARATOR;$storeFolder = 'uploads';if (!empty($_FILES)) {$tempFile = $_FILES['file']['tmp_name'];            $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;$targetFile =  $targetPath. $_FILES['file']['name'];move_uploaded_file($tempFile,$targetFile);}
  */