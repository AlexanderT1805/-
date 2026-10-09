/**
 * PrintPop — поток „Открий“.
 *  - смесва реда на моделите при всяко отваряне;
 *  - пуска видеото само на видимата карта (не и при намалено движение);
 *  - плъзгане надясно = „Любими“ (изисква профил + плъгин), наляво = „Пропусни“;
 *  - докосване отваря продуктовата страница.
 * „Любими“ не се симулира: без свързан плъгин не се записва нищо.
 */
(function () {
	'use strict';

	var PP = window.PrintPop;
	var feed = document.querySelector('[data-pp-feed]');
	var data = (PP && PP.data) || window.printpopData || { i18n: {} };
	var t = data.i18n || {};
	var reduce = PP ? PP.reduceMotion : false;

	/* Филтри (лист) ------------------------------------------------------- */
	var sheet = document.querySelector('[data-pp-filters]');
	if (sheet) {
		var opener = document.querySelector('[data-pp-filter-open]');
		var lastFocus = null;
		var openSheet = function () {
			lastFocus = document.activeElement;
			sheet.hidden = false;
			document.body.style.overflow = 'hidden';
			var first = sheet.querySelector('select, input, button');
			if (first) { first.focus(); }
		};
		var closeSheet = function () {
			sheet.hidden = true;
			document.body.style.overflow = '';
			if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
		};
		if (opener) { opener.addEventListener('click', openSheet); }
		sheet.querySelectorAll('[data-pp-filter-close]').forEach(function (el) { el.addEventListener('click', closeSheet); });
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !sheet.hidden) { closeSheet(); }
		});
	}

	if (!feed) { return; }

	var end = feed.querySelector('[data-pp-end]');

	/* Смесване на реда (Fisher–Yates) ------------------------------------ */
	var reels = Array.prototype.slice.call(feed.querySelectorAll('.pp-reel:not(.pp-reel--end)'));
	for (var i = reels.length - 1; i > 0; i--) {
		var j = Math.floor(Math.random() * (i + 1));
		var tmp = reels[i]; reels[i] = reels[j]; reels[j] = tmp;
	}
	reels.forEach(function (r) { feed.insertBefore(r, end || null); });

	function liveReels() {
		return Array.prototype.slice.call(feed.querySelectorAll('.pp-reel:not([hidden])'));
	}

	/* Видео: пуска се само за видимата карта ------------------------------ */
	var videos = feed.querySelectorAll('video[data-src]');
	function loadVideo(v) {
		if (!v.getAttribute('src') && v.dataset.src) {
			v.src = v.dataset.src;
			v.preload = 'metadata';
		}
	}

	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				var v = entry.target.querySelector('video');
				if (!v) { return; }
				if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
					loadVideo(v);
					if (!reduce) {
						var p = v.play();
						if (p && p.catch) { p.catch(function () { /* автоматичното пускане може да е блокирано */ }); }
					}
				} else {
					v.pause();
				}
			});
		}, { root: feed, threshold: [0, 0.6, 1] });
		reels.forEach(function (r) { io.observe(r); });
	}

	// При намалено движение: ръчен бутон за пускане/спиране.
	if (reduce) {
		videos.forEach(function (v) {
			var reel = v.closest('.pp-reel');
			var actions = reel && reel.querySelector('.pp-reel__actions');
			if (!actions) { return; }
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'pp-fab';
			b.setAttribute('aria-label', 'Play / Pause');
			b.innerHTML = '<svg class="pp-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>';
			b.addEventListener('click', function () {
				loadVideo(v);
				if (v.paused) { v.play(); } else { v.pause(); }
			});
			actions.appendChild(b);
		});
	}

	/* Навигация ------------------------------------------------------------ */
	function scrollToReel(el) {
		if (!el) { return; }
		feed.scrollTo({ top: el.offsetTop, behavior: reduce ? 'auto' : 'smooth' });
	}

	function nextOf(reel) {
		var list = liveReels();
		var idx = list.indexOf(reel);
		return list[idx + 1] || null;
	}

	function currentReel() {
		var list = liveReels();
		var h = feed.clientHeight || 1;
		return list[Math.round(feed.scrollTop / h)] || list[0];
	}

	feed.addEventListener('keydown', function (e) {
		if (e.target !== feed) { return; }
		var list = liveReels();
		var cur = currentReel();
		var idx = list.indexOf(cur);
		if (e.key === 'ArrowDown' || e.key === 'PageDown') {
			e.preventDefault();
			scrollToReel(list[Math.min(idx + 1, list.length - 1)]);
		} else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
			e.preventDefault();
			scrollToReel(list[Math.max(idx - 1, 0)]);
		}
	});

	/* Действия ------------------------------------------------------------- */
	function bounce(reel) {
		reel.style.removeProperty('transform');
		reel.style.removeProperty('--pp-like');
		reel.style.removeProperty('--pp-skip');
		if (!reduce) {
			reel.classList.remove('is-bouncing');
			void reel.offsetWidth;
			reel.classList.add('is-bouncing');
			setTimeout(function () { reel.classList.remove('is-bouncing'); }, 520);
		}
	}

	function skip(reel) {
		var next = nextOf(reel);
		reel.style.removeProperty('--pp-skip');
		reel.classList.add('is-leaving-left');
		scrollToReel(next || end);
		setTimeout(function () {
			var before = next ? next.offsetTop : 0;
			reel.hidden = true;
			if (next) {
				var snap = feed.style.scrollSnapType;
				feed.style.scrollSnapType = 'none';
				feed.scrollTop += next.offsetTop - before;
				feed.style.scrollSnapType = snap;
			}
		}, reduce ? 0 : 450);
		if (!next && PP) { PP.toast({ message: t.noMore || '', icon: 'info', timeout: 3500 }); }
	}

	function like(reel) {
		var id = reel.getAttribute('data-product-id');
		bounce(reel);

		if (!data.isLoggedIn) {
			if (PP) { PP.loginPrompt(); }
			return;
		}
		if (!data.favoritesActive) {
			// Интеграцията за любими още не е свързана — нищо не се записва.
			if (PP) { PP.toast({ message: t.favPending || '', icon: 'info', timeout: 5000 }); }
			return;
		}

		var detail = {
			productId: parseInt(id, 10),
			liked: true,
			done: function (ok, message) {
				if (ok) {
					var fab = reel.querySelector('[data-pp-action="like"]');
					if (fab) { fab.classList.add('is-active'); }
					if (PP) { PP.toast({ message: message || t.saved || '', timeout: 3000 }); }
				} else if (PP) {
					PP.toast({ message: message || t.favPending || '', icon: 'info', timeout: 5000 });
				}
			}
		};
		document.dispatchEvent(new CustomEvent('printpop:favorite', { detail: detail }));
	}

	feed.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-pp-action]');
		if (!btn) { return; }
		var reel = btn.closest('.pp-reel');
		if (btn.getAttribute('data-pp-action') === 'like') { like(reel); }
		if (btn.getAttribute('data-pp-action') === 'skip') { skip(reel); }
	});

	/* Плъзгане ------------------------------------------------------------- */
	var drag = null;
	var swiped = false;

	feed.addEventListener('pointerdown', function (e) {
		if (!e.isPrimary || (e.pointerType === 'mouse' && e.button !== 0)) { return; }
		var reel = e.target.closest('.pp-reel:not(.pp-reel--end)');
		if (!reel || e.target.closest('button')) { return; }
		drag = { reel: reel, x: e.clientX, y: e.clientY, dx: 0, active: false, id: e.pointerId };
		swiped = false;
	});

	feed.addEventListener('pointermove', function (e) {
		if (!drag || e.pointerId !== drag.id) { return; }
		var dx = e.clientX - drag.x;
		var dy = e.clientY - drag.y;

		if (!drag.active) {
			if (Math.abs(dx) < 10 && Math.abs(dy) < 10) { return; }
			if (Math.abs(dx) > Math.abs(dy) * 1.4) {
				drag.active = true;
				drag.reel.classList.add('is-dragging');
				try { drag.reel.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
			} else {
				drag = null; // вертикално — оставяме нативното превъртане
				return;
			}
		}

		drag.dx = dx;
		var w = drag.reel.clientWidth || 1;
		var progress = Math.min(Math.abs(dx) / (w * 0.35), 1);
		drag.reel.style.transform = 'translateX(' + dx * 0.7 + 'px) rotate(' + dx * 0.02 + 'deg)';
		drag.reel.style.setProperty('--pp-like', dx > 0 ? progress : 0);
		drag.reel.style.setProperty('--pp-skip', dx < 0 ? progress : 0);
	});

	function endDrag(e) {
		if (!drag || (e && e.pointerId !== drag.id)) { return; }
		var d = drag;
		drag = null;
		if (!d.active) { return; }

		swiped = true;
		setTimeout(function () { swiped = false; }, 60);
		d.reel.classList.remove('is-dragging');

		var threshold = Math.min(110, d.reel.clientWidth * 0.25);
		if (d.dx > threshold) {
			like(d.reel);
		} else if (d.dx < -threshold) {
			d.reel.style.removeProperty('transform');
			skip(d.reel);
		} else {
			bounce(d.reel);
		}
	}
	feed.addEventListener('pointerup', endDrag);
	feed.addEventListener('pointercancel', endDrag);

	// След плъзгане не отваряме продукта.
	feed.addEventListener('click', function (e) {
		if (swiped && e.target.closest('.pp-reel__link')) {
			e.preventDefault();
			e.stopPropagation();
		}
	}, true);
})();
