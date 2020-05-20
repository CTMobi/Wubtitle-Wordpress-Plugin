/* eslint-disable no-console */
import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";
import { TextControl, Button } from "@wordpress/components";
import { useState, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { select } from "@wordpress/data";
import domReady from "@wordpress/dom-ready";

const TranscriptPanel = () => {
	const [message, setMessage] = useState("");
	const [inputValue, setInputValue] = useState("");
	const isDisabled = inputValue === "";
	const getTranscript = () => {
		setMessage(__("Getting transcript...", "ear2words"));

		wp.ajax
			.send("get_transcript", {
				type: "POST",
				data: {
					url: inputValue,
					source: "youtube",
					from: "transcript_post_type"
				}
			})
			.then(response => {
				setMessage(__("Done", "ear2words"));
				const block = wp.blocks.createBlock("core/paragraph", {
					content: response
				});
				wp.data.dispatch("core/block-editor").insertBlocks(block);
			})
			.fail(response => {
				setMessage(response);
			});
	};
	return (
		<Fragment>
			<PluginDocumentSettingPanel
				name="transcript-panel"
				title="Transcript"
			>
				<TextControl
					label="video url"
					id="input"
					value={inputValue}
					onChange={urlVideo => {
						setInputValue(urlVideo);
					}}
				/>
				<Button
					name="transcript"
					isPrimary
					onClick={getTranscript}
					disabled={isDisabled}
				>
					{__("Get transcript", "ear2words")}
				</Button>
				<p>{message}</p>
			</PluginDocumentSettingPanel>
		</Fragment>
	);
};

domReady(() => {
	if (select("core/editor").getCurrentPostType() !== "transcript") return;

	registerPlugin("transcript-panel", {
		render: TranscriptPanel,
		icon: ""
	});
});
