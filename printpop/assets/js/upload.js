/**
 * PrintPop — страница „Качи модел или изпрати идея“: избор на път и проверка на формат в браузъра.
 * Това е само помощ за потребителя; истинската проверка и съхранение се правят от плъгина за качване.
 */
(function () {
	'use strict';

	var data = (window.PrintPop && window.PrintPop.data) || window.printpopData || { i18n: {} };
	var t = data.i18n || {};

	/* Път: готов файл / идея ---------------------------------------------- */
	var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-pp-path]'));
	function select(tab) {
		tabs.forEach(function (b) {
			var on = b === tab;
			b.setAttribute('aria-selected', on ? 'true' : 'false');
			b.tabIndex = on ? 0 : -1;
			var panel = document.getElementById(b.getAttribute('aria-controls'));
			if (panel) { panel.hidden = !on; }
		});
	}
	tabs.forEach(function (b, i) {
		b.addEventListener('click', function () { select(b); });
		b.addEventListener('keydown', function (e) {
			var n = null;
			if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { n = tabs[(i + 1) % tabs.length]; }
			if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { n = tabs[(i - 1 + tabs.length) % tabs.length]; }
			if (n) { e.preventDefault(); n.focus(); select(n); }
		});
	});
	if (window.location.hash === '#idea' && tabs[1]) { select(tabs[1]); }

	/* Зони за файлове ---------------------------------------------------------- */
	document.querySelectorAll('[data-pp-drop]').forEach(function (zone) {
		var input = zone.querySelector('input[type="file"]');
		var nameEl = zone.querySelector('[data-pp-filename]');
		var errEl = zone.querySelector('[data-pp-fileerror]');
		var accept = (zone.getAttribute('data-accept') || '').split(',').filter(Boolean);
		if (!input) { return; }

		function check(files) {
			var bad = [];
			var names = [];
			Array.prototype.forEach.call(files, function (f) {
				var ext = (f.name.split('.').pop() || '').toLowerCase();
				names.push(f.name);
				if (accept.length && accept.indexOf(ext) === -1) { bad.push(f.name); }
			});
			if (errEl) {
				errEl.textContent = bad.length ? (t.badType || '') + accept.join(', ').toUpperCase() : '';
			}
			if (nameEl) { nameEl.textContent = bad.length ? '' : names.join(', '); }
			if (bad.length) { input.value = ''; }
		}

		input.addEventListener('change', function () { check(input.files); });

		['dragenter', 'dragover'].forEach(function (ev) {
			zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-over'); });
		});
		['dragleave', 'drop'].forEach(function (ev) {
			zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-over'); });
		});
		zone.addEventListener('drop', function (e) {
			if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
				try { input.files = e.dataTransfer.files; } catch (err) { /* стар браузър */ }
				check(e.dataTransfer.files);
			}
		});
	});
})();
