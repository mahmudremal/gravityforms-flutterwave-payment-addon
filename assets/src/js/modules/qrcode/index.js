/**
 * QRCode Generating. https://www.npmjs.com/package/qrcode
 * 
 * @package Future WordPress Inc.
 */

 import QRCode from 'qrcode';
import { toast } from 'toast-notification-alert';
( function ( $ ) {
	class FWPProject_QRCode {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl ?? '';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce ?? '';
			this.buildPath = fwpSiteConfig?.buildPath ?? '';
      this.selector = '.fwp-qrzone-field';
			this.setup_hooks();
		}
    setup_hooks() {
      const thisClass = this;var theInterval, selector, players, css, js, csses, jses;
      thisClass.videoPlayers = [];thisClass.videoRecorders = [];
      theInterval = setInterval( () => {
        document.querySelectorAll( this.selector + '[data-code]:not([data-handled])' ).forEach( ( e, i ) => {
          this.generateQRCode( e );e.dataset.handled = true;
        } );
      }, 2000 );
    }
    generateQRCode( e ) {
      QRCode.toCanvas( e, e.dataset.code, { toSJISFunc: QRCode.toSJIS }, function (error) {
        if( error ) {
          toast.show({title: error, position: 'bottomright', type: 'warn'});
        } else {
          // toast.show({title: 'Successed :)', position: 'bottomright', type: 'info'});
        }
      })
      // QRCode.toDataURL('I am a pony!').then(url => {console.log(url);}).catch(err => {console.error(err);})
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
	new FWPProject_QRCode();
} )( jQuery );