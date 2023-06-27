/**
 * Do's and don'ts block.
 *
 * @package
 */

/**
 * Internal dependencies.
 */
import Edit from './edit';

/**
 * WordPress Dependencies.
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Register block type.
 */
registerBlockType( 'aquila-blocks/dos-and-donts', {
	/**
	 * Block title.
	 *
	 * @type {string}
	 */
	title: __( "Dos and dont's", 'fwp-Listivo-child-c4trade' ),

	/**
	 * Block icon.
	 *
	 * @type {string}
	 */
	icon: 'editor-table',

	/**
	 * Block description.
	 *
	 * @type {string}
	 */
	description: __( 'Add headings and text', 'fwp-Listivo-child-c4trade' ),

	/**
	 * Block category.
	 *
	 * @type {string}
	 */
	category: 'aquila',

	edit: Edit,

	/**
	 * Save
	 *
	 * @return {Object} Save content.
	 */
	save() {
		return (
			<div className="aquila-dos-and-donts">
				<InnerBlocks.Content />
			</div>
		);
	},
} );
