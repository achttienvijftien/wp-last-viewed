import { __ } from '@wordpress/i18n';

import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	return (
		<p { ...useBlockProps() }>
			{ __( 'Your last viewed posts will be displayed here.', 'wp-last-viewed' ) }
		</p>
	);
}
