( function ( $ ) {
	class FWPListivo_Widgets {
		constructor() {
			this.init();
		}
		init() {
			const thisClass = this;var swiperInterval;
			swiperInterval = setInterval( () => {
				if( typeof Swiper !== 'undefined' ) {
					thisClass.initSwiper();// clearInterval( swiperInterval );
					// console.log( 'i got it' );
				}
			}, 1500 );
		}
		initSwiper() {
			const thisClass = this;var args, elems, elem, elemCls = '.listivo-swiper-slider .swiper-container';
			elems = document.querySelectorAll( elemCls + ':not(.is-handled)' );// if( elems.length <= 0 ) {return;}
			elems.forEach( function( elem, index ) {
				// const $ = jQuery;
				args = {
					// Optional parameters
					speed: 1000,
					spaceBetween: 10,
					// autoplay: true,
					autoplay: {
						// delay: 5000,
						pauseOnMouseEnter: true
					},
					freeMode: {
						enabled: true,
						sticky: true,
					},
					mousewheel: {
						invert: true,
						sensitivity: 250
					},
					// parallax: true,
					loop: true,
					grabCursor: true,
					// centeredSlides: true,
					breakpointsBase: 'container', // Base for breakpoints (beta). Can be window or container. If set to window (by default) then breakpoint keys mean window width. If set to container then breakpoint keys treated as swiper container width
					// direction: 'vertical', // Can be 'horizontal' or 'vertical' (for vertical slider).
					loop: true,
					// effect: 'flip', // Transition effect. Can be 'slide', 'fade', 'cube', 'coverflow', 'flip' or 'creative'
					// If we need pagination
					pagination: {
						el: '.swiper-pagination',
						clickable: true,
					},
					// Navigation arrows
					navigation: {
						nextEl: '.swiper-button-next',
						prevEl: '.swiper-button-prev',
					},
					// And if we need scrollbar
					scrollbar: {
						el: '.swiper-scrollbar',
					},
					on: {
						init: function () {
							let swiper = this;
							for (let i = 0; i < swiper.slides.length; i++) {
								$( swiper.slides[i] )
									.find('.img-container')
									.attr({
										'data-swiper-parallax': .7 * swiper.width,
										'data-swiper-paralalx-opacity': .2,
									});
			
								$( swiper.slides[i] )
									.find('.title')
									.attr('data-swiper-parallax', .8 * swiper.width);
								$( swiper.slides[i] )
									.find('.description')
									.attr('data-swiper-parallax', .9 * swiper.width);
							}
						},
						resize: function () {
							this.update();
						},
					},
					slidesPerView: 3,
					breakpoints: {
						320: {
							slidesPerView: 1,
							spaceBetween: 0
						},
						480: {
							slidesPerView: 2,
							spaceBetween: 10
						},
						750: {
							slidesPerView: 3,
							spaceBetween: 10
						},
						1200: {
							slidesPerView: 4,
							spaceBetween: 10
						},
					}
				};
				const swiper = new Swiper( elemCls, args );
				elem.classList.add( 'is-handled' );
				// $(window).on('resize', function () {
				// 	swiper.destroy();
				// 	swiper = new Swiper('.listivo-swiper-slider .swiper-container', args );
				// });
				// Now you can use all slider methods like
				// swiper.slideNext();
			} );
		}
	}
	new FWPListivo_Widgets();
} )( jQuery );
