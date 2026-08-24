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
// ظهور العناصر بانسيابية أثناء التصفح (مع حماية إذا ما حمل النت)
if (window.AOS) {
    AOS.init({ once:true, duration:700, offset:60, easing:'ease-out-cubic' });
} else {
    document.querySelectorAll('[data-aos]').forEach(el => el.removeAttribute('data-aos'));
}

// ظل الشريط + زر العودة للأعلى
const nav = document.getElementById('mainNav');
const toTop = document.getElementById('toTop');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 30);
    toTop.classList.toggle('show', window.scrollY > 300);
});
toTop.addEventListener('click', () => window.scrollTo({ top:0, behavior:'smooth' }));
</script>
</body>
</html>