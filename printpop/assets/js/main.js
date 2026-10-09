/**
 * PrintPop — общи скриптове: меню, известия, диалог за вход.
 * Без външни библиотеки. Уважава prefers-reduced-motion.
 */
(function () {
	'use strict';

	var data = window.printpopData || { i18n: {} };
	var t = data.i18n || {};
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	var ICON_CHECK = '<svg class="pp-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';
	var ICON_INFO = '<svg class="pp-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>';

	/* Мобилно меню ------------------------------------------------------- */
	var toggle = document.querySelector('[data-pp-menu-toggle]');
	var nav = document.querySelector('[data-pp-nav]');
	if (toggle && nav) {
		var setMenu = function (open) {
			nav.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		};
		toggle.addEventListener('click', function () {
			setMenu(toggle.getAttribute('aria-expanded') !== 'true');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				setMenu(false);
				toggle.focus();
			}
		});
		document.addEventListener('click', function (e) {
			if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
				setMenu(false);
			}
		});
	}

	/* Известия ------------------------------------------------------------ */
	var toastRoot = document.querySelector('[data-pp-toasts]');

	/**
	 * @param {Object} opts { message, icon: 'check'|'info', actions: [{label, href?, onClick?}], timeout }
	 */
	function toast(opts) {
		if (!toastRoot) { return null; }
		var el = document.createElement('div');
		el.className = 'pp-toast';
		el.setAttribute('role', 'status');

		var icon = document.createElement('span');
		icon.className = 'pp-toast__icon';
		icon.innerHTML = opts.icon === 'info' ? ICON_INFO : ICON_CHECK;

		var body = document.createElement('div');
		body.className = 'pp-toast__body';
		var msg = document.createElement('div');
		msg.textContent = opts.message || '';
		body.appendChild(msg);

		function remove() {
			if (!el.parentNode) { return; }
			el.classList.add('is-leaving');
			setTimeout(function () { if (el.parentNode) { el.parentNode.removeChild(el); } }, reduceMotion ? 0 : 250);
		}

		if (opts.actions && opts.actions.length) {
			var row = document.createElement('div');
			row.className = 'pp-toast__actions';
			opts.actions.forEach(function (a) {
				var node;
				if (a.href) {
					node = document.createElement('a');
					node.href = a.href;
				} else {
					node = document.createElement('button');
					node.type = 'button';
					node.className = 'pp-toast__btn';
				}
				node.textContent = a.label;
				node.addEventListener('click', function () {
					if (a.onClick) { a.onClick(); }
					if (!a.href) { remove(); }
				});
				row.appendChild(node);
			});
			body.appendChild(row);
		}

		el.appendChild(icon);
		el.appendChild(body);
		toastRoot.appendChild(el);

		var timeout = typeof opts.timeout === 'number' ? opts.timeout : 6000;
		if (timeout > 0) { setTimeout(remove, timeout); }
		return { close: remove };
	}

	/* Диалог: нужен е профил --------------------------------------------- */
	function loginPrompt() {
		if (typeof HTMLDialogElement === 'undefined') {
			if (data.accountUrl) { window.location.href = data.accountUrl; }
			return;
		}
		var dlg = document.createElement('dialog');
		dlg.className = 'pp-dialog';
		dlg.setAttribute('aria-labelledby', 'pp-login-title');

		var h = document.createElement('h2');
		h.id = 'pp-login-title';
		h.textContent = t.loginTitle || '';
		var p = document.createElement('p');
		p.className = 'pp-muted';
		p.textContent = t.loginText || '';

		var actions = document.createElement('div');
		actions.className = 'pp-actions';
		if (data.accountUrl) {
			var go = document.createElement('a');
			go.className = 'pp-btn pp-btn--primary';
			go.href = data.accountUrl;
			go.textContent = t.loginCta || '';
			actions.appendChild(go);
		}
		var later = document.createElement('button');
		later.type = 'button';
		later.className = 'pp-btn pp-btn--ghost';
		later.textContent = t.later || '';
		later.addEventListener('click', function () { dlg.close(); });
		actions.appendChild(later);

		dlg.appendChild(h);
		dlg.appendChild(p);
		dlg.appendChild(actions);
		document.body.appendChild(dlg);
		dlg.addEventListener('close', function () { if (dlg.parentNode) { dlg.parentNode.removeChild(dlg); } });
		dlg.showModal();
	}

	window.PrintPop = {
		toast: toast,
		loginPrompt: loginPrompt,
		reduceMotion: reduceMotion,
		data: data
	};
})();
