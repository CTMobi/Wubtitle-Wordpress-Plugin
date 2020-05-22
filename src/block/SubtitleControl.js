/*  global ear2words_button_object  */
import { ToggleControl, Button } from "@wordpress/components";
import { Fragment, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { langExten, statusExten } from "./labels.js";
import { useDispatch } from "@wordpress/data";

const SubtitleControl = ({ statusText, langText, isPublished, postId }) => {
	const [message, setMessage] = useState("");
	const entityDispatcher = useDispatch("core");
	const updateStatus = published => {
		published = !published;

		let state = "draft";
		if (published) {
			state = "enabled";
		}

		editStatus(state);

		entityDispatcher.saveEditedEntityRecord(
			"postType",
			"attachment",
			postId
		);
	};

	const onClick = () => {
		setMessage(__("Getting transcript...", "ear2words"));
		wp.ajax
			.send("get_transcript_internal_video", {
				type: "POST",
				data: {
					id: postId,
					_ajax_nonce: ear2words_button_object.ajaxnonce
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

	const editStatus = statusToEdit => {
		entityDispatcher.editEntityRecord("postType", "attachment", postId, {
			meta: { ear2words_status: statusToEdit }
		});
	};

	return (
		<Fragment>
			<p style={{ margin: "0" }}>
				{__("Status: ", "ear2words") + statusExten[statusText]}
			</p>
			<p style={{ margin: "8px 0" }}>
				{__("Language: ", "ear2words") + langExten[langText]}
			</p>
			<ToggleControl
				label={__("Published", "ear2words")}
				checked={isPublished}
				onChange={() => {
					updateStatus(isPublished);
				}}
			/>
			<Button name="sottotitoli" id={postId} isPrimary onClick={onClick}>
				{__("Get Transcribe", "ear2words")}
			</Button>
			<p>{message}</p>
		</Fragment>
	);
};

export default SubtitleControl;
