'use strict';

class YMFSEO {
	/**
	 * Sets field box range width and color.
	 * 
	 * @param {Element} fieldBox Field box element.
	 */
	static checkFieldRange ( fieldBox ) {
		/** Get range element */
		const range = fieldBox.querySelector( '.ymfseo-box__field-box-range' );

		if ( ! range ) return;

		/** Get check values */
		const min = parseInt( fieldBox.getAttribute( 'data-min' ) );
		const rec = fieldBox.getAttribute( 'data-rec' ).split( '-' ).map( value => {
			return parseInt( value );
		});
		const max = parseInt( fieldBox.getAttribute( 'data-max' ) );

		/** Check */
		fieldBox.querySelectorAll( 'input, textarea' ).forEach( input => {
			/** Get raw data */
			const rawInputvalue = input.value;
			
			/** Format input string */
			let inputvalue = rawInputvalue;

			for ( const [ tag, replace ] of Object.entries( YMFSEO_WP.replaceTags ) ) {
				inputvalue = inputvalue.replaceAll( tag, replace );
			}

			/** Get input length */
			const inputLength = inputvalue.trim().length;

			/** Set condition */
			range.style.setProperty( '--ymfseo-range-width', `${( inputLength / max ) * 100}%` );

			if ( inputLength >= rec[ 0 ] && inputLength <= rec[ 1 ] ) {
				range.classList.add( 'good' );
			} else {
				range.classList.remove( 'good' );
			}

			if ( inputLength < min || inputLength > max ) {
				range.classList.add( 'bad' );
			} else {
				range.classList.remove( 'bad' );
			}
		});
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
	/** Inits input ranges */
	document.querySelectorAll( '.ymfseo-box__field-box' ).forEach( fieldBox => {
		YMFSEO.checkFieldRange( fieldBox );

		fieldBox.querySelectorAll( 'input, textarea' ).forEach( input => {
			input.addEventListener( 'input', e => {
				YMFSEO.checkFieldRange( fieldBox );
			});
		});
	});

	/** Inits meta box checkboxes */
	YMFSEO.initMetaBoxCheckboxes();
});