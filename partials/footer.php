</div>
</div>
<footer class="site-footer">
  <div class="container footer-shell">
    <div class="footer-brand">
      <img src="<?= url('assets/img/logo-thudocar.png') ?>" alt="Thủ Đô Car">
      <div>
        <div class="footer-title">Showroom Thủ Đô Car</div>
        <div class="footer-text">Uy tín • Chất lượng • Minh bạch</div>
      </div>
    </div>
    <div class="footer-info">
      <div><b>Hotline</b><span>0818 060 461</span></div>
      <div><b>Địa chỉ</b><span>11 Hoàng Quán Chi, Cầu Giấy, Hà Nội</span></div>
      <div><b>Bản quyền</b><span>© <?= date('Y') ?> Thủ Đô Car</span></div>
    </div>
  </div>
</footer>
<div class="toast-wrap" id="toastWrap"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
window.currencyFormat = n => new Intl.NumberFormat('vi-VN').format(Number(n||0));
window.showToast = function(type, message){
  const wrap = document.getElementById('toastWrap');
  if(!wrap || !message) return;
  const icon = type === 'success' ? 'bi-check2-circle' : (type === 'error' ? 'bi-x-circle' : 'bi-info-circle');
  const card = document.createElement('div');
  card.className = 'toast-card ' + (type || 'info');
  card.innerHTML = `<div class="icon"><i class="bi ${icon}"></i></div><div><div class="fw-bold mb-1">Thông báo</div><div>${message}</div></div>`;
  wrap.appendChild(card);
  setTimeout(()=>{card.style.opacity='0';card.style.transform='translateY(-6px)';}, 3200);
  setTimeout(()=>card.remove(), 3600);
};
<?php if ($msg = flash_get('success')): ?>showToast('success', <?= json_encode($msg) ?>);<?php endif; ?>
<?php if ($msg = flash_get('error')): ?>showToast('error', <?= json_encode($msg) ?>);<?php endif; ?>
<?php if ($msg = flash_get('info')): ?>showToast('info', <?= json_encode($msg) ?>);<?php endif; ?>

document.addEventListener('DOMContentLoaded', function(){
  const buy = document.querySelector('[name="giamua_vao"]');
  const spa = document.querySelector('[name="tongchiphi_spa"]');
  const cost = document.querySelector('[name="giavon"]');
  function recalc(){
    if(!buy || !spa || !cost) return;
    cost.value = (parseFloat(buy.value||0) || 0) + (parseFloat(spa.value||0) || 0);
  }
  if (buy && spa && cost) {
    buy.addEventListener('input', recalc); spa.addEventListener('input', recalc);
  }
  const imageInput = document.querySelector('[name="hinhanh"]');
  const imageFileInput = document.querySelector('[name="hinhanh_file"]');
  const preview = document.getElementById('imagePreview');
  if (preview && (imageInput || imageFileInput)) {
    const emptyPreview = () => {
      preview.innerHTML = `<div class="text-center"><div class="empty-icon mx-auto"><i class="bi bi-image"></i></div><div class="preview-note">Tải ảnh xe hoặc nhập đường dẫn ảnh</div></div>`;
    };
    const renderFromText = () => {
      const src = imageInput ? imageInput.value.trim() : '';
      if (src) {
        preview.innerHTML = `<img src="${src}" alt="preview" class="thumb-lg"><div class="preview-note mt-2">Xem ảnh xe</div>`;
      } else {
        emptyPreview();
      }
    };
    const renderFromFile = file => {
      if (!file) return renderFromText();
      const reader = new FileReader();
      reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" alt="preview" class="thumb-lg"><div class="preview-note mt-2">Ảnh sẽ được tải lên khi lưu xe</div>`;
      };
      reader.readAsDataURL(file);
    };
    if (imageInput) imageInput.addEventListener('input', () => {
      if (imageFileInput && imageFileInput.files && imageFileInput.files[0]) return;
      renderFromText();
    });
    if (imageFileInput) imageFileInput.addEventListener('change', () => {
      renderFromFile(imageFileInput.files && imageFileInput.files[0] ? imageFileInput.files[0] : null);
    });
    if (imageFileInput && imageFileInput.files && imageFileInput.files[0]) {
      renderFromFile(imageFileInput.files[0]);
    } else {
      renderFromText();
    }
  }
  document.querySelectorAll('[data-password-toggle]').forEach(btn=>{
    btn.addEventListener('click', function(){
      const target = document.getElementById(this.dataset.passwordToggle);
      if (!target) return;
      target.type = target.type === 'password' ? 'text' : 'password';
      this.innerHTML = target.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
  });
});
</script>
</body>
</html>
