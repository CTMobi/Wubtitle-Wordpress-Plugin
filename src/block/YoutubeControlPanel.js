/* eslint-disable no-console */
import { useSelect } from "@wordpress/data";
import { PanelBody, Button } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";

const YoutubeControlPanel = props => {
	const [message, setMessage] = useState("");
	const isDisabled = props.url === undefined;
	let status = __("None", "ear2words");

	useSelect(select => {
		if (props.url === undefined) {
			return;
		}
		const transcript = select("core").getEntityRecords(
			"postType",
			"transcript",
			{
				metaKey: "_video_id",
				metaValue: props.url
			}
		);
		if (transcript.length > 0) {
			status = __("Created", "ear2words");
		}
	});

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
				<p style={{ margin: "0" }}>
					{`${__("Status: ", "ear2words")} ${status}`}
				</p>
				<Button
					name="sottotitoli"
					id={props.id}
					isPrimary
					onClick={onClick}
					disabled={isDisabled}
				>
					{__("Get Transcribe", "ear2words")}
				</Button>
				<p>{message}</p>
			</PanelBody>
		</InspectorControls>
	);
};

export default YoutubeControlPanel;
