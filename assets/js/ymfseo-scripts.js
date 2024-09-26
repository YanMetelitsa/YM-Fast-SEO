'use strict';

class YMFSEO {
	/**
	 * Sets field box range width and color.
	 * 
	 * @param {Element} fieldBox Field box element.
	 */
	static checkFieldRange ( fieldBox ) {
		/** Get check values */
		const min = parseInt( fieldBox.getAttribute( 'data-min' ) );
		const rec = fieldBox.getAttribute( 'data-rec' ).split( '-' ).map( value => {
			return parseInt( value );
		});
		const max = parseInt( fieldBox.getAttribute( 'data-max' ) );

		/** Get range element */
		const range = fieldBox.querySelector( '.ymfseo-box__field-box-range' );
		
		/** Check */
		fieldBox.querySelectorAll( 'input, textarea' ).forEach( input => {
			const inputLength = input.value.trim().length;

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
}

window.addEventListener( 'load', e => {
	document.querySelectorAll( '.ymfseo-box__field-box' ).forEach( fieldBox => {
		YMFSEO.checkFieldRange( fieldBox );

		fieldBox.querySelectorAll( 'input, textarea' ).forEach( input => {
			input.addEventListener( 'input', e => {
				YMFSEO.checkFieldRange( fieldBox );
			});
		});
	});
});