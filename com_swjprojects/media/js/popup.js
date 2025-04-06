/**
 * @package       SW JProjects
 * @version       2.4.0.1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

document.addEventListener("DOMContentLoaded", function () {
	let popups = document.querySelectorAll('[popup]');
	if (popups) {
		popups.forEach(function (element) {
			//  Get url
			let url = '';
			if (element.getAttribute('href')) {
				url = element.getAttribute('href');
			} else if (element.getAttribute('popup')) {
				url = element.getAttribute('popup');
			}

			// Get name
			let name = '';
			if (element.getAttribute('title')) {
				name = element.getAttribute('title');
			} else if (element.getAttribute('data-title')) {
				name = element.getAttribute('data-title');
			} else if (element.getAttribute('data-name')) {
				name = element.getAttribute('data-name');
			}

			// Open popup
			if (url) {
				element.addEventListener('click', function (e) {
					e.preventDefault();
					return openPopup(url, name);
				});
			}
		});
	}
});

// Open popup
let popupOpen = false,
	popup = null;

function openPopup(url, name) {
	// Close popup
	if (popupOpen) {
		popup.close();
	}

	// Get winSize
	let winSize = {width: 0, height: 0};
	if (typeof (window.innerWidth) == 'number') {
		winSize.width = window.innerWidth;
		winSize.height = window.innerHeight;
	} else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		winSize.width = document.documentElement.clientWidth;
		winSize.height = document.documentElement.clientHeight;
	} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
		winSize.width = document.body.clientWidth;
		winSize.height = document.body.clientHeight;
	}
	winSize.width = (winSize.width.toFixed() * 1);
	winSize.height = (winSize.height.toFixed() * 1);

	// Get popup size
	let size = {
		width: ((winSize.width / 100 * 90).toFixed() * 1),
		height: ((winSize.height / 100 * 90).toFixed() * 1)
	};

	// Get popup center
	let center = {
		width: (((winSize.width - size.width) / 2).toFixed() * 1),
		height: (((winSize.height - size.height) / 2).toFixed() * 1)
	};

	// Open window
	popup = window.open(
		url,
		name,
		'width=' + size.width +
		',height=' + size.height +
		',left=' + center.width +
		',top=' + center.height
	);
	popupOpen = true;

	return popup;
}