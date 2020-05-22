import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import TranscriptionEditBlock from "./TranscriptionEditBlock";

registerBlockType("wubtitle/transcription", {
	title: __("Trascription", "ear2words"),
	icon: "megaphone",
	description: __("Enter the transcript of your video", "ear2words"),
	category: "embed",
	attributes: {
		contentId: {
			type: "int"
		}
	},
	edit: TranscriptionEditBlock,
	save: props => {
		return (
			<div>{"[transcript id= " + props.attributes.contentId + " ]"}</div>
		);
	}
});
