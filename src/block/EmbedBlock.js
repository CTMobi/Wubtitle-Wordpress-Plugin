import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import EmbedControlPanel from './EmbedControlPanel';
import { Fragment } from '@wordpress/element';

const withInspectorControls = (BlockEdit) => {
	return (props) => {
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
