import { ToggleControl } from "@wordpress/components";
import { Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { langExten, statusExten } from "./labels.js";
import { useDispatch } from "@wordpress/data";

const SubtitleControl = ({ statusText, langText, isPublished, postId }) => {
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
		</Fragment>
	);
};

export default SubtitleControl;
