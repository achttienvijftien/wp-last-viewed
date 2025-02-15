const path = require( 'path' );
const WPLastViewed = require( path.resolve(
	__dirname,
	'../../assets/js/wp-last-viewed'
) );

describe( 'WordPress Last Viewed tests', () => {
	let tracker;

	beforeEach( () => {
		// Initialize a new instance of the tracker before each test
		tracker = new WPLastViewed( '.test-last-viewed-list', {
			cookieName: 'test-last-viewed',
			defaultAmount: 5,
			postTypes: [ 'post', 'page' ],
		} );

		// Clear any existing cookies before tests
		document.cookie =
			'test-last-viewed=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
	} );

	test( 'should initialize with default values', () => {
		expect( tracker.cookieName ).toBe( 'test-last-viewed' );
		expect( tracker.defaultAmount ).toBe( 5 );
		expect( tracker.listSelector ).toBe( '.test-last-viewed-list' );
	} );

	test( 'should get post ID from body class', () => {
		document.body.className = 'postid-123 post-template-default';
		const postId = tracker.getPostIdFromBody();
		expect( postId ).toBe( 123 );
	} );

	test( 'should get post type from body class', () => {
		document.body.className = 'postid-123 attachment-template-default';
		const postType = tracker.getPostTypeFromBody();
		expect( postType ).toBe( 'attachment' );
	} );

	test( 'should get last viewed posts from cookie', () => {
		document.cookie =
			'test-last-viewed=1/post,2/post,3/page; path=/; SameSite=Lax';
		const lastViewedPosts = tracker.getLastViewedPosts();
		expect( lastViewedPosts ).toEqual( [
			{ id: 1, type: 'post' },
			{ id: 2, type: 'post' },
			{ id: 3, type: 'page' },
		] );
	} );

	test( 'should append post ID correctly', () => {
		tracker.appendPostToCookie( 123, 'post' );
		tracker.appendPostToCookie( 234, 'page' );
		const cookie = decodeURIComponent( document.cookie );
		expect( cookie ).toBe( 'test-last-viewed=234/page,123/post' );
	} );

	test( 'should move existing post ID to the front', () => {
		document.cookie =
			'test-last-viewed=123/post,456/post,789/page; path=/; SameSite=Lax';
		tracker.appendPostToCookie( 456, 'post' );
		const cookie = decodeURIComponent( document.cookie );
		expect( cookie ).toBe( 'test-last-viewed=456/post,123/post,789/page' );
	} );

	test( 'should correctly get and set cookies', () => {
		tracker.setCookie( [
			{ id: 1, type: 'post' },
			{ id: 2, type: 'page' },
			{ id: 3, type: 'page' },
		] );
		const cookie = decodeURIComponent( document.cookie );
		expect( cookie ).toBe( 'test-last-viewed=1/post,2/page,3/page' );
	} );

	test( 'should escape HTML strings', () => {
		const unsafeString = '<script>alert("XSS")</script>';
		const safeString = tracker.escapeHTML( unsafeString );
		expect( safeString ).toBe(
			'&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;'
		);
	} );

	test( 'should render posts into the DOM', () => {
		// Create a test container in the document
		document.body.innerHTML =
			'<ul class="test-last-viewed-list" data-amount="2"></ul>';

		const posts = [
			{
				id: '1',
				link: 'https://example.com/post1',
				title: { rendered: 'Post 1' },
			},
			{
				id: '2',
				link: 'https://example.com/post2',
				title: { rendered: 'Post 2' },
			},
			{
				id: '3',
				link: 'https://example.com/post3',
				title: { rendered: 'Post 3' },
			},
		];

		tracker.renderPosts( posts );

		const listElement = document.querySelector( '.test-last-viewed-list' );
		expect( listElement.children.length ).toBe( 2 );
		expect( listElement.children[ 0 ].innerHTML ).toContain( 'Post 1' );
		expect( listElement.children[ 1 ].innerHTML ).toContain( 'Post 2' );
	} );

	test( 'should render default amount of posts into the DOM', () => {
		// Create a test container in the document
		document.body.innerHTML = '<ul class="test-last-viewed-list"></ul>';

		const posts = [
			{
				id: '1',
				link: 'https://example.com/post1',
				title: { rendered: 'Post 1' },
			},
			{
				id: '2',
				link: 'https://example.com/post2',
				title: { rendered: 'Post 2' },
			},
			{
				id: '3',
				link: 'https://example.com/post3',
				title: { rendered: 'Post 3' },
			},
			{
				id: '4',
				link: 'https://example.com/post4',
				title: { rendered: 'Post 4' },
			},
			{
				id: '5',
				link: 'https://example.com/post5',
				title: { rendered: 'Post 5' },
			},
			{
				id: '6',
				link: 'https://example.com/post6',
				title: { rendered: 'Post 6' },
			},
		];

		tracker.renderPosts( posts );

		const listElement = document.querySelector( '.test-last-viewed-list' );
		expect( listElement.children.length ).toBe( 5 );
		expect( listElement.children[ 0 ].innerHTML ).toContain( 'Post 1' );
		expect( listElement.children[ 1 ].innerHTML ).toContain( 'Post 2' );
	} );

	test( 'should fetch and return posts in correct order', async () => {
		tracker.postTypes = [ 'post' ];

		// Mock the global fetch function
		global.fetch = jest.fn( () =>
			Promise.resolve( {
				json: () =>
					Promise.resolve( [
						{
							id: 2,
							link: 'https://example.com/post2',
							title: { rendered: 'Post 2' },
						},
						{
							id: 1,
							link: 'https://example.com/post1',
							title: { rendered: 'Post 1' },
						},
					] ),
			} )
		);

		const postIds = [
			{ id: 1, type: 'post' },
			{ id: 2, type: 'post' },
		];

		// test fetchPosts method
		tracker.fetchPosts( postIds, ( posts ) => {
			// test if posts are sorted correctly
			expect( posts[ 0 ].id ).toBe( 1 );
			expect( posts[ 1 ].id ).toBe( 2 );
		} );

		// restore fetch  method
		global.fetch.mockRestore();
	} );
} );
