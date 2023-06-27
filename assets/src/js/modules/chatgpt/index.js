/**
 * CropperJS Js: https://www.npmjs.com/package/cropperjs
 * JavaScript image cropper
 *
 * @package Future WordPress Inc.
 */

 import { ChatGPTAPI } from 'chatgpt';

(function () {
  class FWPProject_ChatGPT {
    constructor() {
      this.setup_hooks();
    }
    setup_hooks() {
      const thisClass = this;
      thisClass.example();
    }
    async example() {
      const api = new ChatGPTAPI({
        apiKey: process.env.OPENAI_API_KEY
      })
    
      const res = await api.sendMessage('Hello World!')
      console.log(res.text)
    }
  }
  new FWPProject_ChatGPT();
})();
