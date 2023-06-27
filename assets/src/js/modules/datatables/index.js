/**
 * DataTable Js: https://datatables.net/forums/discussion/62415/how-to-import-datatables-in-es6-webpack
 * 
 *
 * @package Future WordPress Inc.
 */
import View from "./View.js";
// import $ from 'jquery';
// import 'datatables.net';
// import 'datatables.net-select';

(function () {
  class FWPProject_DataTable {
    constructor() {
      this.selector = ".fwp-datatable-field";
      this.setup_hooks();
    }
    setup_hooks() {
      const thisClass = this;
      var theInterval, players, css, js, csses, jses;
      theInterval = setInterval(() => {
        document
          .querySelectorAll(this.selector + ":not([data-handled])")
          .forEach((e, i) => {
            this.executePicker(e);
            e.dataset.handled = true;
          });
      }, 3000);
    }
    executePicker(e) {
      let view = new View();
      view.execTable( e );
    }
  }
  new FWPProject_DataTable();
})();
