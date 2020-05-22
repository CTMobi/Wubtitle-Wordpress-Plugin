document.addEventListener("DOMContentLoaded", function() {
	const button = document.getElementById("insert-my-media");
	if (button) {
		button.addEventListener("click", () => {
			wp.media.frame.detach();
			wp.media.editor.remove();
			openMediaWindow();
		});
	}
	const windowTrascriptions = wp.media({
		frame: "post",
		state: "embed"
	});
	windowTrascriptions.on("select", () => {
		const embedUrl = document.getElementById("embed-url-field").value;
		wp.media.editor.insert(
			"[embed transcription='enable' ]" + embedUrl + "[/embed]"
		);
	});
	let isOpened = false;

	const openMediaWindow = () => {
		windowTrascriptions.open();
		if (!isOpened) {
			document.getElementById("menu-item-insert").remove();
			document.getElementById("menu-item-gallery").remove();
			document.getElementById("menu-item-playlist").remove();
			document.getElementById("menu-item-video-playlist").remove();
			document.getElementById("menu-item-featured-image").remove();
			document.getElementById("menu-item-embed").innerHTML =
				"Wubtitle Transcription";
			document.getElementById("media-frame-title").innerHTML =
				"<h1> Wubtitle Transcription </h1>";
			isOpened = true;
		}
	};
	return false;
});
