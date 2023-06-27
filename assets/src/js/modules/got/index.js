/**
 * GOT HTTP Request Js: https://www.npmjs.com/package/got
 * https://github.com/sindresorhus/got
 * https://github.com/sindresorhus/got/blob/HEAD/documentation
 * 
 * @package Future WordPress Inc.
 */

 import got from 'got';
//  import got, {Options} from 'got';
 
 
 ( function () {
     class FWPProject_GotRequest {
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
                        // thisClass.executeTagify( e );
                     } );
                 } );
             }, 2000 );
         }
         examples() {
          const {data} = await got.post('https://httpbin.org/anything', {
            json: {
              hello: 'world'
            }
          }).json();
          console.log(data);
          const options = new Options({
            prefixUrl: 'https://httpbin.org',
            headers: {
              foo: 'foo'
            }
          });
          
          options.headers.foo = 'bar';
          
          // Note that `Options` stores normalized options, therefore it needs to be passed as the third argument.
          const {headers} = await got('anything', undefined, options).json();
          console.log(headers.Foo);
          const {headers} = await got(
            'https://httpbin.org/anything',
            {
              headers: {
                foo: 'bar'
              }
            }
          ).json();          
         }
     }
     new FWPProject_GotRequest();
 } )();