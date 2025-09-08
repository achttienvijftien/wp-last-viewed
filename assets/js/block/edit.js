import { __ } from '@wordpress/i18n';

import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const blockProps = useBlockProps();

	const { maximum } = attributes;

	const setMaximum = ( element ) => {
		let value = element.target.value;
		value = isNaN( value ) ? maximum : parseInt( value );
		setAttributes( { maximum: value } );
	};

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Your last viewed posts will be displayed here.',
					'wp-last-viewed'
				) }
			</p>
			{ isSelected && (
				<div>
					<label htmlFor="wp-last-viewed-maximum">
						Maximum number of posts:
					</label>
					<div>
						<input
							id="wp-last-viewed-maximum"
							type="number"
							value={ maximum }
							onChange={ setMaximum }
						/>
					</div>
				</div>
			) }
		</div>
	);
}
