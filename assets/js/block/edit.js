import { __, sprintf } from '@wordpress/i18n';

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wp-last-viewed' ) }>
					<TextControl
						label={ __( 'Number of posts', 'wp-last-viewed' ) }
						type="number"
						help={
							attributes.amount < 1 || attributes.amount > 20
								? __(
										'Must be between 1 and 20.',
										'wp-last-viewed'
								  )
								: ''
						}
						value={ attributes.amount }
						onChange={ ( value ) => {
							const amount = parseInt( value ) || 1;
							if ( amount < 1 || amount > 20 ) {
								return;
							}
							setAttributes( { amount } );
						} }
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				{ sprintf(
					/* translators: %d is the number of last viewed posts */
					__(
						'Your last viewed %d posts will be displayed here.',
						'wp-last-viewed'
					),
					attributes.amount
				) }
			</p>
		</>
	);
}
