/**
 * Flatpicket Js: https://flatpickr.js.org/getting-started/
 * https://preview.keenthemes.com/start/documentation/forms/flatpickr.html
 * 
 * @package Future WordPress Inc.
 */

import PasswordMeter from "password-meter";

( function () {
    class FWPProject_PasswordMeter {
        constructor() {
            this.selector = '.fwp-passwordmeted-field';
            this.setup_hooks();
        }
        setup_hooks() {
            const thisClass = this;var theInterval, players, css, js, csses, jses;
            theInterval = setInterval( () => {
                document.querySelectorAll( this.selector + ':not([data-handled])' ).forEach( ( e, i ) => {
                    this.executeMeter( e );e.dataset.handled = true;
                } );
            }, 2000 );
        }
        executeMeter( e ) {
            var args = {enableTime: false,dateFormat: "F-Y"};// noCalendar: true,
            if( e.dataset.config ) {
                args = JSON.parse( e.dataset.config );
            }
            console.log( JSON.stringify( new PasswordMeter().getResult( e.value ) ) );
        }
    }
    new FWPProject_PasswordMeter();
} )();