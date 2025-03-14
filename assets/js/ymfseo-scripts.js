'use strict';

/**
 * Main YMFSEO JS class.
 */
class YMFSEO {
	/**
	 * Sets cookie.
	 * 
	 * @param {string} name Cookie name.
	 * @param {any} value Cookie value.
	 * @param {int} days Days expires.
	 */
	static setCookie ( name, value, days = 10 ) {
		let expires = '';

		if ( days ) {
			const date = new Date();
			date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
			expires = `expires=${date.toUTCString()}`;
		}

		document.cookie = `ymfseo-${name}=${( value || "" )}; ${expires}; path=/`;
	}

	/**
	 * Retrives cookie value.
	 * 
	 * @param {string} name Cookie name.
	 * 
	 * @returns Cookie value or null.
	 */
	static getCookie ( name ) {
		const cookies = document.cookie.split( '; ' );

		for ( let cookie of cookies ) {
			const [ key, value ] = cookie.split( '=' );

			if ( key === `ymfseo-${name}` ) {
				return value;
			}
		}

		return null;
	}

	/**
	 * Deletes cookie.
	 * 
	 * @param {string} name Cookie name.
	 */
	static deleteCookie ( name ) {
		document.cookie = `ymfseo-${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
	}
	
	/**
	 * Inits inputs SEO length checkers.
	 */
	static initLengthCheckers () {
		const checkers = document.querySelectorAll( '.ymfseo-length-checker' );

		checkers.forEach( checker => {
			const input = document.querySelector( `*[ name=${checker.getAttribute( 'data-for' )} ]` );

			if ( checker.classList.contains( 'ymfseo-length-checker_term' ) ) {
				checker.style.width = `${input.offsetWidth}px`;
			}

			YMFSEO.checkInputLength( checker, input );
			
			input.addEventListener( 'input', e => {
				YMFSEO.checkInputLength( checker, input );
			});
		});
	}

	/**
	 * Sets field box range width and color.
	 * 
	 * @param {Element} checker Checker element.
	 * @param {Element} input   Input element.
	 */
	static checkInputLength ( checker, input ) {
		/* Get check values */
		const min = parseInt( input.getAttribute( 'data-min' ) );
		const rec = input.getAttribute( 'data-rec' ).split( '-' ).map( value => {
			return parseInt( value );
		});
		const max = parseInt( input.getAttribute( 'data-max' ) );

		/* Get raw input data */
		const rawInputvalue = input.value;
			
		/* Format input string */
		let inputvalue = rawInputvalue;

		for ( const [ tag, replace ] of Object.entries( YMFSEO_WP.replaceTags ) ) {
			inputvalue = inputvalue.replaceAll( tag, replace );
		}

		/* Get input length */
		const inputLength = inputvalue.trim().length;

		/* Set condition */
		checker.style.setProperty( '--ymfseo-checker-width', `${( inputLength / max ) * 100}%` );

		if ( inputLength >= rec[ 0 ] && inputLength <= rec[ 1 ] ) {
			checker.classList.add( 'good' );
		} else {
			checker.classList.remove( 'good' );
		}

		if ( inputLength < min || inputLength > max ) {
			checker.classList.add( 'bad' );
		} else {
			checker.classList.remove( 'bad' );
		}
	}

	/**
	 * Inits meta box checkboxes.
	 */
	static initMetaBoxCheckboxes () {
		document.querySelectorAll( '.ymfseo-box__checkbox' ).forEach( box => {
			const span  = box.querySelector( '.components-form-toggle' );
			const input = box.querySelector( 'input' );

			input.addEventListener( 'change', e => {
				if ( input.checked ) {
					span.classList.add( 'is-checked' );
				} else {
					span.classList.remove( 'is-checked' );
				}
			});
		});
	}
}

/**
 * YMFSEO Settings JS class.
 */
class YMFSEO_Settings {
	/**
	 * Retrives settings sections.
	 * 
	 * @returns Sections object.
	 */
	static #getSections () {
		const page = document.querySelector( '.ymfseo-seettings-page' );
		const nav  = document.querySelector( '.ymfseo-seettings-page__nav' );

		let output = [];

		if ( page && nav ) {
			const slugs    = Array.from( nav.querySelectorAll( '[ data-target ]' ) ).map( item => item.getAttribute( 'data-target' ) );
			const sections = page.querySelectorAll( '.ymfseo-seettings-page section' );

			output = slugs.reduce( ( acc, slug, index ) => {
				acc[ slug ] = sections[ index ];
				return acc;
			}, {} );
		}

		return output;
	}

	/**
	 * Activates settings section.
	 * 
	 * @param {string} slug Section slug.
	 */
	static activateSection ( slug ) {
		const sections = YMFSEO_Settings.#getSections();
		
		if ( sections[ slug ] ) {
			window.location.hash = `#${slug}`;

			Object.entries( sections ).forEach( ( [ name, element ] ) => {
				element.classList.remove( 'active' );
			});

			sections[ slug ].classList.add( 'active' );
		}
	}

	/**
	 *  Inits settings sections.
	 */
	static initSettingsSections () {
		let slug = 'general';

		const hash     = window.location.hash;
		const sections = YMFSEO_Settings.#getSections();

		/* Check hash slug. */
		if ( hash && sections[ hash.replace( '#', '' ) ] ) {
			slug = hash.replace( '#', '' );
		}

		/* Check cookie slug. */
		const cookieSlug = YMFSEO.getCookie( 'last-settings-tab' );

		if ( cookieSlug && sections[ cookieSlug ] ) {
			slug = cookieSlug;

			YMFSEO.deleteCookie( 'last-settings-tab' );
		}

		/* Activate section. */
		YMFSEO_Settings.activateSection( slug );

		/* Activate nav item. */
		const navItem = document.querySelector( `.ymfseo-seettings-page__nav-item[ data-target=${slug} ]` );

		if ( navItem ) {
			navItem.classList.add( 'active' );
		}
	}

	/**
	 * Inits settings navigation bar.
	 */
	static initSettingsNav () {
		const navItems = document.querySelectorAll( '.ymfseo-seettings-page__nav-item' );

		navItems.forEach( item => {
			item.addEventListener( 'click', e => {
				const slug = item.getAttribute( 'data-target' );

				navItems.forEach( item => item.classList.remove( 'active' ) );
				item.classList.add( 'active' );

				YMFSEO_Settings.activateSection( slug );
			});
		});
	}

	// static addRedirectRow ( button ) {
	// 	const parent   = button.closest( '.ymfseo-settings-redirects-section' );
	// 	const grid     = parent.querySelector( '.ymfseo-settings-redirects-section__grid' );
	// 	const items    = parent.querySelectorAll( '.ymfseo-settings-redirects-section__item' );
	// 	const lastItem = items[ items.length - 1 ];

	// 	const counter = parseInt( lastItem.getAttribute( 'data-counter' ) ) + 1;

	// 	console.log( lastItem, counter );
	// }

	// static removeRedirectRow ( button ) {
	// 	alert( 2 );
	// }

	/**
	 * Inits settings additional `Save` buttons.
	 */
	static initSettingsSaveButtons () {
		const saveButtons = document.querySelectorAll( '.ymfseo-submit .button' );

		saveButtons.forEach( btn => {
			btn.addEventListener( 'click', e => {
				YMFSEO.setCookie( 'last-settings-tab', window.location.hash.replace( '#', '' ) );
				btn.focus();
			});
		});
	}
}

window.addEventListener( 'load', e => {
	YMFSEO.initLengthCheckers();
	YMFSEO.initMetaBoxCheckboxes();
});