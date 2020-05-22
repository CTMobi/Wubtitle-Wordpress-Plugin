/* eslint-disable no-console */
import { PanelBody, Button } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";

const YoutubeControlPanel = props => {
	const [message, setMessage] = useState("");
	const isDisabled = props.url === undefined;
	const onClick = () => {
		setMessage(__("Getting transcript...", "ear2words"));
		wp.ajax
			.send("get_transcript", {
				type: "POST",
				data: {
					url: props.url,
					source: "youtube",
					from: "default_post_type"
				}
			})
			.then(response => {
				setMessage("Done");
				const block = wp.blocks.createBlock("wubtitle/transcription", {
					contentId: response
				});
				wp.data.dispatch("core/block-editor").insertBlocks(block);
			})
			.fail(response => {
				setMessage(response);
			});
	};

	return (
		<InspectorControls>
			<PanelBody title="Wubtitle">
				<Button
					name="sottotitoli"
					id={props.id}
					isPrimary
					onClick={onClick}
					disabled={isDisabled}
				>
					{__("Transcribe", "ear2words")}
				</Button>
				<p>{message}</p>
			</PanelBody>
		</InspectorControls>
	);
};

export default YoutubeControlPanel;
