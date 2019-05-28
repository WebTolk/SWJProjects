/*
 * @package    SW JProjects Component
 * @version    1.2.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2019 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

document.addEventListener("DOMContentLoaded", function () {
	let translate = document.querySelector('[data-translate-switcher]').getAttribute('data-default'),
		storage = window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, "")
			.split('#')[0] + '_translate',
		buttons = document.querySelectorAll('[data-translate-switcher] [data-translate]');

	// Set translate after DOM loaded
	if (sessionStorage.getItem(storage)) {
		translate = sessionStorage.getItem(storage);
	}
	switchTranslate(translate);

	// Change translate after click button
	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			button.classList.add('active');
			switchTranslate(button.getAttribute('data-translate'));
		});
	});

	// Switch translate
	function switchTranslate(active) {
		// Toggle translate fields
		document.querySelectorAll('[data-translate-input]').forEach(function (input) {
			if (input.getAttribute('data-translate') === active) {
				input.style.display = '';
			} else {
				input.style.display = 'none';
			}
		});

		// Toggle translate text
		document.querySelectorAll('[data-translate-text]').forEach(function (input) {
			if (input.getAttribute('data-translate') === active) {
				input.style.display = '';
			} else {
				input.style.display = 'none';
			}
		});

		// Set active class to button
		buttons.forEach(function (button) {
			if (button.getAttribute('data-translate') === active) {
				button.classList.add('active');
			} else {
				button.classList.remove('active');
			}
		});

		// Save translate
		sessionStorage.setItem(storage, active);
	}
});