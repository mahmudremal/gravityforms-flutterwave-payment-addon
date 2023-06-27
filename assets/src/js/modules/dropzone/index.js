/**
 * Dropzone file uploading plugin. https://dropzone.dev/
 * 
 * @package Future WordPress Inc.
 */

import { Dropzone } from "dropzone";
import { toast } from 'toast-notification-alert';
// import { __ } from '@wordpress/i18n'; // sprintf, _n

// import '../../../sass/modules/dropzone.scss';
// import './index.scss';

// import defaultPreviewTemplate from "bundle-text:./preview-template.html";

// <form action="" method="post" enctype="multipart/form-data"><input type="file" name="file" /></form>

( function ( $ ) {
  /**
   * Dropzone assets
   * <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
   * <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
   * 
   * <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
   * <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
   */
	class FWPProject_DropZone {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl ?? '';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce ?? '';
			this.buildPath = fwpSiteConfig?.buildPath ?? '';
			this.videoClips = fwpSiteConfig?.videoClips ?? [];
      this.selector = '.fwp-dropzone-field';
      // reference  https://developer.mozilla.org/en-US/docs/Web/API/FileReader/    http://community.sitepoint.com/t/get-video-duration-before-upload/30623/4
      this.videoControl = {
        videoMaxTime: "20:30:00",
        audioMaxTime: "20:30:00",
        uploadMaxSize: 629145600
      };
      // https://davidwalsh.name/html5-video-duration

      // console.log( 'Dropzone init...' );

			this.initFunctions();
			this.initDropZone();
		}
    initFunctions() {
      const thisClass = this;
      if( typeof __ !== 'function' ) {
        function __( text, domain ) {
          return text;
        }
      }
    }
		initDropZone() {
			const thisClass = this;var theInterval, zones, dropzone, args;
      Dropzone.autoDiscover = false;thisClass.dropzones = [];
      args = thisClass.getOptions();
      theInterval = setInterval(() => {
        zones = document.querySelectorAll( thisClass.selector + ':not([data-handled])' );
        // console.log( zones );
        // if( zones.length >= 1 ) {
          zones.forEach( function( zone, i ) {
            zone.dataset.handled = true;
            if( zone.dataset.config ) {args = args.extend( JSON.parse( zone.dataset.config ) );}
            dropzone = new Dropzone( thisClass.selector, args );
            thisClass.dropzones.push( { order: 'i', elem: 'e', zone: dropzone } );
            thisClass.initHooks( dropzone );
            // console.log( dropzone );
          } );
        // }
      }, 1500 );
		}
    previewTemplate() {
      return (`
      <div class="dz-preview dz-file-preview">
        <div class="dz-image"><img data-dz-thumbnail /></div>
        <div class="dz-details">
          <div class="dz-size"><span data-dz-size></span></div>
          <div class="dz-filename"><span data-dz-name></span></div>
        </div>
        <div class="dz-progress">
          <span class="dz-upload" data-dz-uploadprogress></span>
        </div>
        <div class="dz-error-message"><span data-dz-errormessage></span></div>
        <div class="dz-success-mark">
          <svg
            width="54"
            height="54"
            viewBox="0 0 54 54"
            fill="white"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M10.2071 29.7929L14.2929 25.7071C14.6834 25.3166 15.3166 25.3166 15.7071 25.7071L21.2929 31.2929C21.6834 31.6834 22.3166 31.6834 22.7071 31.2929L38.2929 15.7071C38.6834 15.3166 39.3166 15.3166 39.7071 15.7071L43.7929 19.7929C44.1834 20.1834 44.1834 20.8166 43.7929 21.2071L22.7071 42.2929C22.3166 42.6834 21.6834 42.6834 21.2929 42.2929L10.2071 31.2071C9.81658 30.8166 9.81658 30.1834 10.2071 29.7929Z"
            />
          </svg>
        </div>
        <div class="dz-error-mark">
          <svg
            width="54"
            height="54"
            viewBox="0 0 54 54"
            fill="white"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M26.2929 20.2929L19.2071 13.2071C18.8166 12.8166 18.1834 12.8166 17.7929 13.2071L13.2071 17.7929C12.8166 18.1834 12.8166 18.8166 13.2071 19.2071L20.2929 26.2929C20.6834 26.6834 20.6834 27.3166 20.2929 27.7071L13.2071 34.7929C12.8166 35.1834 12.8166 35.8166 13.2071 36.2071L17.7929 40.7929C18.1834 41.1834 18.8166 41.1834 19.2071 40.7929L26.2929 33.7071C26.6834 33.3166 27.3166 33.3166 27.7071 33.7071L34.7929 40.7929C35.1834 41.1834 35.8166 41.1834 36.2071 40.7929L40.7929 36.2071C41.1834 35.8166 41.1834 35.1834 40.7929 34.7929L33.7071 27.7071C33.3166 27.3166 33.3166 26.6834 33.7071 26.2929L40.7929 19.2071C41.1834 18.8166 41.1834 18.1834 40.7929 17.7929L36.2071 13.2071C35.8166 12.8166 35.1834 12.8166 34.7929 13.2071L27.7071 20.2929C27.3166 20.6834 26.6834 20.6834 26.2929 20.2929Z"
            />
          </svg>
        </div>
      </div>
      `);
    }
    initHooks( dropzone ) {
      const thisClass = this;var zone;
      window.dropZoneField = dropzone;

      dropzone.on( "addedfile", ( file ) => {
        // console.log( `File added: ${file.name}` );
      } );
      dropzone.on( "sending", function( file, xhr, formData ) {
        // Will send the filesize along with the file as POST data.
        // formData.append( "filesize", file.size );
        formData.append( 'action', 'gravityformsflutterwaveaddons/project/filesystem/upload' );
        formData.append( '_nonce', thisClass.ajaxNonce );
        // document.querySelector( '.woocommerce-checkout-payment' ).classList.add( 'still-uploading' );
      } );
      dropzone.on( "complete", function( file, xhr, formData ) {
        // document.querySelector( '.woocommerce-checkout-payment' ).classList.remove( 'still-uploading' );
      } );
      dropzone.on( "removedfile", function( file ) {
        // console.log( 'To remove ' + file.name );
        var formdata = new FormData();
        formdata.append( 'todelete', file.name );
        formdata.append( 'fileinfo', JSON.stringify( file ) );
        formdata.append( 'action', 'gravityformsflutterwaveaddons/project/filesystem/remove' );
        formdata.append( '_nonce', thisClass.ajaxNonce );
        thisClass.send( formdata );
      } );
      document.onpaste = function(event) {
        var items = ( event.clipboardData || event.originalEvent.clipboardData ).items;
        if( items.length > 0 ) {
          items.forEach( (item) => {
            if( item.kind === 'file' ) {
              dropzone.addFile( item.getAsFile() );
            // } else {console.log( item );
            }
          } );
        }
      }
    }
    getOptions( e = false ) {
      const thisClass = this;
      return {
        /**
         * Has to be specified on elements other than form (or when the form doesn't
         * have an `action` attribute).
         *
         * You can also provide a function that will be called with `files` and
         * `dataBlocks`  and must return the url as string.
         */
        // url: null,
        url: thisClass.ajaxUrl,
      
        /**
         * Can be changed to `"put"` if necessary. You can also provide a function
         * that will be called with `files` and must return the method (since `v3.12.0`).
         */
        method: "post",
      
        /**
         * Will be set on the XHRequest.
         */
        withCredentials: false,
      
        /**
         * The timeout for the XHR requests in milliseconds (since `v4.4.0`).
         * If set to null or 0, no timeout is going to be set.
         */
        timeout: null,
      
        /**
         * How many file uploads to process in parallel (See the
         * Enqueuing file uploads documentation section for more info)
         */
        parallelUploads: 1,
      
        /**
         * Whether to send multiple files in one request. If
         * this it set to true, then the fallback file input element will
         * have the `multiple` attribute as well. This option will
         * also trigger additional events (like `processingmultiple`). See the events
         * documentation section for more information.
         */
        uploadMultiple: true,
      
        /**
         * Whether you want files to be uploaded in chunks to your server. This can't be
         * used in combination with `uploadMultiple`.
         *
         * See [chunksUploaded](#config-chunksUploaded) for the callback to finalise an upload.
         */
        chunking: false,
      
        /**
         * If `chunking` is enabled, this defines whether **every** file should be chunked,
         * even if the file size is below chunkSize. This means, that the additional chunk
         * form data will be submitted and the `chunksUploaded` callback will be invoked.
         */
        forceChunking: false,
      
        /**
         * If `chunking` is `true`, then this defines the chunk size in bytes.
         */
        chunkSize: 2 * 1024 * 1024,
      
        /**
         * If `true`, the individual chunks of a file are being uploaded simultaneously.
         */
        parallelChunkUploads: false,
      
        /**
         * Whether a chunk should be retried if it fails.
         */
        retryChunks: false,
      
        /**
         * If `retryChunks` is true, how many times should it be retried.
         */
        retryChunksLimit: 3,
      
        /**
         * The maximum filesize (in MiB) that is allowed to be uploaded.
         */
        maxFilesize: 20480,
      
        /**
         * The name of the file param that gets transferred.
         * **NOTE**: If you have the option  `uploadMultiple` set to `true`, then
         * Dropzone will append `[]` to the name.
         */
        paramName: "file",
      
        /**
         * Whether thumbnails for images should be generated
         */
        createImageThumbnails: true,
      
        /**
         * In MB. When the filename exceeds this limit, the thumbnail will not be generated.
         */
        maxThumbnailFilesize: 10,
      
        /**
         * If `null`, the ratio of the image will be used to calculate it.
         */
        thumbnailWidth: 120,
      
        /**
         * The same as `thumbnailWidth`. If both are null, images will not be resized.
         */
        thumbnailHeight: 120,
      
        /**
         * How the images should be scaled down in case both, `thumbnailWidth` and `thumbnailHeight` are provided.
         * Can be either `contain` or `crop`.
         */
        thumbnailMethod: "crop",
      
        /**
         * If set, images will be resized to these dimensions before being **uploaded**.
         * If only one, `resizeWidth` **or** `resizeHeight` is provided, the original aspect
         * ratio of the file will be preserved.
         *
         * The `options.transformFile` function uses these options, so if the `transformFile` function
         * is overridden, these options don't do anything.
         */
        resizeWidth: null,
      
        /**
         * See `resizeWidth`.
         */
        resizeHeight: null,
      
        /**
         * The mime type of the resized image (before it gets uploaded to the server).
         * If `null` the original mime type will be used. To force jpeg, for example, use `image/jpeg`.
         * See `resizeWidth` for more information.
         */
        resizeMimeType: null,
      
        /**
         * The quality of the resized images. See `resizeWidth`.
         */
        resizeQuality: 0.8,
      
        /**
         * How the images should be scaled down in case both, `resizeWidth` and `resizeHeight` are provided.
         * Can be either `contain` or `crop`.
         */
        resizeMethod: "contain",
      
        /**
         * The base that is used to calculate the **displayed** filesize. You can
         * change this to 1024 if you would rather display kibibytes, mebibytes,
         * etc... 1024 is technically incorrect, because `1024 bytes` are `1 kibibyte`
         * not `1 kilobyte`. You can change this to `1024` if you don't care about
         * validity.
         */
        filesizeBase: 1000,
      
        /**
         * If not `null` defines how many files this Dropzone handles. If it exceeds,
         * the event `maxfilesexceeded` will be called. The dropzone element gets the
         * class `dz-max-files-reached` accordingly so you can provide visual
         * feedback.
         */
        maxFiles: 10,
      
        /**
         * An optional object to send additional headers to the server. Eg:
         * `{ "My-Awesome-Header": "header value" }`
         */
        headers: null,
      
        /**
         * Should the default headers be set or not?
         * Accept: application/json <- for requesting json response
         * Cache-Control: no-cache <- Request shouldnt be cached
         * X-Requested-With: XMLHttpRequest <- We sent the request via XMLHttpRequest
         */
        defaultHeaders: true,
      
        /**
         * If `true`, the dropzone element itself will be clickable, if `false`
         * nothing will be clickable.
         *
         * You can also pass an HTML element, a CSS selector (for multiple elements)
         * or an array of those. In that case, all of those elements will trigger an
         * upload when clicked.
         */
        clickable: true,
      
        /**
         * Whether hidden files in directories should be ignored.
         */
        ignoreHiddenFiles: true,
      
        /**
         * The default implementation of `accept` checks the file's mime type or
         * extension against this list. This is a comma separated list of mime
         * types or file extensions.
         *
         * Eg.: `image/*,application/pdf,.psd`
         *
         * If the Dropzone is `clickable` this option will also be used as
         * [`accept`](https://developer.mozilla.org/en-US/docs/HTML/Element/input#attr-accept)
         * parameter on the hidden file input as well.
         * video/*,image/*,audio/*,application/pdf,.psd
         */
        // acceptedFiles: '*',
      
        /**
         * **Deprecated!**
         * Use acceptedFiles instead.
         */
        acceptedMimeTypes: null,
      
        /**
         * If false, files will be added to the queue but the queue will not be
         * processed automatically.
         * This can be useful if you need some additional user input before sending
         * files (or if you want want all files sent at once).
         * If you're ready to send the file simply call `myDropzone.processQueue()`.
         *
         * See the [enqueuing file uploads](#enqueuing-file-uploads) documentation
         * section for more information.
         */
        autoProcessQueue: true,
      
        /**
         * If false, files added to the dropzone will not be queued by default.
         * You'll have to call `enqueueFile(file)` manually.
         */
        autoQueue: true,
      
        /**
         * If `true`, this will add a link to every file preview to remove or cancel (if
         * already uploading) the file. The `dictCancelUpload`, `dictCancelUploadConfirmation`
         * and `dictRemoveFile` options are used for the wording.
         */
        addRemoveLinks: true,
      
        /**
         * Defines where to display the file previews – if `null` the
         * Dropzone element itself is used. Can be a plain `HTMLElement` or a CSS
         * selector. The element should have the `dropzone-previews` class so
         * the previews are displayed properly.
         */
        previewsContainer: null,
      
        /**
         * Set this to `true` if you don't want previews to be shown.
         */
        disablePreviews: false,
      
        /**
         * This is the element the hidden input field (which is used when clicking on the
         * dropzone to trigger file selection) will be appended to. This might
         * be important in case you use frameworks to switch the content of your page.
         *
         * Can be a selector string, or an element directly.
         */
        hiddenInputContainer: "body",
      
        /**
         * If null, no capture type will be specified
         * If camera, mobile devices will skip the file selection and choose camera
         * If microphone, mobile devices will skip the file selection and choose the microphone
         * If camcorder, mobile devices will skip the file selection and choose the camera in video mode
         * On apple devices multiple must be set to false.  AcceptedFiles may need to
         * be set to an appropriate mime type (e.g. "image/*", "audio/*", or "video/*").
         */
        capture: null,
      
        /**
         * **Deprecated**. Use `renameFile` instead.
         */
        renameFilename: null,
      
        /**
         * A function that is invoked before the file is uploaded to the server and renames the file.
         * This function gets the `File` as argument and can use the `file.name`. The actual name of the
         * file that gets used during the upload can be accessed through `file.upload.filename`.
         */
        renameFile: null,
      
        /**
         * If `true` the fallback will be forced. This is very useful to test your server
         * implementations first and make sure that everything works as
         * expected without dropzone if you experience problems, and to test
         * how your fallbacks will look.
         */
        forceFallback: false,
      
        /**
         * The text used before any files are dropped.
         */
        dictDefaultMessage: thisClass.__( 'Drop files here to upload', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * The text that replaces the default message text it the browser is not supported.
         */
        dictFallbackMessage:
        thisClass.__( 'Your browser does not support drag\'n\'drop file uploads.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * The text that will be added before the fallback form.
         * If you provide a  fallback element yourself, or if this option is `null` this will
         * be ignored.
         */
        dictFallbackText:
        thisClass.__( 'Please use the fallback form below to upload your files like in the olden days.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If the filesize is too big.
         * `{{filesize}}` and `{{maxFilesize}}` will be replaced with the respective configuration values.
         */
        dictFileTooBig:
          thisClass.__( 'File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If the file doesn't match the file type.
         */
        dictInvalidFileType: thisClass.__( 'You can\'t upload files of this type.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If the server response was invalid.
         * `{{statusCode}}` will be replaced with the servers status code.
         */
        dictResponseError: thisClass.__( 'Server responded with {{statusCode}} code.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If `addRemoveLinks` is true, the text to be used for the cancel upload link.
         */
        dictCancelUpload: thisClass.textIcon( thisClass.__( 'Cancel upload', 'woocommerce-checkout-video-snippet' ), 'cross.svg' ),
      
        /**
         * The text that is displayed if an upload was manually canceled
         */
        dictUploadCanceled: thisClass.__( 'Upload canceled.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If `addRemoveLinks` is true, the text to be used for confirmation when cancelling upload.
         */
        dictCancelUploadConfirmation: thisClass.__( 'Are you sure you want to cancel this upload?', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * If `addRemoveLinks` is true, the text to be used to remove a file.
         */
        dictRemoveFile: thisClass.textIcon( thisClass.__( 'Remove file', 'woocommerce-checkout-video-snippet' ), 'cross.svg' ),
      
        /**
         * If this is not null, then the user will be prompted before removing a file.
         */
        dictRemoveFileConfirmation: null,
      
        /**
         * Displayed if `maxFiles` is st and exceeded.
         * The string `{{maxFiles}}` will be replaced by the configuration value.
         */
        dictMaxFilesExceeded: thisClass.__( 'You can not upload any more files.', 'woocommerce-checkout-video-snippet' ),
      
        /**
         * Allows you to translate the different units. Starting with `tb` for terabytes and going down to
         * `b` for bytes.
         */
        dictFileSizeUnits: { tb: thisClass.__( 'TB', 'woocommerce-checkout-video-snippet' ), gb: thisClass.__( 'GB', 'woocommerce-checkout-video-snippet' ), mb: thisClass.__( 'MB', 'woocommerce-checkout-video-snippet' ), kb: thisClass.__( 'KB', 'woocommerce-checkout-video-snippet' ), b: thisClass.__( 'b', 'woocommerce-checkout-video-snippet' ) },
        /**
         * Called when dropzone initialized
         * You can add event listeners here
         */
        init: function () {
          let dropzone = this;var e;
          Object.keys( thisClass.videoClips ).forEach( ( i ) => {
            e = thisClass.videoClips[i];
            e.type = ( e.type ) ? e.type : 'video/webm';
            e.name = ( ! e.name ) ? '' : ( ( e.name[0] ) ? e.name[0] : e.name );
            dropzone.displayExistingFile( e, e.full_url );
            // console.log( e );
            // dropzone.options.maxFiles = ( dropzone.options.maxFiles - 1 );
          } );

          // fetch("url").then(res => res.blob()).then((currentBlob) => {
          //   const generateFile = new File([currentBlob], "filename.jpg", {
          //     type: currentBlob.type,
          //   });
          //   myDropzone.addFile(generateFile);
          // });

        },
      
        /**
         * Can be an **object** of additional parameters to transfer to the server, **or** a `Function`
         * that gets invoked with the `files`, `xhr` and, if it's a chunked upload, `chunk` arguments. In case
         * of a function, this needs to return a map.
         *
         * The default implementation does nothing for normal uploads, but adds relevant information for
         * chunked uploads.
         *
         * This is the same as adding hidden input fields in the form element.
         */
        params: function (files, xhr, chunk) {
          if (chunk) {
            return {
              dzuuid: chunk.file.upload.uuid,
              dzchunkindex: chunk.index,
              dztotalfilesize: chunk.file.size,
              dzchunksize: this.options.chunkSize,
              dztotalchunkcount: chunk.file.upload.totalChunkCount,
              dzchunkbyteoffset: chunk.index * this.options.chunkSize,
            };
          }
        },
        // params: {action: '', _nonce: ''},
      
        /**
         * A function that gets a [file](https://developer.mozilla.org/en-US/docs/DOM/File)
         * and a `done` function as parameters.
         *
         * If the done function is invoked without arguments, the file is "accepted" and will
         * be processed. If you pass an error message, the file is rejected, and the error
         * message will be displayed.
         * This function will not be called if the file is too big or doesn't match the mime types.
         */
        // accept(file, done) {return done();},
        accept: function ( file, done ) {
        // https://stackoverflow.com/questions/48214047/dropzone-js-uploading-base64-strings
          // var reader = new FileReader();
          // reader.onload = function (event) {
          //   imageData = event.target.result;
          //   console.log(imageData);
          // };
          // reader.readAsDataURL(file);
          // if( thisClass.videoDurationValidate( file ) ) {
          if( true ) {
            return done();
          } else {
            return false;
          }
        },

      
        /**
         * The callback that will be invoked when all chunks have been uploaded for a file.
         * It gets the file for which the chunks have been uploaded as the first parameter,
         * and the `done` function as second. `done()` needs to be invoked when everything
         * needed to finish the upload process is done.
         */
        chunksUploaded: function (file, done) {
          done();
        },
      
        /**
         * Sends the file as binary blob in body instead of form data.
         * If this is set, the `params` option will be ignored.
         * It's an error to set this to `true` along with `uploadMultiple` since
         * multiple files cannot be in a single binary body.
         */
        binaryBody: false,
      
        /**
         * Gets called when the browser is not supported.
         * The default implementation shows the fallback input field and adds
         * a text.
         */
        fallback: function () {
          // This code should pass in IE7... :(
          let messageElement;
          this.element.className = `${this.element.className} dz-browser-not-supported`;
      
          for (let child of this.element.getElementsByTagName("div")) {
            if (/(^| )dz-message($| )/.test(child.className)) {
              messageElement = child;
              child.className = "dz-message"; // Removes the 'dz-default' class
              break;
            }
          }
          if (!messageElement) {
            messageElement = Dropzone.createElement(
              '<div class="dz-message"><span></span></div>'
            );
            this.element.appendChild(messageElement);
          }
      
          let span = messageElement.getElementsByTagName("span")[0];
          if (span) {
            if (span.textContent != null) {
              span.textContent = this.options.dictFallbackMessage;
            } else if (span.innerText != null) {
              span.innerText = this.options.dictFallbackMessage;
            }
          }
      
          return this.element.appendChild(this.getFallbackForm());
        },
      
        /**
         * Gets called to calculate the thumbnail dimensions.
         *
         * It gets `file`, `width` and `height` (both may be `null`) as parameters and must return an object containing:
         *
         *  - `srcWidth` & `srcHeight` (required)
         *  - `trgWidth` & `trgHeight` (required)
         *  - `srcX` & `srcY` (optional, default `0`)
         *  - `trgX` & `trgY` (optional, default `0`)
         *
         * Those values are going to be used by `ctx.drawImage()`.
         */
        resize: function (file, width, height, resizeMethod) {
          let info = {
            srcX: 0,
            srcY: 0,
            srcWidth: file.width,
            srcHeight: file.height,
          };
      
          let srcRatio = file.width / file.height;
      
          // Automatically calculate dimensions if not specified
          if (width == null && height == null) {
            width = info.srcWidth;
            height = info.srcHeight;
          } else if (width == null) {
            width = height * srcRatio;
          } else if (height == null) {
            height = width / srcRatio;
          }
      
          // Make sure images aren't upscaled
          width = Math.min(width, info.srcWidth);
          height = Math.min(height, info.srcHeight);
      
          let trgRatio = width / height;
      
          if (info.srcWidth > width || info.srcHeight > height) {
            // Image is bigger and needs rescaling
            if (resizeMethod === "crop") {
              if (srcRatio > trgRatio) {
                info.srcHeight = file.height;
                info.srcWidth = info.srcHeight * trgRatio;
              } else {
                info.srcWidth = file.width;
                info.srcHeight = info.srcWidth / trgRatio;
              }
            } else if (resizeMethod === "contain") {
              // Method 'contain'
              if (srcRatio > trgRatio) {
                height = width / srcRatio;
              } else {
                width = height * srcRatio;
              }
            } else {
              throw new Error(`Unknown resizeMethod '${resizeMethod}'`);
            }
          }
      
          info.srcX = (file.width - info.srcWidth) / 2;
          info.srcY = (file.height - info.srcHeight) / 2;
      
          info.trgWidth = width;
          info.trgHeight = height;
      
          return info;
        },
      
        /**
         * Can be used to transform the file (for example, resize an image if necessary).
         *
         * The default implementation uses `resizeWidth` and `resizeHeight` (if provided) and resizes
         * images according to those dimensions.
         *
         * Gets the `file` as the first parameter, and a `done()` function as the second, that needs
         * to be invoked with the file when the transformation is done.
         */
        transformFile: function (file, done) {
          if (
            (this.options.resizeWidth || this.options.resizeHeight) &&
            file.type.match(/image.*/)
          ) {
            return this.resizeImage(
              file,
              this.options.resizeWidth,
              this.options.resizeHeight,
              this.options.resizeMethod,
              done
            );
          } else {
            return done(file);
          }
        },
      
        /**
         * A string that contains the template used for each dropped
         * file. Change it to fulfill your needs but make sure to properly
         * provide all elements.
         *
         * If you want to use an actual HTML element instead of providing a String
         * as a config option, you could create a div with the id `tpl`,
         * put the template inside it and provide the element like this:
         *
         *     document
         *       .querySelector('#tpl')
         *       .innerHTML
         *
         */
        previewTemplate: thisClass.previewTemplate(),
      
        /*
         Those functions register themselves to the events on init and handle all
         the user interface specific stuff. Overwriting them won't break the upload
         but can break the way it's displayed.
         You can overwrite them if you don't like the default behavior. If you just
         want to add an additional event handler, register it on the dropzone object
         and don't overwrite those options.
         */
      
        // Those are self explanatory and simply concern the DragnDrop.
        drop(e) {
          return this.element.classList.remove("dz-drag-hover");
        },
        dragstart(e) {},
        dragend(e) {
          return this.element.classList.remove("dz-drag-hover");
        },
        dragenter(e) {
          return this.element.classList.add("dz-drag-hover");
        },
        dragover(e) {
          return this.element.classList.add("dz-drag-hover");
        },
        dragleave(e) {
          return this.element.classList.remove("dz-drag-hover");
        },
      
        paste(e) {},
      
        // Called whenever there are no files left in the dropzone anymore, and the
        // dropzone should be displayed as if in the initial state.
        reset() {
          return this.element.classList.remove("dz-started");
        },
      
        // Called when a file is added to the queue
        // Receives `file`
        addedfile(file) {
          if (this.element === this.previewsContainer) {
            this.element.classList.add("dz-started");
          }
      
          if (this.previewsContainer && !this.options.disablePreviews) {
            file.previewElement = Dropzone.createElement(
              this.options.previewTemplate.trim()
            );
            file.previewTemplate = file.previewElement; // Backwards compatibility
      
            this.previewsContainer.appendChild(file.previewElement);
            for (var node of file.previewElement.querySelectorAll("[data-dz-name]")) {
              node.textContent = file.name;
            }
            for (node of file.previewElement.querySelectorAll("[data-dz-size]")) {
              node.innerHTML = this.filesize(file.size);
            }
      
            if (this.options.addRemoveLinks) {
              file._removeLink = Dropzone.createElement(
                `<a class="dz-remove" href="javascript:undefined;" data-dz-remove>${this.options.dictRemoveFile}</a>`
              );
              file.previewElement.appendChild(file._removeLink);
            }
      
            let removeFileEvent = (e) => {
              e.preventDefault();
              e.stopPropagation();
              if (file.status === Dropzone.UPLOADING) {
                return Dropzone.confirm(
                  this.options.dictCancelUploadConfirmation,
                  () => this.removeFile(file)
                );
              } else {
                if (this.options.dictRemoveFileConfirmation) {
                  return Dropzone.confirm(
                    this.options.dictRemoveFileConfirmation,
                    () => this.removeFile(file)
                  );
                } else {
                  return this.removeFile(file);
                }
              }
            };
      
            for (let removeLink of file.previewElement.querySelectorAll(
              "[data-dz-remove]"
            )) {
              removeLink.addEventListener("click", removeFileEvent);
            }
          }
        },
      
        // Called whenever a file is removed.
        removedfile(file) {
          if (file.previewElement != null && file.previewElement.parentNode != null) {
            file.previewElement.parentNode.removeChild(file.previewElement);
          }
          return this._updateMaxFilesReachedClass();
        },
      
        // Called when a thumbnail has been generated
        // Receives `file` and `dataUrl`
        thumbnail(file, dataUrl) {
          if (file.previewElement) {
            file.previewElement.classList.remove("dz-file-preview");
            for (let thumbnailElement of file.previewElement.querySelectorAll(
              "[data-dz-thumbnail]"
            )) {
              thumbnailElement.alt = file.name;
              thumbnailElement.src = dataUrl;
            }
      
            return setTimeout(
              () => file.previewElement.classList.add("dz-image-preview"),
              1
            );
          }
        },
      
        // Called whenever an error occurs
        // Receives `file` and `message`
        error(file, message) {
          if (file.previewElement) {
            file.previewElement.classList.add("dz-error");
            if (typeof message !== "string" && message.error) {
              message = message.error;
            }
            for (let node of file.previewElement.querySelectorAll(
              "[data-dz-errormessage]"
            )) {
              node.textContent = message;
            }
          }
        },
      
        errormultiple() {},
      
        // Called when a file gets processed. Since there is a cue, not all added
        // files are processed immediately.
        // Receives `file`
        processing(file) {
          if (file.previewElement) {
            file.previewElement.classList.add("dz-processing");
            if (file._removeLink) {
              return (file._removeLink.innerHTML = this.options.dictCancelUpload);
            }
          }
        },
      
        processingmultiple() {},
      
        // Called whenever the upload progress gets updated.
        // Receives `file`, `progress` (percentage 0-100) and `bytesSent`.
        // To get the total number of bytes of the file, use `file.size`
        uploadprogress(file, progress, bytesSent) {
          if (file.previewElement) {
            for (let node of file.previewElement.querySelectorAll(
              "[data-dz-uploadprogress]"
            )) {
              node.nodeName === "PROGRESS"
                ? (node.value = progress)
                : (node.style.width = `${progress}%`);
            }
          }
        },
      
        // Called whenever the total upload progress gets updated.
        // Called with totalUploadProgress (0-100), totalBytes and totalBytesSent
        totaluploadprogress() {},
      
        // Called just before the file is sent. Gets the `xhr` object as second
        // parameter, so you can modify it (for example to add a CSRF token) and a
        // `formData` object to add additional information.
        sending() {},
      
        sendingmultiple() {},
      
        // When the complete upload is finished and successful
        // Receives `file`
        success(file) {
          if (file.previewElement) {
            return file.previewElement.classList.add("dz-success");
          }
        },
      
        successmultiple() {},
      
        // When the upload is canceled.
        canceled(file) {
          return this.emit("error", file, this.options.dictUploadCanceled);
        },
      
        canceledmultiple() {},
      
        // When the upload is finished, either with success or an error.
        // Receives `file`
        complete(file) {
          if (file._removeLink) {
            file._removeLink.innerHTML = this.options.dictRemoveFile;
          }
          if (file.previewElement) {
            return file.previewElement.classList.add("dz-complete");
          }
        },
      
        completemultiple() {},
      
        maxfilesexceeded() {},
      
        maxfilesreached() {},
      
        queuecomplete() {},
      
        addedfiles() {},
      };
    }
    displayExistingFile() {
      // https://github.com/dropzone/dropzone/discussions/1909
      const thisClass = this;var Dropzone = thisClass.dropzones[0];
      // Dropzone.options.myDropzone = {
      //   init: function() {
      //     let myDropzone = this;
      //     // If you only have access to the original image sizes on your server,
      //     // and want to resize them in the browser:
      //     let mockFile = { name: "Filename 2", size: 12345 };
      //     myDropzone.displayExistingFile(mockFile, "https://i.picsum.photos/id/959/600/600.jpg");

      //     // If the thumbnail is already in the right size on your server:
      //     let mockFile = { name: "Filename", size: 12345 };
      //     let callback = null; // Optional callback when it's done
      //     let crossOrigin = null; // Added to the `img` tag for crossOrigin handling
      //     let resizeThumbnail = false; // Tells Dropzone whether it should resize the image first
      //     myDropzone.displayExistingFile(mockFile, "https://i.picsum.photos/id/959/120/120.jpg", callback, crossOrigin, resizeThumbnail);

      //     // If you use the maxFiles option, make sure you adjust it to the
      //     // correct amount:
      //     let fileCountOnServer = 2; // The number of files already uploaded
      //     myDropzone.options.maxFiles = myDropzone.options.maxFiles - fileCountOnServer;
      //   }
      // };
    }
    secondsToTime(in_seconds) {
      const thisClass =this;
      var time = '';
      in_seconds = parseFloat(in_seconds.toFixed(2));
      var hours = Math.floor(in_seconds / 3600);
      var minutes = Math.floor((in_seconds - (hours * 3600)) / 60);
      var seconds = in_seconds - (hours * 3600) - (minutes * 60);
      //seconds = Math.floor( seconds );
      seconds = seconds.toFixed(0);
      if (hours < 10) {hours = "0" + hours;}
      if (minutes < 10) {minutes = "0" + minutes;}
      if (seconds < 10) {seconds = "0" + seconds;}
      var time = minutes + ':' + seconds;
      return time;
    }
    videoDurationValidate( file ) {
      const thisClass =this;
      var reader = new FileReader();
      var fileSize = file.size;
      console.log( file );

      if ( fileSize > thisClass.videoControl.uploadMaxSize ) {
        toast.show({title: thisClass.__( 'Filesize exceed it maximum limit 30MB.', 'gravitylovesflutterwave' ), position: 'bottomright', type: 'warn'});
        return false;
      } else {
        reader.onload = function(e) {
          if (file.type == "video/mp4" || file.type == "video/ogg" || file.type == "video/webm") {
            var videoElement = document.createElement('video');
            videoElement.src = e.target.result;
            var timer = setInterval(function() {
              if (videoElement.readyState === 4) {
                getTime = thisClass.secondsToTime(videoElement.duration);
                if (getTime > thisClass.videoControl.videoMaxTime ) {
                  toast.show({title: thisClass.__( 'Video exceed it\'s duration limit.', 'gravitylovesflutterwave' ) + thisClass.videoControl.videoMaxTime, position: 'bottomright', type: 'warn'});
                  return false;
                } else {
                  return true;
                }
                clearInterval(timer);
              }
            }, 500)
          } else if (file.type == "audio/mpeg" || file.type == "audio/wav" || file.type == "audio/ogg") {

            var audioElement = document.createElement('audio');
            audioElement.src = e.target.result;
            var timer = setInterval(function() {
              if (audioElement.readyState === 4) {
                getTime = thisClass.secondsToTime(audioElement.duration);
                if (getTime > thisClass.videoControl.audioMaxTime ) {
                  toast.show({title: thisClass.__( 'Audio exceed it\'s duration limit.', 'gravitylovesflutterwave' ) + thisClass.videoControl.audioMaxTime, position: 'bottomright', type: 'warn'});
                  return false;
                } else {
                  return true;
                }
                clearInterval(timer);
              }
            }, 500)
          } else {
            var timer = setInterval(function() {
              if (file) {
                toast.show({title: thisClass.__( 'Invalid file formate.', 'gravitylovesflutterwave' ) + file.type, position: 'bottomright', type: 'warn'});
                clearInterval(timer);
                return false;
              }
            }, 500)

          }

        };
        if (file) {
          reader.readAsDataURL(file);

        } else {
          alert('nofile');
        }

      }
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
    send( data ) {
      const thisClass = this;var message;
      $.ajax({
        url: thisClass.ajaxUrl,
        type: "POST",
        data: data,    
        cache: false,
        contentType: false,
        processData: false,
        success: function( json ) {
          // console.log( json );
          message = ( json.data.message ) ? json.data.message : json.data;
          if( json.success ) {
            toast.show({title: message, position: 'bottomright', type: 'info'});
          } else {
            toast.show({title: message, position: 'bottomright', type: 'warn'});
          }
        },
        error: function( err ) {
          console.log( err.responseText );
        }
      });
    }
	}
	new FWPProject_DropZone();
} )( jQuery );
// ( typeof jQuery !== 'undefined' ) ? jQuery : false



/**
 * Php implimentations.
 * 
 * $ds = DIRECTORY_SEPARATOR;$storeFolder = 'uploads';if (!empty($_FILES)) {$tempFile = $_FILES['file']['tmp_name'];            $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;$targetFile =  $targetPath. $_FILES['file']['name'];move_uploaded_file($tempFile,$targetFile);}
 */