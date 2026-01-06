import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { Spinner } from '@wordpress/components';
import Edit from './edit';
import metadata from './block.json';
import './style.scss';
import './edit.scss';

registerBlockType( metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null, // Server-side rendered
} );

