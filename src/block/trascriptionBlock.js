import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import TranscriptionEditBlock from "./TranscriptionEditBlock";

registerBlockType("wubtitle/transcription", {
	title: __("Trascription", "ear2words"),
	icon: "megaphone",
	description: __("Enter the transcript of your video"),
	category: "embed",
	attributes: {
		contentId: {
			type: "string"
		}
	},
	edit: TranscriptionEditBlock,
	save: props => {
		return "[transcript id= " + props.attributes.contentId + " ]";
	}
});