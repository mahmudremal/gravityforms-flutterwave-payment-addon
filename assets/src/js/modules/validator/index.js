/**
 * Tagify Js: https://github.com/yairEO/tagify
 * https://preview.keenthemes.com/start/documentation/forms/tagify.html
 * 
 * @package Future WordPress Inc.
 */

 import validator from 'validator';
//  import isEmail from 'validator/lib/isEmail';
//  import isEmail from 'validator/es/lib/isEmail';
 
 ( function () {
     class FWPProject_Validator {
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
                        thisClass.executeValidator( e );
                     } );
                 } );
             }, 2000 );
         }
         executeValidator( e ) {
          // validator.isEmail( );
          // isEmail( );



          validator = {
            version,
            toDate,
            toFloat,
            toInt,
            toBoolean,
            equals,
            contains,
            matches,
            isEmail,
            isURL,
            isMACAddress,
            isIP,
            isIPRange,
            isFQDN,
            isBoolean,
            isIBAN,
            isBIC,
            isAlpha,
            isAlphaLocales,
            isAlphanumeric,
            isAlphanumericLocales,
            isNumeric,
            isPassportNumber,
            isPort,
            isLowercase,
            isUppercase,
            isAscii,
            isFullWidth,
            isHalfWidth,
            isVariableWidth,
            isMultibyte,
            isSemVer,
            isSurrogatePair,
            isInt,
            isIMEI,
            isFloat,
            isFloatLocales,
            isDecimal,
            isHexadecimal,
            isOctal,
            isDivisibleBy,
            isHexColor,
            isRgbColor,
            isHSL,
            isISRC,
            isMD5,
            isHash,
            isJWT,
            isJSON,
            isEmpty,
            isLength,
            isLocale,
            isByteLength,
            isUUID,
            isMongoId,
            isAfter,
            isBefore,
            isIn,
            isLuhnNumber,
            isCreditCard,
            isIdentityCard,
            isEAN,
            isISIN,
            isISBN,
            isISSN,
            isMobilePhone,
            isMobilePhoneLocales,
            isPostalCode,
            isPostalCodeLocales,
            isEthereumAddress,
            isCurrency,
            isBtcAddress,
            isISO6391,
            isISO8601,
            isRFC3339,
            isISO31661Alpha2,
            isISO31661Alpha3,
            isISO4217,
            isBase32,
            isBase58,
            isBase64,
            isDataURI,
            isMagnetURI,
            isMimeType,
            isLatLong,
            ltrim,
            rtrim,
            trim,
            escape,
            unescape,
            stripLow,
            whitelist,
            blacklist,
            isWhitelisted,
            normalizeEmail,
            toString,
            isSlug,
            isStrongPassword,
            isTaxID,
            isDate,
            isTime,
            isLicensePlate,
            isVAT,
            ibanLocales,
          };
         }
     }
     new FWPProject_Validator();
 } )();