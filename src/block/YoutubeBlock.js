import { createHigherOrderComponent } from "@wordpress/compose";
import { addFilter } from "@wordpress/hooks";
import YoutubeControlPanel from "./YoutubeControlPanel";
import { Fragment } from "@wordpress/element";

const withInspectorControls = BlockEdit => {
	return props => {
		if (props.name !== "core-embed/youtube") {
			return <BlockEdit {...props} />;
		}
		return (
			<Fragment>
				<BlockEdit {...props} />
				<YoutubeControlPanel
					{...props.attributes}
					setAttributes={props.setAttributes}
				/>
			</Fragment>
		);
	};
};

const ExtendVideoBlock = createHigherOrderComponent(
	withInspectorControls,
	"withInspectorControls"
);

addFilter(
	"editor.BlockEdit",
	"ear2words/with-inspector-controls",
	ExtendVideoBlock
);
