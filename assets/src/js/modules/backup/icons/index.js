import * as SvgIcons from './implode';
// import { isEmpty } from 'lodash';

/**
 * Get icon component.
 *
 * @param {String} option Option.
 *
 * @return {*|SvgCheck} SVG Component.
 */
export const getIconComponent = ( option ) => {
	const IconsMap = {
		// dos: SvgIcons.Check,
		// donts: SvgIcons.Cross,
		QuickLogin: SvgIcons.QuickLogin,
	};

	// ! isEmpty( option )
	return ( option != '' && option in IconsMap )
		? IconsMap[ option ]
		: IconsMap.dos;
};
