document.addEventListener( 'DOMContentLoaded', function () {

	var postId = wlv_tracker_data.post_id;
	var amount = wlv_tracker_data.amount
	var cookieName = wlv_tracker_data.tracking_cookie;
	var cookieAge = wlv_tracker_data.cookie_age;

	cookies = document.cookie.split( '; ' );

	var cookieMap = [];
	for ( const cookie of cookies ) {
		const parts = cookie.split( '=' );
		cookieMap[parts[0]] = decodeURIComponent( parts[1] );
	}

	var postsCookie = cookieMap[cookieName];
	var posts = postsCookie.split( ',' );

	// Find existing post_id
	const index = posts.indexOf( postId );

	if ( index !== - 1 ) {
		// Remove it so it can be moved to the front
		posts.splice( index, 1 );
	}

	// Add to start
	posts.unshift( postId );

	// Limit amount
	posts = posts.slice( 0, amount );

	var newCookie = encodeURIComponent( posts.join( ',' ) );

	document.cookie = cookieName + '=' + newCookie + '; path=/; max-age=' + cookieAge;
} );