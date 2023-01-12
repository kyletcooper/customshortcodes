(() => {
	// Elements
	const titleInput = document.getElementById('title');
	const shortcodeInput = document.querySelector('[data-customshortcodes-input]');
	const shortcodeCopyButton = document.querySelector('[data-customshortcodes-copy]');



	// Slugify Title while Typing

	const slugifyString = string => {
		// https://mhagemann.medium.com/the-ultimate-way-to-slugify-a-url-string-in-javascript-b8e4a0d849e1

		const a = 'àáâäæãåāăąçćčđďèéêëēėęěğǵḧîïíīįìıİłḿñńǹňôöòóœøōõőṕŕřßśšşșťțûüùúūǘůűųẃẍÿýžźż·/_,:;'
		const b = 'aaaaaaaaaacccddeeeeeeeegghiiiiiiiilmnnnnoooooooooprrsssssttuuuuuuuuuwxyyzzz------'
		const p = new RegExp(a.split('').join('|'), 'g')

		return string
			.toLowerCase()
			.replace(/\s+/g, '-') // Replace spaces with -
			.replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
			.replace(/&/g, '-and-') // Replace & with 'and'
			.replace(/[^\w\-]+/g, '') // Remove all non-word characters
			.replace(/\-\-+/g, '-') // Replace multiple - with single -
			.replace(/^-+/, '') // Trim - from start of text
		// .replace(/-+$/, '') // Trim - from end of text
	}

	titleInput.addEventListener('input', () => {
		const value = slugifyString(titleInput.value);
		titleInput.value = value;
		shortcodeInput.value = `[${value}]`;
	});



	// Copy to Clipboard

	const toast = (message, type) => {
		const toast = document.createElement('div');
		toast.classList.add('customshortcodes__toast');
		toast.classList.add(`customshortcodes__toast--${type}`);
		toast.textContent = message;

		document.body.appendChild(toast);

		setTimeout(() => {
			toast.classList.add('customshortcodes__toast--leaving');

			setTimeout(() => {
				toast.remove();
			}, 500);
		}, 2000);
	}

	shortcodeCopyButton.addEventListener('click', () => {
		const value = shortcodeInput.value;

		navigator.clipboard.writeText(value)
			.then(() => {
				toast('Copied to clipboard.', 'success');
			}).catch(() => {
				console.error('Could not copy to clipboard.', 'error');
			});
	});

	shortcodeInput.addEventListener('click', () => {
		shortcodeInput.setSelectionRange(0, shortcodeInput.value.length);
	});
})();