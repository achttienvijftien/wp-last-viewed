'use strict';

// @link https://stackoverflow.com/questions/14573223/set-cookie-and-get-cookie-with-javascript
function setCookie( name, value, days ) {
	let expires = '';
	if ( days ) {
		const date = new Date();
		date.setTime( date.getTime() + days * 24 * 60 * 60 * 1000 );
		expires = '; expires=' + date.toUTCString();
	}
	document.cookie = name + '=' + ( value || '' ) + expires + '; path=/';
}
function getCookie( name ) {
	const nameEQ = name + '=';
	const ca = document.cookie.split( ';' );
	for ( let i = 0; i < ca.length; i++ ) {
		let c = ca[ i ];
		while ( c.charAt( 0 ) === ' ' ) c = c.substring( 1, c.length );
		if ( c.indexOf( nameEQ ) === 0 )
			return c.substring( nameEQ.length, c.length );
	}
	return null;
}

let currentCookie = getCookie( '1815_last-viewed' );
const postId = document.querySelector( '[name=postId]' ).content;

if ( currentCookie ) {
	if ( currentCookie.indexOf( postId ) === -1 ) {
		currentCookie = postId + ',' + currentCookie;

		setCookie( '1815_last-viewed', currentCookie, 30 );
	}
} else {
	setCookie( '1815_last-viewed', postId, 30 );
}
