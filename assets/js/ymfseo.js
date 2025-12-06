'use strict';

/**
 * Main YM Fast SEO class.
 */
class YMFSEO {
	/**
	 * Inits meta box checkboxes.
	 */
	static initMetaBoxCheckboxes () {
		document.querySelectorAll( '.ymfseo-post-meta-box__checkbox' ).forEach( box => {
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

	/**
	 * Inits SEO inputs length indicators.
	 */
	static initInputLengthIndicators () {
		const indicators = document.querySelectorAll( '.ymfseo-length-indicator' );

		indicators.forEach( indicator => {
			if ( indicator.classList.contains( 'initialized' ) ) {
				return;
			}

			const input = indicator.parentElement.querySelector( `*[ name=${indicator.dataset.for} ]` );

			if ( indicator.classList.contains( 'ymfseo-length-indicator_term' ) ) {
				indicator.style.width = `${input.offsetWidth}px`;
			}

			YMFSEO.checkInputLength( indicator, input );
			
			input.addEventListener( 'input', e => {
				YMFSEO.checkInputLength( indicator, input );
			});

			indicator.classList.add( 'initialized' );
		});
	}

	/**
	 * Sets field box range width and color.
	 * 
	 * @param {Element} indicator Indicator element.
	 * @param {Element} input     Input element.
	 */
	static checkInputLength ( indicator, input ) {
		/* Get check values */
		const min = parseInt( input.dataset.min );
		const rec = input.dataset.rec.split( '-' ).map( value => {
			return parseInt( value );
		});
		const max = parseInt( input.dataset.max );

		/* Get raw input data */
		const rawInputValue = input.value;
			
		/* Format input string */
		let inputValue = rawInputValue;

		for ( const [ tag, replace ] of Object.entries( YMFSEO_WP.replaceTags ) ) {
			inputValue = inputValue.replaceAll( tag, replace );
		}

		/* Get input length */
		let inputLength = inputValue.trim().length;

		if ( inputLength > 0 && ! YMFSEO_WP.isTitlePartsHidden && 'ymfseo-title' == input.name ) {
			let separatorLength = YMFSEO_WP.titleSeparator.length;
			let titlePartLength = YMFSEO_WP.siteName.length;

			if ( parseInt( input.dataset.postId ?? -1 ) === YMFSEO_WP.frontPageID ) {
				titlePartLength = YMFSEO_WP.siteDescription.length;;
			}

			inputLength += 1 + separatorLength + 1 + titlePartLength;
		}

		/* Set condition */
		indicator.style.setProperty( '--ymfseo-indicator-width', `${( inputLength / max ) * 100}%` );

		if ( inputLength > 0 ) {
			indicator.title = inputLength;
		} else {
			indicator.title = '';
		}

		if ( inputLength >= rec[ 0 ] && inputLength <= rec[ 1 ] ) {
			indicator.classList.add( 'good' );
		} else {
			indicator.classList.remove( 'good' );
		}

		if ( inputLength < min || inputLength > max ) {
			indicator.classList.add( 'bad' );
		} else {
			indicator.classList.remove( 'bad' );
		}
	}
}

/**
 * YM Fast SEO Cookies class.
 */
class YMFSEO_Cookies {
	/**
	 * Sets cookie value.
	 * 
	 * @param {string} name  Cookie name.
	 * @param {any}    value Cookie value.
	 * @param {int}    days  Days expires.
	 */
	static set ( name, value, days = 10 ) {
		let expires = '';

		if ( days ) {
			const date = new Date();

			date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );

			expires = `expires=${date.toUTCString()}`;
		}

		document.cookie = `ymfseo-${name}=${( value || '' )}; ${expires}; path=/`;
	}

	/**
	 * Retrieves cookie value.
	 * 
	 * @param {string} name Cookie name.
	 * 
	 * @returns Cookie value or null.
	 */
	static get ( name ) {
		const cookies = document.cookie.split( '; ' );

		for ( let cookie of cookies ) {
			const [ key, value ] = cookie.split( '=' );

			if ( `ymfseo-${name}` == key ) {
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
	static delete ( name ) {
		document.cookie = `ymfseo-${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
	}
}

/**
 * YM Fast SEO Settings class.
 */
class YMFSEO_Settings {
	/**
	 * Retrieves settings sections.
	 * 
	 * @returns {Element[]} Sections object.
	 */
	static get sections () {
		const settingsPage    = document.querySelector( '.ymfseo-settings-page' );
		const settingsPageNav = settingsPage.querySelector( '.nav-tab-wrapper' );

		let output = [];

		if ( settingsPage && settingsPageNav ) {
			const slugs    = Array.from( settingsPageNav.querySelectorAll( '[ data-target ]' ) ).map( item => item.dataset.target );
			const sections = settingsPage.querySelectorAll( '.ymfseo-settings-page section' );

			output = slugs.reduce( ( accumulator, slug, index ) => {
				accumulator[ slug ] = sections[ index ];

				return accumulator;
			}, {} );
		}

		return output;
	}

	/**
	 * Inits settings navigation bar.
	 */
	static initNav () {
		const navItems = document.querySelectorAll( '.ymfseo-settings-page .nav-tab' );

		navItems.forEach( item => {
			item.addEventListener( 'click', e => {
				const slug = item.dataset.target;

				navItems.forEach( item => item.classList.remove( 'nav-tab-active' ) );
				item.classList.add( 'nav-tab-active' );

				YMFSEO_Settings.openSection( slug );
			});
		});
	}

	/**
	 *  Inits settings sections.
	 */
	static initSections () {
		let slug = 'general';

		const hash     = window.location.hash;
		const sections = YMFSEO_Settings.sections;

		/* Check hash slug. */
		if ( hash && sections[ hash.replace( '#', '' ) ] ) {
			slug = hash.replace( '#', '' );
		}

		/* Check cookie slug. */
		const cookieSlug = YMFSEO_Cookies.get( 'last-settings-tab' );

		if ( cookieSlug && sections[ cookieSlug ] ) {
			slug = cookieSlug;

			YMFSEO_Cookies.delete( 'last-settings-tab' );
		}

		/* Activate section. */
		YMFSEO_Settings.openSection( slug );

		/* Activate nav item. */
		const navItem = document.querySelector( `.ymfseo-settings-page .nav-tab[ data-target=${slug} ]` );

		if ( navItem ) {
			navItem.classList.add( 'nav-tab-active' );
		}
	}

	/**
	 * Activates settings section.
	 * 
	 * @param {string} slug Section slug.
	 */
	static openSection ( slug ) {
		const sections = YMFSEO_Settings.sections;
		
		if ( sections[ slug ] ) {
			window.location.hash = `#${slug}`;

			Object.entries( sections ).forEach( ( [ name, element ] ) => {
				element.classList.remove( 'active' );
			});

			sections[ slug ].classList.add( 'active' );
		}
	}


	/**
	 * Adds new redirection row.
	 * 
	 * @param {Element} button `Add` button Element.
	 */
	static addRedirectRow ( button ) {
		/*const parent   = button.closest( '.ymfseo-settings-redirects-section' );
		const grid     = parent.querySelector( '.ymfseo-settings-redirects-section__grid' );
		const items    = parent.querySelectorAll( '.ymfseo-settings-redirects-section__item' );
		const lastItem = items[ items.length - 1 ];

		const counter = parseInt( lastItem.dataset.counter ) + 1;*/
	}

	/**
	 * Removes redirection row.
	 * 
	 * @param {Element} button `Remove` button Element.
	 */
	static removeRedirectRow ( button ) {
		/* */
	}


	/**
	 * Inits settings additional `Save` buttons.
	 */
	static initSaveButtons () {
		const saveButtons = document.querySelectorAll( '.ymfseo-submit .button' );

		saveButtons.forEach( btn => {
			btn.addEventListener( 'click', e => {
				YMFSEO_Cookies.set( 'last-settings-tab', window.location.hash.replace( '#', '' ) );
				btn.focus();
			});
		});
	}
}

window.addEventListener( 'load', e => {
	YMFSEO.initInputLengthIndicators();
	YMFSEO.initMetaBoxCheckboxes();
});