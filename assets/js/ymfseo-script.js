'use strict';

window.addEventListener( 'load', e => {
	const checkBoxes = document.querySelectorAll( '.ymfseo-box__check-box input' );

	checkBoxes.forEach( checkbox => {
		checkbox.addEventListener( 'input', e => {
			checkbox.closest( 'span' ).classList.toggle( 'is-checked' );
		});
	});
});