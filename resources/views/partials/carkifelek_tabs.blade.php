@php
   // Hangi tab aktif — pageindex'e gore belirlenir
   $_aktifTab = isset($pageindex) ? $pageindex : 500;
   $_subeQs = isset($_GET['sube']) && isset($isletme) ? '?sube='.$isletme->id : '';
@endphp

<style>
   .ck-tabs {
      display: flex; gap: 6px; flex-wrap: wrap;
      background: #fff; border-radius: 14px;
      padding: 6px; margin: 0 0 18px;
      box-shadow: 0 4px 18px rgba(92, 0, 142, .08);
      border: 1px solid #ece6f3;
   }
   .ck-tabs a {
      flex: 1 1 auto; min-width: 140px;
      text-align: center; text-decoration: none !important;
      padding: 11px 16px; border-radius: 10px;
      font-weight: 600; font-size: 13.5px; color: #5a4f78;
      transition: background .15s, color .15s, transform .15s;
      display: flex; align-items: center; justify-content: center; gap: 7px;
      white-space: nowrap;
   }
   .ck-tabs a i { font-size: 15px; opacity: .85; }
   .ck-tabs a:hover { background: #faf5ff; color: #5C008E; }
   .ck-tabs a.active {
      background: linear-gradient(135deg, #5C008E 0%, #7B2FB8 50%, #9D5DC8 100%);
      color: #fff !important;
      box-shadow: 0 6px 16px rgba(92, 0, 142, .28);
   }
   .ck-tabs a.active i { opacity: 1; }
   @media (max-width: 600px) {
      .ck-tabs a { font-size: 12.5px; padding: 9px 12px; min-width: 110px; }
   }
</style>

<div class="ck-tabs">
   <a href="/isletmeyonetim/carkifelek{{ $_subeQs }}" class="{{ $_aktifTab == 500 ? 'active' : '' }}">
      <i class="fa fa-circle-o-notch"></i> Çarkıfelek
   </a>
   <a href="/isletmeyonetim/carkkazananlar{{ $_subeQs }}" class="{{ $_aktifTab == 501 ? 'active' : '' }}">
      <i class="fa fa-trophy"></i> Çark Kazananlar
   </a>
   <a href="/isletmeyonetim/puanodulleri{{ $_subeQs }}" class="{{ $_aktifTab == 502 ? 'active' : '' }}">
      <i class="fa fa-star"></i> Puan Ödülleri
   </a>
</div>
