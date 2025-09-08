import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	attributes: {
		maximum: {
			type: 'number',
			default: 4,
		},
	},
	edit: Edit,
	save: Save,
} );
