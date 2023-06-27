/**
 * Flatpicket Js: https://flatpickr.js.org/getting-started/
 * https://preview.keenthemes.com/start/documentation/forms/flatpickr.html
 * 
 * @package Future WordPress Inc.
 */

import flatpickr from "flatpickr";
// import { toast } from 'toast-notification-alert';
( function () {
    class FWPProject_FlatPickr {
        constructor() {
            this.selector = '.fwp-flatpickr-field';
            this.setup_hooks();
        }
        setup_hooks() {
            const thisClass = this;var theInterval, players, css, js, csses, jses;
            theInterval = setInterval( () => {
                document.querySelectorAll( this.selector + ':not([data-handled])' ).forEach( ( e, i ) => {
                    this.executePicker( e );e.dataset.handled = true;
                } );
            }, 2000 );
        }
        executePicker( e ) {
            var args = {enableTime: false,dateFormat: "F-Y"};// noCalendar: true,
            if( e.dataset.config ) {
                args = JSON.parse( e.dataset.config );
            }
            flatpickr( e, args );
        }
    }
    new FWPProject_FlatPickr();
} )();