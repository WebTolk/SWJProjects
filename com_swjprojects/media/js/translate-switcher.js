/*
 * @package    SW JProjects Component
 * @version    1.0.1
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2018 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

document.addEventListener("DOMContentLoaded", function () {
	let buttons = document.querySelectorAll('[data-translate-switcher] [data-translate]');
	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			for (let i = 0; i < buttons.length; i++) {
				buttons[i].classList.remove('active');
			}
			button.classList.add('active');
			switchTranslate();
		});
	});
	switchTranslate();

	// Switch translate
	function switchTranslate() {
		let active = document.querySelector('[data-translate-switcher] .active')
			.getAttribute('data-translate');

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
	}
});