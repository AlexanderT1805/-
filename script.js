(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const fine = matchMedia('(hover:hover)').matches;

  /* ---------- Shoe SVG ---------- */
  const shoeSVG = (c2 = '#ffffff') => `
  <svg class="shoe-svg" viewBox="0 0 400 220" style="--shoe2:${c2}" xmlns="http://www.w3.org/2000/svg">
    <path d="M20 160 Q18 190 50 195 L350 195 Q385 192 382 165 L380 150 L20 150Z" fill="#f4f4fb"/>
    <path d="M20 168 Q200 182 382 168" stroke="#0003" stroke-width="3" fill="none"/>
    <path class="upper" d="M30 150 Q18 90 50 56 Q80 44 112 62 Q150 44 175 28 L215 38 Q250 80 300 100 Q372 112 380 150Z"/>
    <path class="accent" d="M60 150 Q150 110 250 118 Q320 122 378 150Z" opacity=".85"/>
    <path class="accent" d="M120 82 Q150 40 175 28 L190 34 Q165 56 150 90Z" opacity=".9"/>
    <path d="M205 44 L240 70 M190 62 L228 86 M175 78 L215 100" stroke="#fff" stroke-width="7" stroke-linecap="round"/>
    <path d="M140 118 Q230 90 320 128" stroke="#0004" stroke-width="3" fill="none"/>
    <path d="M50 56 Q80 44 112 62" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" opacity=".9"/><ellipse cx="70" cy="118" rx="24" ry="14" fill="#0003"/>
  </svg>`;

  /* ---------- Loader ---------- */
  $$('.loader-shoe').forEach(e => e.innerHTML = shoeSVG());
  addEventListener('load', () => setTimeout(() => { $('#loader').classList.add('done'); startTyping(); }, 1700));

  /* ---------- Hero shoe + swatches ---------- */
  const colors = [['#ff3d71', '#ffd24d'], ['#00e5ff', '#ff3d71'], ['#8f5bff', '#00ffb3'], ['#ffb300', '#222'], ['#22c55e', '#fff']];
  $('#heroShoeWrap').innerHTML = shoeSVG(colors[0][1]);
  const sw = $('#swatches');
  colors.forEach(([c, c2], i) => {
    const b = document.createElement('button');
    b.className = 'swatch' + (i ? '' : ' active');
    b.style.background = c; b.style.color = c; b.ariaLabel = 'Цвят ' + (i + 1);
    b.onclick = () => {
      $$('.swatch').forEach(x => x.classList.remove('active')); b.classList.add('active');
      document.documentElement.style.setProperty('--shoe', c);
      $('#heroShoeWrap .shoe-svg').style.setProperty('--shoe2', c2);
      const w = $('#heroShoeWrap'); w.animate([{ rotate: '0deg' }, { rotate: '360deg' }], { duration: 700, easing: 'ease-in-out' });
    };
    sw.append(b);
  });

  /* ---------- Typing effect ---------- */
  function startTyping() {
    const words = ['СТЪПИ СМЕЛО', 'БЯГАЙ БЪРЗО', 'ИЗГЛЕЖДАЙ ТОП'];
    let w = 0, i = 0, del = false; const el = $('#typed');
    (function tick() {
      const word = words[w];
      el.textContent = word.slice(0, i);
      if (!del && i === word.length) { del = true; return setTimeout(tick, 1400); }
      if (del && i === 0) { del = false; w = (w + 1) % words.length; }
      i += del ? -1 : 1;
      setTimeout(tick, del ? 40 : 90);
    })();
  }

  /* ---------- Products ---------- */
  const products = [
    ['Aero Runner', 'run', 189, '#ff3d71', '#ffd24d', 'НОВО'],
    ['Urban Pulse', 'street', 159, '#00e5ff', '#ff3d71', ''],
    ['Retro 86', 'retro', 139, '#ffb300', '#222', 'ХИТ'],
    ['Cloud Sprint', 'run', 219, '#8f5bff', '#00ffb3', ''],
    ['Night Walker', 'street', 169, '#22c55e', '#fff', '-20%'],
    ['Vintage Court', 'retro', 129, '#e11d48', '#fff', ''],
    ['Velocity X', 'run', 239, '#06b6d4', '#facc15', 'НОВО'],
    ['City Glide', 'street', 149, '#f97316', '#111', '']
  ];
  const grid = $('#grid');
  grid.innerHTML = products.map(([n, cat, p, c, c2, badge], i) => `
    <article class="card reveal" data-cat="${cat}" style="--shoe:${c}">
      ${badge ? `<span class="badge">${badge}</span>` : ''}
      <div class="art">${shoeSVG(c2)}</div>
      <h3>${n}</h3>
      <div class="meta"><span class="price">${p} лв.</span><button class="add" data-i="${i}">В количката +</button></div>
    </article>`).join('');

  /* ---------- Filters ---------- */
  $('#filters').addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    $$('#filters button').forEach(x => x.classList.remove('active')); b.classList.add('active');
    $$('.card').forEach(c => {
      const show = b.dataset.f === 'all' || c.dataset.cat === b.dataset.f;
      c.classList.toggle('hide', !show);
      if (show) c.animate([{ opacity: 0, transform: 'scale(.85)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: 'ease-out' });
    });
  });

  /* ---------- Cart ---------- */
  let cart = [];
  const fmt = n => n + ' лв.';
  const toast = msg => { const t = $('#toast'); t.textContent = msg; t.classList.add('show'); clearTimeout(toast.t); toast.t = setTimeout(() => t.classList.remove('show'), 2200); };
  function renderCart() {
    $('#cartCount').textContent = cart.length;
    $('#cartList').innerHTML = cart.map((it, i) => `<li><span>${it.n}</span><span>${fmt(it.p)}<button data-r="${i}">✕</button></span></li>`).join('') || '<li style="color:var(--muted)">Количката е празна</li>';
    $('#cartTotal').textContent = fmt(cart.reduce((s, x) => s + x.p, 0));
  }
  const toggleCart = o => { $('#cart').classList.toggle('open', o); $('#overlay').classList.toggle('show', o); };
  $('#cartBtn').onclick = () => toggleCart(true);
  $('#closeCart').onclick = $('#overlay').onclick = () => toggleCart(false);
  $('#cartList').addEventListener('click', e => { const r = e.target.dataset.r; if (r !== undefined) { cart.splice(+r, 1); renderCart(); } });
  grid.addEventListener('click', e => {
    const b = e.target.closest('.add'); if (!b) return;
    const [n, , p, c] = products[b.dataset.i]; cart.push({ n, p });
    const r = b.getBoundingClientRect(), t = $('#cartBtn').getBoundingClientRect();
    const f = document.createElement('div'); f.className = 'fly'; f.style.background = c;
    f.style.left = r.left + 'px'; f.style.top = r.top + 'px'; document.body.append(f);
    requestAnimationFrame(() => { f.style.left = t.left + 'px'; f.style.top = t.top + 'px'; f.style.transform = 'scale(.2)'; });
    setTimeout(() => { f.remove(); const cb = $('#cartBtn'); cb.classList.remove('bump'); void cb.offsetWidth; cb.classList.add('bump'); renderCart(); confetti(t.left + 20, t.top + 20); }, 800);
    toast(n + ' е добавена ✔');
  });
  renderCart();

  /* ---------- Newsletter ---------- */
  $('#form').onsubmit = e => { e.preventDefault(); $('#formMsg').textContent = '🎉 Благодарим! Кодът ти: STRIDE10'; const r = e.target.getBoundingClientRect(); confetti(r.left + r.width / 2, r.top, 80); e.target.reset(); };

  /* ---------- Scroll: reveal, nav, progress, counters ---------- */
  const io = new IntersectionObserver(es => es.forEach(en => {
    if (!en.isIntersecting) return; en.target.classList.add('in'); io.unobserve(en.target);
    const c = $('[data-count]', en.target); if (c) count(c);
  }), { threshold: .15 });
  $$('.reveal').forEach(el => io.observe(el));
  function count(el) {
    const end = +el.dataset.count, t0 = performance.now();
    (function s(t) { const k = Math.min((t - t0) / 1800, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - k, 3))).toLocaleString('bg'); if (k < 1) requestAnimationFrame(s); })(t0);
  }
  addEventListener('scroll', () => {
    $('#nav').classList.toggle('scrolled', scrollY > 40);
    $('#progress').style.width = scrollY / (document.documentElement.scrollHeight - innerHeight) * 100 + '%';
    $('.b1').style.translate = `0 ${scrollY * .25}px`;
  }, { passive: true });

  /* ---------- Cursor, magnetic, tilt, ripple ---------- */
  if (fine) {
    let mx = 0, my = 0, cx = 0, cy = 0;
    addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; $('#cursor-dot').style.transform = `translate(${mx}px,${my}px)`; });
    (function loop() { cx += (mx - cx) * .15; cy += (my - cy) * .15; $('#cursor').style.transform = `translate(${cx}px,${cy}px)`; requestAnimationFrame(loop); })();
    document.addEventListener('mouseover', e => $('#cursor').classList.toggle('hover', !!e.target.closest('a,button,input,.card,.feature')));

    $$('.magnetic').forEach(el => {
      el.addEventListener('mousemove', e => { const r = el.getBoundingClientRect(); el.style.transform = `translate(${(e.clientX - r.left - r.width / 2) * .3}px,${(e.clientY - r.top - r.height / 2) * .4}px)`; });
      el.addEventListener('mouseleave', () => { el.style.transform = ''; });
    });
    const tilt = el => {
      el.addEventListener('mousemove', e => {
        const r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        el.style.transform = `perspective(800px) rotateY(${(x - .5) * 16}deg) rotateX(${(.5 - y) * 16}deg) scale(1.03)`;
        el.style.setProperty('--mx', x * 100 + '%'); el.style.setProperty('--my', y * 100 + '%');
      });
      el.addEventListener('mouseleave', () => { el.style.transform = ''; });
    };
    $$('.card,.tilt').forEach(tilt);

    // hero shoe follows mouse
    const hs = $('#heroShoe'), hw = $('#heroShoeWrap');
    hs.addEventListener('mousemove', e => { const r = hs.getBoundingClientRect(); const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5; hw.style.transform = `rotateY(${x * 30}deg) rotateX(${-y * 24}deg) rotateZ(${x * -6}deg)`; });
    hs.addEventListener('mouseleave', () => hw.style.transform = '');
  }
  document.addEventListener('click', e => {
    const b = e.target.closest('.ripple'); if (!b) return;
    const r = b.getBoundingClientRect(), s = document.createElement('span'), d = Math.max(r.width, r.height);
    s.className = 'rp'; s.style.cssText = `width:${d}px;height:${d}px;left:${e.clientX - r.left - d / 2}px;top:${e.clientY - r.top - d / 2}px`;
    b.append(s); setTimeout(() => s.remove(), 600);
  });

  /* ---------- Background particles ---------- */
  const cv = $('#bg'), g = cv.getContext('2d'); let W, H, pts = [], mouse = { x: -999, y: -999 };
  const resize = () => { W = cv.width = innerWidth; H = cv.height = innerHeight; pts = Array.from({ length: Math.min(90, W / 14) }, () => ({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .5, vy: (Math.random() - .5) * .5 })); };
  resize(); addEventListener('resize', resize); addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
  (function draw() {
    g.clearRect(0, 0, W, H);
    pts.forEach((p, i) => {
      p.x += p.vx; p.y += p.vy; if (p.x < 0 || p.x > W) p.vx *= -1; if (p.y < 0 || p.y > H) p.vy *= -1;
      const dx = p.x - mouse.x, dy = p.y - mouse.y, d = Math.hypot(dx, dy);
      if (d < 120) { p.x += dx / d * 1.5; p.y += dy / d * 1.5; }
      g.fillStyle = '#ffffff55'; g.beginPath(); g.arc(p.x, p.y, 1.8, 0, 7); g.fill();
      for (let j = i + 1; j < pts.length; j++) { const q = pts[j], dd = Math.hypot(p.x - q.x, p.y - q.y); if (dd < 120) { g.strokeStyle = `rgba(0,229,255,${.18 * (1 - dd / 120)})`; g.beginPath(); g.moveTo(p.x, p.y); g.lineTo(q.x, q.y); g.stroke(); } }
    });
    requestAnimationFrame(draw);
  })();

  /* ---------- Confetti ---------- */
  function confetti(x, y, n = 40) {
    const cols = ['#ff3d71', '#00e5ff', '#ffd24d', '#8f5bff', '#22c55e'];
    for (let i = 0; i < n; i++) {
      const p = document.createElement('i'), a = Math.random() * 6.28, v = 60 + Math.random() * 120;
      p.style.cssText = `position:fixed;left:${x}px;top:${y}px;width:8px;height:8px;background:${cols[i % 5]};z-index:400;pointer-events:none;border-radius:${i % 2 ? '50%' : '2px'}`;
      document.body.append(p);
      p.animate([{ transform: 'translate(0,0) rotate(0)', opacity: 1 }, { transform: `translate(${Math.cos(a) * v}px,${Math.sin(a) * v + 120}px) rotate(${Math.random() * 720}deg)`, opacity: 0 }], { duration: 900 + Math.random() * 500, easing: 'cubic-bezier(.2,.8,.4,1)' }).onfinish = () => p.remove();
    }
  }

  // Konami-style easter egg: click the logo 5 times
  let clicks = 0; $('.logo').addEventListener('click', e => { if (++clicks >= 5) { clicks = 0; confetti(innerWidth / 2, innerHeight / 2, 150); toast('🎉 Намери тайната!'); } });
})();
