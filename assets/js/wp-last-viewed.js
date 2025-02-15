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
		this.postTypes = config.postTypes || [ 'post' ];
		this.cookieExpirationSeconds =
			parseInt( config.cookieExpirationSeconds ) || 2592000; // One month;

		// Initialize tracking when the DOM is fully loaded
		document.addEventListener( 'DOMContentLoaded', () => {
			this.initPostTracking();
			this.renderLastViewedPosts();
		} );
	}

	/**
	 * Initialize the post tracking functionality: adds the current post to the cookie.
	 */
	initPostTracking() {
		const postId = this.getPostIdFromBody();
		const postType = this.getPostTypeFromBody();
		if ( ! postId || ! postType ) {
			return;
		}
		if ( this.postTypes.indexOf( postType ) === -1 ) {
			return;
		}

		this.appendPostToCookie( postId, postType );
	}

	/**
	 * Render the last viewed posts on the page. Works with multiple lists.
	 */
	renderLastViewedPosts() {
		const lastViewedPosts = this.getLastViewedPosts();

		// If no posts are found, display an empty list
		if ( 0 === lastViewedPosts.length ) {
			this.renderPosts( [] );
		} else {
			// Fetch and display the posts asynchronously
			this.fetchPosts( lastViewedPosts, ( posts ) => {
				this.renderPosts( posts );
			} );
		}
	}

	/**
	 * Retrieve the post ID from the body class.
	 *
	 * @return {number | undefined} id of the post currently displayed.
	 */
	getPostIdFromBody() {
		return parseInt(
			document.body.className.match( /(postid|page-id)-(\d+)/ )?.[ 2 ]
		);
	}

	/**
	 * Retrieve the post type from the body class.
	 *
	 * @return {string | undefined} type of the post currently displayed.
	 */
	getPostTypeFromBody() {
		return document.body.className.match(
			/([a-zA-Z0-9_-]+)-template-.*/
		)?.[ 1 ];
	}

	/**
	 * Retrieve the last viewed posts stored in the cookie.
	 *
	 * @return {{id: number, type: string}[]} list of tracked posts with id and type.
	 */
	getLastViewedPosts() {
		const lastViewed = this.getCookie();
		return lastViewed
			? lastViewed.split( ',' ).map( ( id ) => {
					const idParts = id.split( '/' );
					return { id: parseInt( idParts[ 0 ] ), type: idParts[ 1 ] };
			  } )
			: [];
	}

	/**
	 * Append (or rearrange) a post to the cookie.
	 *
	 * @param {number} postId   id of the post.
	 * @param {string} postType type of the post.
	 */
	appendPostToCookie( postId, postType ) {
		const postIds = this.getLastViewedPosts();

		// Remove the ID if it already exists to move it to the front
		const index = postIds.findIndex( ( post ) => post.id === postId );
		if ( index !== -1 ) {
			postIds.splice( index, 1 );
		}

		// Add the ID at the start of the list
		postIds.unshift( { id: postId, type: postType } );

		// Save the updated list to the cookie
		this.setCookie( postIds );
	}

	/**
	 * Callback used after the posts have been fetched.
	 *
	 * @callback fetchedPostsCallback
	 * @param {{id: number, link: string, title: string}[]} posts fetched and sorted data from API.
	 * @return {void}
	 */

	/**
	 * Fetch the posts from the WordPress REST API with a callback.
	 *
	 * @param {{id: number, type: string}[]} posts    list of post ids to fetch.
	 * @param {fetchedPostsCallback}         callback Callback that is called after fetching.
	 * @return {Promise<void>}
	 */
	async fetchPosts( posts, callback ) {
		const postIds = posts.map( ( post ) => post.id );

		// ignore current post.
		const currentId = this.getPostIdFromBody();
		if ( currentId && postIds.indexOf( currentId ) !== -1 ) {
			postIds.splice( postIds.indexOf( currentId ), 1 );
		}

		const fetchPromises = this.postTypes.map( ( postType ) => {
			return this.fetchPostsFromType( postType, postIds, posts.length );
		} );

		// wait for all post types to be fetched.
		const results = await Promise.all( fetchPromises );

		// flatten all results to a single array.
		const fullPostData = results.flat();

		// Sort posts based on order in cookie
		fullPostData.sort( ( a, b ) => {
			const indexA = postIds.indexOf( a.id );
			const indexB = postIds.indexOf( b.id );
			return indexA - indexB;
		} );

		callback( fullPostData );
	}

	/**
	 * Fetch posts from a specific post type, for given post ids and max amount.
	 *
	 * @param {string}                       postType post type to fetch
	 * @param {{id: number, type: string}[]} postIds  List of tracked post ids.
	 * @param {number}                       amount   Max amount of posts to fetch.
	 * @return {Promise<any|*[]>} promise resolved when received response is decoded.
	 */
	async fetchPostsFromType( postType, postIds, amount ) {
		// map the post type to the corresponding api route.
		const routeMap = {
			post: 'posts',
			page: 'pages',
			attachment: 'media',
		};

		// for custom post types, the type is used as-is.
		const postTypeRoute = routeMap[ postType ] || postType;

		// Construct the API URL with the necessary query parameters
		const apiUrl =
			`/?rest_route=/wp/v2/${ postTypeRoute }&` +
			`_fields=title,link,id` +
			`&include=${ postIds.join( ',' ) }` +
			`&per_page=${ amount }`;

		try {
			// fetch the posts.
			const response = await fetch( apiUrl, {
				method: 'GET',
				headers: { Accept: 'application/json' },
			} );
			// return json-decoded posts.
			return await response.json();
		} catch ( error ) {
			return [];
		}
	}

	/**
	 * Render the posts in the elements defined by listSelector.
	 *
	 * @param {{id: number, link: string, title: string}[]} posts posts fetched from API to render.
	 */
	renderPosts( posts ) {
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

	/**
	 * Retrieve the cookie and decode to string.
	 *
	 * @return {string|null} URI decoded cookie as string.
	 */
	getCookie() {
		const match = document.cookie.match(
			new RegExp( `(^| )${ this.cookieName }=([^;]+)` )
		);
		return match ? decodeURIComponent( match[ 2 ] ) : null;
	}

	/**
	 * Set the cookie with the specified value
	 *
	 * @param {{id: number, type: string}[]} values list of posts with id and type.
	 */
	setCookie( values ) {
		const value = values
			.map( ( post ) => {
				return post.id + '/' + post.type;
			} )
			.join( ',' );
		const d = new Date();
		d.setTime( d.getTime() + this.cookieExpirationSeconds * 1000 );
		const cookieText =
			`${ this.cookieName }=${ encodeURIComponent( value ) }; ` +
			`expires=${ d.toUTCString() }; ` +
			`path=${ encodeURIComponent( this.cookiePath ) }; SameSite=Lax`;

		document.cookie = cookieText;
	}

	/**
	 * Escape HTML in strings to prevent XSS attacks
	 *
	 * @param {string} str String to escape
	 * @return {string} escaped string.
	 */
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
