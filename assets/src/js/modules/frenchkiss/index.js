/**
 * CropperJS Js: https://www.npmjs.com/package/frenchkiss
 * Javascript Translation Script
 * FrenchKiss.js is a blazing fast lightweight i18n library written in JavaScript, working both in the browser and NodeJS environments. It provides a simple and really fast solution for handling internationalization.
 * 
 * @package Future WordPress Inc.
 */

 import frenchkiss from 'frenchkiss';

(function () {
  class FWPProject_frenchkiss {
    constructor() {
      this.setup_hooks();
    }
    setup_hooks() {
      const thisClass = this;
      // Define the locale language
      frenchkiss.locale('en');
      // Add translations in each languages
      frenchkiss.set('en', {
        hello: 'Hello {name} !',
        fruits: {
          apple: 'apples'
        },
        // and other sentences...
      });
      frenchkiss.t('hello', {
        name: 'John',
      }); // => 'Hello John !'

      frenchkiss.t('fruits.apple'); // => 'apples'
    }
  }
  new FWPProject_frenchkiss();
})();
