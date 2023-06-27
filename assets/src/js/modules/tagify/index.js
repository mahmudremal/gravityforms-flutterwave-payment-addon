/**
 * Tagify Js: https://github.com/yairEO/tagify
 * https://preview.keenthemes.com/start/documentation/forms/tagify.html
 * 
 * @package Future WordPress Inc.
 */

import Tagify from '@yaireo/tagify'
 
 ( function () {
     class FWPProject_Tagify {
         constructor() {
             this.selector = '.fwp-sweetalert-field';
             this.setup_hooks();
         }
         setup_hooks() {
             const thisClass = this;var theInterval, players, css, js, csses, jses;
             theInterval = setInterval( () => {
                 document.querySelectorAll( this.selector + ':not([data-handled])' ).forEach( ( e, i ) => {
                     e.dataset.handled = true;
                     e.addEventListener( 'click', ( event ) => {
                        thisClass.executeTagify( e );
                     } );
                 } );
             }, 2000 );
         }
         executeTagify( e ) {
            tagify = new Tagify( e );
            e.addEventListener('change', ( event ) => {
                console.log( event.target.value );
            } );
         }
     }
     new FWPProject_Tagify();
 } )();