'use strict';

// WordPress Last Viewed Tracker as a class.
class WPLastViewed {
	constructor( listSelector, config = {} ) {
		// Default configuration for cookie name, amount selector, and list selector
		this.listSelector = listSelector || '.wp-last-viewed-list';

		this.i18n = {};
		this.i18n.noPostsFound = config.i18n?.noPostsFound || 'No posts found.';
		this.cookieName = config.cookieName || '1815-last-viewed';
		this.cookiePath = config.cookiePath || '/';
		this.defaultAmount = parseInt( config.defaultAmount ) || 20;
		this.cookieExpirationSeconds =
			parseInt( config.cookieExpirationSeconds ) || 2592000; // One month;

		// Initialize tracking when the DOM is fully loaded
		document.addEventListener( 'DOMContentLoaded', () => {
			this.initPostTracking();
			this.renderLastViewedPosts();
		} );
	}

	// Initialize the post tracking functionality: adds the current post to the cookie
	initPostTracking() {
		const postId = this.getPostIdFromBody();
		if ( postId ) {
			this.appendPostToCookie( postId );
		}
	}

	// Render the last viewed posts on the page
	renderLastViewedPosts() {
		const lastViewedPostIds = this.getLastViewedPostIds();

		// If no posts are found, display an empty list
		if ( 0 === lastViewedPostIds.length ) {
			this.displayPosts( [] );
		} else {
			// Fetch and display the posts asynchronously
			this.fetchPosts( lastViewedPostIds, ( posts ) => {
				this.displayPosts( posts );
			} );
		}
	}

	// Retrieve the post ID from the body class
	getPostIdFromBody() {
		return parseInt(
			document.body.className.match( /postid-(\d+)/ )?.[ 1 ]
		);
	}

	// Retrieve the last viewed post IDs stored in the cookie
	getLastViewedPostIds() {
		const lastViewed = this.getCookie();
		return lastViewed
			? lastViewed.split( ',' ).map( ( id ) => parseInt( id ) )
			: [];
	}

	// Append (or rearrange) a post ID to the cookie and limit the list length
	appendPostToCookie( postId ) {
		const postIds = this.getLastViewedPostIds();

		// Remove the ID if it already exists to move it to the front
		const index = postIds.indexOf( postId );
		if ( index !== -1 ) {
			postIds.splice( index, 1 );
		}

		// Add the ID at the start of the list
		postIds.unshift( postId );

		// Save the updated list to the cookie
		this.setCookie( postIds );
	}

	// Fetch the posts from the WordPress REST API with a callback
	fetchPosts( postIds, callback ) {
		// Construct the API URL with the necessary query parameters
		const apiUrl =
			`/?rest_route=/wp/v2/posts&` +
			`_fields=title,link,id` +
			`&include=${ postIds.join( ',' ) }` +
			`&per_page=${ postIds.length }`;

		// Perform the fetch request
		fetch( apiUrl, {
			method: 'GET',
			headers: { Accept: 'application/json' },
		} )
			.then( ( response ) => response.json() )
			.then( ( posts ) => {
				// Sort the posts to match the order in the cookie
				posts.sort( ( a, b ) => {
					const indexA = postIds.indexOf( a.id );
					const indexB = postIds.indexOf( b.id );
					return indexA - indexB;
				} );
				callback( posts );
			} )
			.catch( () => {
				callback( [] );
			} );
	}

	// Display the posts in the elements defined by listSelector
	displayPosts( posts ) {
		// Find all elements that should show the last viewed posts
		const listElements = document.querySelectorAll( this.listSelector );
		if ( ! listElements.length ) return;

		if ( 0 === posts.length ) {
			listElements.forEach( ( element ) => {
				element.innerHTML = `<li class="wp-last-viewed-list__item placeholder">${ this.escapeHTML(
					this.i18n.noPostsFound
				) }</li>`;
			} );
			return;
		}

		// Generate the HTML for the posts
		const viewedPostsHtml = posts.map( ( post ) => {
			if ( ! post || ! post.link || ! post.title?.rendered ) {
				return '';
			}
			return (
				`<li class="wp-last-viewed-list__item">` +
				`<a href="${ encodeURI( post.link ) }">` +
				this.escapeHTML( post.title.rendered ) +
				`</a></li>`
			);
		} );

		// Insert the generated HTML into each list element
		listElements.forEach( ( element ) => {
			const listAmount =
				parseInt( element.getAttribute( 'data-amount' ) ) ||
				this.defaultAmount;

			element.innerHTML = viewedPostsHtml
				.slice() // clone list.
				.splice( 0, listAmount ) // only show x items.
				.join( '' );
		} );
	}

	// Retrieve the cookie by its name
	getCookie() {
		const match = document.cookie.match(
			new RegExp( `(^| )${ this.cookieName }=([^;]+)` )
		);
		return match ? decodeURIComponent( match[ 2 ] ) : null;
	}

	// Set the cookie with the specified value
	setCookie( values ) {
		const value = values.join( ',' );
		const d = new Date();
		d.setTime( d.getTime() + this.cookieExpirationSeconds * 1000 );
		const cookieText =
			`${ this.cookieName }=${ encodeURIComponent( value ) }; ` +
			`expires=${ d.toUTCString() }; ` +
			`path=${ encodeURIComponent( this.cookiePath ) }; SameSite=Lax`;

		document.cookie = cookieText;
	}

	// Escape HTML in strings to prevent XSS attacks
	escapeHTML( str ) {
		return str.replace( /[&<>"]+/g, function ( match ) {
			const escape = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#39;',
			};
			return escape[ match ];
		} );
	}
}

// Load config.
let config = {};
if ( typeof window !== 'undefined' && window.wpLastViewedConfig ) {
	config = window.wpLastViewedConfig;
}

// Initialize the tracker.
new WPLastViewed( '.wp-last-viewed-list', config );

if ( typeof module !== 'undefined' && module.exports ) {
	module.exports = WPLastViewed;
}
