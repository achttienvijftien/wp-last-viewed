import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, __experimentalNumberControl as NumberControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
  const { amount } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'wp-last-viewed' ) }>
					<NumberControl
						label={ __( 'Amount of posts', 'wp-last-viewed' ) }
						value={ amount }
						onChange={ (value) => setAttributes({ amount: parseInt(value, 10) || 0 }) }
						min={ 1 }
						max={ 20 }
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				{ __( 'The last viewed posts will be displayed here.', 'wp-last-viewed' ) }
			</p>
		</>
	);
}
