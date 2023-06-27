/**
 *
 *
 * @package Future WordPress Inc.
 */

(function () {
  class FWPProject_YouTube {
    constructor() {
        this.lastResult = {};
        this.lastError  = {};
        this.curntPopUp = false;
        this.ajaxUrl = siteConfig?.ajaxUrl ?? '';
        this.ajaxNonce = siteConfig?.ajax_nonce ?? '';
        this.lastAjax	 = false;
        var i18n = siteConfig?.i18n ?? {};
        this.i18n = {
            loading               : 'Loading....',
            i_confirm_it          : 'Yes I confirm it',
            confirming            : 'Confirming',
            successful            : 'Successful',
            press_esc             : 'Press ESC to close.',
            ...i18n
        }

        this.setup_hooks();
    }
    setup_hooks() {
      this.watchGrids();
      this.watchEscKey();
    }
    createPopUp( playlist = 'PLIbMQVUKxl0QX4w0O5apjqA_WSh0Pca_0' ) {
      const thisClass = this;
      return new Promise( async (resolve, reject) => {
        document.querySelectorAll( '.fwp-yt-popup' ).forEach( ( pop ) => {pop.remove();} );
        var popupwraper, popupbackdrop, popupcontainer, popupwrap, iframe, close, sidebar, list, item, videoId, itemlink, itemrow, itemleft, thumb, image, duration, itemright, title, metas, channel, seperator, lastmodified;
        popupwraper = document.createElement( 'div' );popupwraper.classList.add( 'fwp-yt-popup' );popupwraper.setAttribute( 'title', thisClass.i18n.press_esc );
        popupbackdrop = document.createElement( 'div' );popupbackdrop.classList.add( 'popup-backdrop' );popupwraper.appendChild( popupbackdrop );
        popupcontainer = document.createElement( 'div' );popupcontainer.classList.add( 'popup-center__container' );
        popupwrap = document.createElement( 'div' );popupwrap.classList.add( 'popup-center__body' );
        close = document.createElement( 'div' );close.classList.add( 'popup-center__close', 'fas', 'fa-times' );popupwrap.appendChild( close );
        iframe = document.createElement( 'iframe' );iframe.classList.add( 'popup-center__iframe' );
        iframe.width = 560;iframe.height = 315;iframe.frameborder = '0';iframe.allowfullscreen = 'true';
        iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
        iframe.src = 'https://www.youtube.com/embed/videoseries?list=' + playlist;popupwrap.appendChild( iframe );
        sidebar = document.createElement( 'div' );sidebar.classList.add( 'popup-sidebar-right' );
        list = document.createElement( 'ul' );list.classList.add( 'popup-playlist-items' );
        
        playlist = await fetch( thisClass.ajaxUrl + '?action=special_youtube_playlist_api_integration_plugin_playlist&playlist=' + playlist + '&key=' + thisClass.ajaxNonce ).then(response => response.json()).then(data => {
            // console.log(data);
            return data;
        }).catch(error => {
            console.error(error);
            reject( error );return error;
        });
        playlist = ( playlist && playlist.data && playlist.data.items ) ? playlist.data.items : [];
        playlist.forEach( ( e, i ) => {
            videoId = ( e.contentDetails ) ? e.contentDetails.videoId : e.snippet.resourceId.videoId;
            item = document.createElement( 'li' );item.classList.add( 'playlist-single__item' );
            itemlink = document.createElement( 'a' );itemlink.classList.add( 'playlist-single__itemlink' );
            itemlink.title = e.snippet.title;itemlink.target= '_blank';itemlink.dataset.id = videoId;
            itemlink.href = 'https://www.youtube.com/watch?v=' + videoId;
            itemrow = document.createElement( 'div' );itemrow.classList.add( 'playlist-single__itemrow' );
            itemleft = document.createElement( 'div' );itemleft.classList.add( 'playlist-single__itemleft' );
            thumb = document.createElement( 'div' );thumb.classList.add( 'playlist-single__thumb' );
            image = document.createElement( 'img' );image.classList.add( 'playlist-single__image' );
            image.src = e.snippet.thumbnails.default.url;image.alt = e.snippet.title;
            duration = document.createElement( 'div' );duration.classList.add( 'playlist-single__duration' );duration.innerHTML = '12:34';
            thumb.appendChild( image );thumb.appendChild( duration );itemleft.appendChild( thumb );itemrow.appendChild( itemleft );
            
            itemright = document.createElement( 'div' );itemright.classList.add( 'playlist-single__itemright' );
            title = document.createElement( 'h3' );title.classList.add( 'playlist-single__title' );title.innerHTML = e.snippet.title;
            metas = document.createElement( 'div' );metas.classList.add( 'playlist-single__metas' );
            channel = document.createElement( 'div' );channel.classList.add( 'playlist-single__channel' );channel.innerHTML = e.snippet.channelTitle;
            seperator = document.createElement( 'div' );seperator.classList.add( 'playlist-single__seperator' );
            lastmodified = document.createElement( 'div' );lastmodified.classList.add( 'playlist-single__lastmodified' );lastmodified.innerHTML = e.snippet.publishedAt;
            
            metas.appendChild( channel );metas.appendChild( seperator );metas.appendChild( lastmodified );
            itemright.appendChild( title );itemright.appendChild( metas );itemrow.appendChild( itemright );
            itemlink.appendChild( itemrow );item.appendChild( itemlink );list.appendChild( item );
        } );

        sidebar.appendChild( list );popupwrap.appendChild( sidebar );popupcontainer.appendChild( popupwrap );
        popupwraper.appendChild( popupbackdrop );popupwraper.appendChild( popupcontainer );

        document.body.appendChild( popupwraper );
        // console.log( popupwraper );
        thisClass.curntPopUp = document.querySelector( '.fwp-yt-popup' );

        document.querySelectorAll( '.popup-backdrop, .popup-center__close' ).forEach( ( pop ) => {pop.addEventListener( 'click', ( e ) => {thisClass.curntPopUp.remove();thisClass.curntPopUp = false;} );} ); // e.target.parentElement
        document.querySelectorAll( '.playlist-single__itemlink' ).forEach( ( item ) => {
            item.addEventListener( 'click', ( e ) => {
                e.preventDefault();
                if( item.dataset.id ) {
                    iframe = document.querySelector( '.popup-center__iframe' );
                    if( iframe ) {
                        iframe.src = 'https://www.youtube.com/embed/' + item.dataset.id;
                        document.querySelectorAll( '.playlist-single__itemlink.active' ).forEach( ( el ) => {el.classList.remove( 'active' );} );
                        item.classList.add( 'active' );
                    }
                    // else {console.log( 'signal 2' );}
                }
                // else {console.log( 'signal 1' );}
            } );
        } );
        resolve( "Popup Created" );
      } );
    }
    watchEscKey() {
      document.addEventListener("keydown", function(event) {
        if( event.key === "Escape" ) {
          document.querySelectorAll( '.fwp-yt-popup' ).forEach( ( pop ) => {pop.remove();} );
        }
      });
    }
    doPreloader( todo ) {
        const thisClass = this;
        if( todo ) {
            var preloader, preload, circle, text, bar;
            preload = document.createElement( 'div' );preload.classList.add( 'fwp-yt-popuppreoad' );
            circle = document.createElement( 'div' );circle.classList.add( 'preload-circle-loader' );preload.appendChild( circle );
            // text = document.createElement( 'div' );text.classList.add( 'preload-loader-text' );text.innerHTML = thisClass.i18n.loading; preload.appendChild( text );
            // bar = document.createElement( 'div' );bar.classList.add( 'preload-progress-bar' );preload.appendChild( bar );
            document.body.appendChild( preload );
            
            

            // <div class="fwp-yt-popuppreoad">
            // <div class="preload-circle-loader"></div>
            // <div class="preload-loader-text">Loading...</div>
            // <div id="preload-progress-bar"></div>
            // </div>
        } else {
            document.querySelectorAll( '.fwp-yt-popuppreoad' ).forEach( ( pop ) => {pop.remove();} );
        }
    }
    watchGrids() {
        const thisClass = this;var theInterval;
        theInterval = setInterval(() => {
            document.querySelectorAll( '.imagehvr .imagehvr-link:not([data-handled=true])' ).forEach( ( el ) => {
                el.dataset.handled = true;
                el.addEventListener( 'click', ( e ) => {
                    e.preventDefault();
                    thisClass.doPreloader( true );
                    thisClass.createPopUp( el.dataset.id ).then( data => {
                        console.error( data );
                    } ).catch(error => {
                      console.error(error);
                    } );
                    thisClass.doPreloader( false );
                } );
            } );
        }, 3000 );
    }
  }
  new FWPProject_YouTube();
})();
