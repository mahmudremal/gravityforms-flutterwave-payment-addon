/**
 * CropperJS Js: https://www.npmjs.com/package/cropperjs
 * JavaScript image cropper
 *
 * @package Future WordPress Inc.
 */

// import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';

(function () {
  class FWPProject_CropperJS {
    constructor() {
      this.selector = ".fwp-cropperjs-field";
      this.setup_hooks();
    }
    setup_hooks() {
      const thisClass = this;
      var theInterval, players, css, js, csses, jses;
      theInterval = setInterval(() => {
        document.querySelectorAll(this.selector + ":not([data-handled])").forEach((e, i) => {
            this.executePicker(e);
            e.dataset.handled = true;
          });
      }, 2000);
    }
    executePicker(e) {
        const image = document.getElementById('image');
        const cropper = new Cropper(image, {
        aspectRatio: 16 / 9,
        crop(event) {
            console.log(event.detail);
            // console.log(event.detail.x);
            // console.log(event.detail.y);
            // console.log(event.detail.width);
            // console.log(event.detail.height);
            // console.log(event.detail.rotate);
            // console.log(event.detail.scaleX);
            // console.log(event.detail.scaleY);
        },
        });
    }
  }
  new FWPProject_CropperJS();
})();
