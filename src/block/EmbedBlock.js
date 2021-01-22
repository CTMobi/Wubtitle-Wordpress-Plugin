import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import EmbedControlPanel from './EmbedControlPanel';
import { Fragment } from '@wordpress/element';

const withInspectorControls = (BlockEdit) => {
	return (props) => {
		if (props.name === 'core/embed') {
			switch (props.attributes.providerNameSlug) {
				case 'youtube':
					props.name = 'core-embed/youtube';
					break;
				case 'vimeo':
					props.name = 'core-embed/vimeo';
					break;
				default:
					props.name = '';
			}
		}
		if (
			props.name !== 'core-embed/youtube' &&
			props.name !== 'core-embed/vimeo'
		) {
			return <BlockEdit {...props} />;
		}
		return (
			<Fragment>
				<BlockEdit {...props} />
				<EmbedControlPanel
					{...props.attributes}
					setAttributes={props.setAttributes}
					block={props.name}
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
