<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$title = 'Tổng quan';
$brands = $pdo->query("SELECT DISTINCT hangxe FROM xe WHERE hangxe IS NOT NULL AND hangxe<>'' ORDER BY hangxe ASC")->fetchAll(PDO::FETCH_COLUMN);
$modelsRows = $pdo->query("SELECT DISTINCT hangxe, dongxe FROM xe WHERE dongxe IS NOT NULL AND dongxe<>'' ORDER BY hangxe ASC, dongxe ASC")->fetchAll();

$groups = [
    'duoi1ty' => ['title' => 'XE DƯỚI 1 TỶ', 'where' => 'COALESCE(giaban_dukien,0) < 1000000000'],
    '1den2ty' => ['title' => 'XE TỪ 1 ĐẾN 2 TỶ', 'where' => 'COALESCE(giaban_dukien,0) >= 1000000000 AND COALESCE(giaban_dukien,0) <= 2000000000'],
    'tren2ty' => ['title' => 'XE TRÊN 2 TỶ', 'where' => 'COALESCE(giaban_dukien,0) > 2000000000'],
];
$groupCars = [];
foreach ($groups as $key => $group) {
    $sql = "SELECT maxe, hangxe, dongxe, namsanxuat, giaban_dukien, mota, hinhanh, bienso, trangthai FROM xe WHERE {$group['where']} ORDER BY COALESCE(giaban_dukien,0) ASC, maxe DESC LIMIT 4";
    $groupCars[$key] = $pdo->query($sql)->fetchAll();
}

$quickCards = [
    ['label' => 'Danh sách xe', 'icon' => 'bi-car-front-fill', 'href' => url('cars/index.php'), 'allowed' => has_role(['giamdoc','sales','thumua','kithuat','admin'])],
    ['label' => 'Khách hàng', 'icon' => 'bi-people-fill', 'href' => url('customers/index.php'), 'allowed' => has_role(['giamdoc','sales','ketoan','admin'])],
    ['label' => 'Giao dịch', 'icon' => 'bi-receipt-cutoff', 'href' => url('transactions/index.php'), 'allowed' => has_role(['giamdoc','sales','ketoan','admin'])],
    ['label' => 'Spa xe', 'icon' => 'bi-brush-fill', 'href' => url('spa/index.php'), 'allowed' => has_role(['giamdoc','kithuat','ketoan','admin'])],
];

$heroSlides = [
    url('assets/img/showroom.jpg'),
    url('assets/img/bia.jpg'),
    url('assets/img/viec.jpg'),
];
require_once __DIR__ . '/partials/header.php';
?>
<div class="home-hero">
  <div class="quick-search-panel">
    <div class="search-pill"><i class="bi bi-search"></i> Tìm xe</div>
    <div class="quick-search-title">Tìm chiếc xe dành cho bạn</div>
    <form method="get" action="<?= url('cars/index.php') ?>" id="homeSearchForm">
      <div class="mb-3">
        <label class="form-label">Hãng xe</label>
        <select class="form-select" name="brand" id="brandSelect">
          <option value="">Chọn hãng xe</option>
          <?php foreach ($brands as $brand): ?>
            <option value="<?= e($brand) ?>"><?= e($brand) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="form-label">Dòng xe</label>
        <select class="form-select" name="model" id="modelSelect">
          <option value="">Chọn dòng xe</option>
          <?php foreach ($modelsRows as $row): ?>
            <option value="<?= e($row['dongxe']) ?>" data-brand="<?= e($row['hangxe']) ?>"><?= e($row['dongxe']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <input type="hidden" name="kw" id="kwField">
      <button class="btn btn-primary w-100 btn-lg">Tìm kiếm</button>
    </form>
  </div>

  <div class="hero-banner">
    <div class="hero-slides">
      <?php foreach ($heroSlides as $i => $slide): ?>
        <div class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>">
          <img src="<?= e($slide) ?>" alt="Banner xe">
        </div>
      <?php endforeach; ?>
      <div class="hero-overlay"></div>
      <button class="hero-arrow left" type="button" data-hero-prev><i class="bi bi-chevron-left"></i></button>
      <button class="hero-arrow right" type="button" data-hero-next><i class="bi bi-chevron-right"></i></button>
      <div class="hero-dots">
        <?php foreach ($heroSlides as $i => $slide): ?>
          <button type="button" class="hero-dot <?= $i === 0 ? 'is-active' : '' ?>" data-hero-dot="<?= $i ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="home-shortcuts">
  <?php foreach ($quickCards as $card): ?>
    <a class="shortcut-card <?= $card['allowed'] ? '' : 'opacity-50' ?>" href="<?= $card['allowed'] ? e($card['href']) : 'javascript:void(0)' ?>">
      <span class="shortcut-icon"><i class="bi <?= e($card['icon']) ?>"></i></span>
      <span><?= e($card['label']) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="price-section">
  <div class="section-title-main">TIN BÁN XE THEO KHOẢNG GIÁ</div>
  <?php foreach ($groups as $key => $group): ?>
    <h2><?= e($group['title']) ?></h2>
    <div class="cars-grid mb-4">
      <?php foreach ($groupCars[$key] as $idx => $car): ?>
        <article class="car-card-home">
          <img class="car-image" src="<?= e(get_car_image($car, $idx)) ?>" alt="<?= e(car_title($car)) ?>">
          <div class="car-card-body">
            <div class="car-title"><?= e(car_title($car)) ?></div>
            <div class="car-price"><?= money($car['giaban_dukien'] ?? 0) ?></div>
            <div class="car-desc"><?= e(short_text($car['mota'] ?? '')) ?></div>
            <a class="car-link" href="<?= url('cars/view.php?id=' . (int)$car['maxe']) ?>">Xem chi tiết <i class="bi bi-chevron-right"></i></a>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (empty($groupCars[$key])): ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <div class="empty-icon mx-auto"><i class="bi bi-car-front"></i></div>
          <div>Chưa có xe trong nhóm giá này.</div>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const brand = document.getElementById('brandSelect');
  const model = document.getElementById('modelSelect');
  const kwField = document.getElementById('kwField');
  const form = document.getElementById('homeSearchForm');
  function filterModels(){
    const current = brand.value;
    Array.from(model.options).forEach((opt, idx) => {
      if (idx === 0) return;
      const visible = !current || opt.dataset.brand === current;
      opt.hidden = !visible;
      if (!visible && opt.selected) model.value = '';
    });
  }
  filterModels();
  brand.addEventListener('change', filterModels);
  form.addEventListener('submit', function(){
    kwField.value = [brand.value, model.value].filter(Boolean).join(' ');
  });

  const slides = Array.from(document.querySelectorAll('.hero-slide'));
  const dots = Array.from(document.querySelectorAll('.hero-dot'));
  let idx = 0;
  function renderHero(next){
    idx = (next + slides.length) % slides.length;
    slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
    dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
  }
  document.querySelector('[data-hero-prev]')?.addEventListener('click', () => renderHero(idx - 1));
  document.querySelector('[data-hero-next]')?.addEventListener('click', () => renderHero(idx + 1));
  dots.forEach((dot, i) => dot.addEventListener('click', () => renderHero(i)));
  setInterval(() => renderHero(idx + 1), 5000);
});
</script>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
