'use strict';

class YMFSEO {
	/**
	 * Inits inputs SEO length checkers.
	 */
	static initLengthCheckers () {
		const checkers = document.querySelectorAll( '.ymfseo-length-checker' );

		checkers.forEach( checker => {
			const input = document.querySelector( `*[ name=${checker.getAttribute( 'data-for' )} ]` );

			checker.style.width = `${input.offsetWidth}px`;

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

window.addEventListener( 'load', e => {
	YMFSEO.initLengthCheckers();
	YMFSEO.initMetaBoxCheckboxes();
});