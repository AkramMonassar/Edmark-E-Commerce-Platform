</main>

<footer class="bg-dark text-light mt-5 py-4">
  <div class="container">
    <div class="row gy-3">
      <div class="col-md-4">
        <h6><i class="bi bi-person-circle"></i> عن الحساب</h6>
        <ul class="list-unstyled small">
          <li><a class="text-decoration-none" style="color:#ccc" href="cart.php">سلة الشراء</a></li>
          <li><a class="text-decoration-none" style="color:#ccc" href="checkout.php">الدفع</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6><i class="bi bi-shop"></i> عن الموقع</h6>
        <ul class="list-unstyled small">
          <li><a class="text-decoration-none" style="color:#ccc" href="about_us.php">من نحن</a></li>
          <li><a class="text-decoration-none" style="color:#ccc" href="contact_us.php">تواصل معنا</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6><i class="bi bi-share"></i> تابعنا</h6>
        <a class="text-light fs-5 me-2" href="http://facebook.com"><i class="bi bi-facebook"></i></a>
        <a class="text-light fs-5 me-2" href="http://twitter.com"><i class="bi bi-twitter-x"></i></a>
        <a class="text-light fs-5" href="http://google.com"><i class="bi bi-google"></i></a>
      </div>
    </div>
    <hr>
    <div class="text-center small">Copyrights &copy; 2016 - 2026 . All rights reserved</div>
  </div>
</footer>

<button id="toTop" title="العودة للأعلى"><i class="bi bi-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  /* ===== 1) الحركة والتمرير ===== */
  if (window.AOS) {
    AOS.init({
      once: true,
      duration: 700,
      offset: 60,
      easing: 'ease-out-cubic'
    });
  } else {
    document.querySelectorAll('[data-aos]').forEach(el => el.removeAttribute('data-aos'));
  }
  const nav = document.getElementById('mainNav');
  const toTop = document.getElementById('toTop');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 30);
    toTop.classList.toggle('show', window.scrollY > 300);
  });
  toTop.addEventListener('click', () => window.scrollTo({
    top: 0,
    behavior: 'smooth'
  }));

  /* ===== 2) أساسيات AJAX ===== */
  async function api(action, data = {}, method = 'POST') {
    if (method === 'GET') {
      const res = await fetch('api/cart.php?action=' + action);
      return res.json();
    }
    const body = new FormData();
    body.append('action', action);
    body.append('csrf_token', window.CSRF || '');
    for (const k in data) body.append(k, data[k]);
    const res = await fetch('api/cart.php', {
      method: 'POST',
      body
    });
    return res.json();
  }

  function updateCartUI(s) {
    const badge = document.getElementById('cartBadge');
    const total = document.getElementById('cartTotal');
    if (badge) {
      badge.textContent = s.count;
      badge.classList.toggle('d-none', s.count === 0);
    }
    if (total) {
      total.textContent = s.total + '$';
      total.classList.toggle('d-none', s.total === 0);
    }
  }

  function toast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'alert alert-' + type + ' position-fixed top-0 start-50 translate-middle-x mt-3 py-2 shadow';
    t.style.zIndex = 2000;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
  }

  /* ===== 3) إضافة للسلة بدون تحميل ===== */
  document.querySelectorAll('.js-add-form').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const id = form.querySelector('[name="id"]').value;
      const r = await api('add', {
        id
      });
      if (r.ok) {
        updateCartUI(r);
        toast('تمت الإضافة إلى السلة ✅');
      } else if (r.error === 'login_required') location.href = 'login.php';
      else if (r.error === 'already_in_cart') toast('المنتج موجود في سلتك بالفعل', 'warning');
      else if (r.error === 'out_of_stock') toast('هذا المنتج نفد من المخزون', 'warning');
      else toast(r.error === 'out_of_stock' ? 'الكمية المطلوبة تتجاوز المخزون المتاح' : 'تعذر تحديث الكمية', 'warning');
    });
  });

  /* ===== 4) حذف منتج بدون تحميل ===== */
  document.querySelectorAll('form [name="delete2"]').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();
      const tbody = btn.closest('tbody');
      const id = btn.form.querySelector('[name="delete1"]').value;
      const r = await api('delete', {
        id
      });
      if (r.ok) {
        btn.closest('tr').remove();
        if (tbody && !tbody.querySelector('tr')) location.reload(); // لإظهار حالة "السلة فارغة"
        else {
          updateCartUI(r);
          toast('تم حذف المنتج');
        }
      }
    });
  });

</script>
<script>
// ===== العدّاد v2: أزرار + كتابة مباشرة + الحد = المخزون =====
document.querySelectorAll('.qty-stepper').forEach(st => {
    const id    = st.dataset.id;
    const stock = Math.max(1, parseInt(st.dataset.stock, 10) || 1);
    const inp   = st.querySelector('.qty-input');
    const plus  = st.querySelector('.plus');
    const minus = st.querySelector('.minus');

    const clamp = v => Math.min(stock, Math.max(1, v));

    const paint = q => {
        inp.value = q;
        inp.classList.remove('pop'); void inp.offsetWidth; inp.classList.add('pop');
        minus.disabled = q <= 1;
        plus.disabled  = q >= stock;
    };

    const send = q => {
        paint(q);
        api('qty', { id, qty: q }).then(r => {
            if (r.ok) {
                const tr    = st.closest('tr');
                const price = parseInt(tr.querySelector('.cell-price').textContent, 10) || 0;
                const cell  = tr.querySelector('.cell-total');
                cell.textContent = (price * q) + '$';
                cell.classList.remove('flash'); void cell.offsetWidth; cell.classList.add('flash');
                updateCartUI(r);
            } else if (r.error === 'out_of_stock') {
                toast('الحد الأقصى المتاح من المخزون: ' + stock, 'warning');
            } else {
                toast('تعذر تحديث الكمية', 'danger');
            }
        });
    };

    minus.disabled = (+inp.value) <= 1;
    plus.disabled  = (+inp.value) >= stock;

    plus.addEventListener('click',  () => send(clamp((+inp.value || 1) + 1)));
    minus.addEventListener('click', () => send(clamp((+inp.value || 1) - 1)));

    // كتابة بالكيبورد مع debounce
    let t = null;
    inp.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
            let v = parseInt(inp.value, 10);
            if (!v || v < 1) v = 1;
            if (v > stock) { toast('المتوفر بالمخزون: ' + stock + ' فقط', 'warning'); v = stock; }
            send(v);
        }, 400);
    });
});
</script>
</body>

</html>