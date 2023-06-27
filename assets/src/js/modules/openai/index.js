// import ApexCharts from 'apexcharts';
// import flatpickr from "flatpickr";
const { Configuration, OpenAIApi } = require("openai");

( function ( $ ) {
	class FWPProject_OpenAI {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl ?? '';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce ?? '';
			var i18n = fwpSiteConfig?.i18n ?? {};
			this.i18n = {
				are_u_sure					: 'Are you sure?',
				sure_to_delete			: 'Are you sure about this deletation.',
				...i18n
			}
			this.setup_hooks();
		}
		setup_hooks() {
      const configuration = new Configuration({
        apiKey: process.env.OPENAI_API_KEY,
      });
      const openai = new OpenAIApi(configuration);
      const response = await openai.createCompletion({
        model: "text-davinci-003",
        prompt: "Say this is a test",
        temperature: 0,
        max_tokens: 7,
      });
		}
	}

	new FWPProject_OpenAI();
} )( jQuery );
