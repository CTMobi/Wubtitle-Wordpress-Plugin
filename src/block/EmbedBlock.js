import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import EmbedControlPanel from './EmbedControlPanel';
import { Fragment } from '@wordpress/element';

const withInspectorControls = (BlockEdit) => {
	return (props) => {
		let name;
		if (props.name === 'core/embed') {
			switch (props.attributes.providerNameSlug) {
				case 'youtube':
					name = 'core-embed/youtube';
					break;
				case 'vimeo':
					name = 'core-embed/vimeo';
					break;
				default:
					name = '';
			}
		}
		const isYoutubeOrVimeo = props.name === 'core/embed' && name !== '';
		if (
			props.name !== 'core-embed/youtube' &&
			props.name !== 'core-embed/vimeo' &&
			!isYoutubeOrVimeo
		) {
			return <BlockEdit {...props} />;
		}
		return (
			<Fragment>
				<BlockEdit {...props} />
				<EmbedControlPanel
					{...props.attributes}
					setAttributes={props.setAttributes}
					block={name || props.name}
				/>
			</Fragment>
		);
	};
};

const ExtendVideoBlock = createHigherOrderComponent(
	withInspectorControls,
	'withInspectorControls'
);

addFilter(
	'editor.BlockEdit',
	'wubtitle/with-inspector-controls',
	ExtendVideoBlock
);
