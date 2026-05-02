@php $__active = $active ?? ''; @endphp
<div class="modern-nav-pills">
    <a href="/" class="nav-pill-item {{ $__active === 'anasayfa' ? 'active' : '' }}">
        <i class="fa fa-home"></i>
        <span>Anasayfa</span>
    </a>
    <a href="/profilim" class="nav-pill-item {{ $__active === 'profilim' ? 'active' : '' }}">
        <i class="fa fa-user"></i>
        <span>Profilim</span>
    </a>
    <a href="/randevularim" class="nav-pill-item {{ $__active === 'randevularim' ? 'active' : '' }}">
        <i class="fa fa-calendar"></i>
        <span>Randevularım</span>
    </a>
    <a href="/sadakat" class="nav-pill-item {{ $__active === 'sadakat' ? 'active' : '' }}">
        <i class="fa fa-star"></i>
        <span>Sadakat</span>
    </a>
    <a href="/ayarlarim" class="nav-pill-item {{ $__active === 'ayarlarim' ? 'active' : '' }}">
        <i class="fa fa-lock"></i>
        <span>Şifrem</span>
    </a>
</div>
