'use strict';

window.addEventListener( 'load', e => {
	const checkBoxes = document.querySelectorAll( '.ymfseo-box__check-box input' );

	checkBoxes.forEach( checkbox => {
		checkbox.addEventListener( 'input', e => {
			const span = checkbox.closest( 'span' );

			if ( checkbox.checked ) {
				span.classList.add( 'is-checked' );
			} else {
				span.classList.remove( 'is-checked' );
			}
		});
	});
});