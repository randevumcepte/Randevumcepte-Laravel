{{-- Çoklu şube seçici (çark / sadakat ödülü / bildirim reklamı için).
     Gerekli değişkenler: $yetkiliolunanisletmeler (salon_id dizisi), $isletme (mevcut şube).
     Tek şubeli işletmede hiçbir şey render edilmez. Her sayfada bir kez include edilir.
     JS: window.ssSeciliIdler(root) -> seçili salon_id string dizisi.
     Bildirim formunda checkbox name="salon_ids[]" olduğundan serializeArray otomatik toplar. --}}
@php
    $__subeler = \App\Salonlar::whereIn('id', $yetkiliolunanisletmeler ?? [])->get(['id', 'salon_adi']);
    $__aktifSube = isset($isletme) ? (string) $isletme->id : '';
@endphp
@if($__subeler->count() > 1)
<div class="sube-secici" data-aktif="{{ $__aktifSube }}"
     style="margin:12px 0;padding:12px 14px;border:1px solid #e6e0f5;border-radius:10px;background:#faf8ff;">
    <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:#4b3b7a;">
        <i class="fa fa-store"></i> Hangi şubelere uygulansın?
    </div>
    <label style="display:inline-flex;align-items:center;margin:0 14px 6px 0;font-size:13px;cursor:pointer;">
        <input type="checkbox" class="ss-tumu" style="margin-right:5px;"> <b>Tümü</b>
    </label>
    @foreach($__subeler as $sube)
        <label style="display:inline-flex;align-items:center;margin:0 14px 6px 0;font-size:13px;cursor:pointer;">
            <input type="checkbox" class="ss-sube" name="salon_ids[]" value="{{ $sube->id }}"
                   {{ (string) $sube->id === $__aktifSube ? 'checked' : '' }} style="margin-right:5px;">
            {{ $sube->salon_adi }}
        </label>
    @endforeach
</div>
<script>
(function () {
    window.ssSeciliIdler = function (root) {
        root = root || document;
        return Array.prototype.slice.call(root.querySelectorAll('.ss-sube:checked'))
            .map(function (x) { return x.value; });
    };
    function wire() {
        var kutular = document.querySelectorAll('.sube-secici');
        Array.prototype.forEach.call(kutular, function (box) {
            if (box.__ssWired) return;
            box.__ssWired = true;
            var tumu = box.querySelector('.ss-tumu');
            var subeler = box.querySelectorAll('.ss-sube');
            var aktif = box.getAttribute('data-aktif');
            function syncTumu() {
                var all = subeler.length > 0;
                Array.prototype.forEach.call(subeler, function (s) { if (!s.checked) all = false; });
                if (tumu) tumu.checked = all;
            }
            if (tumu) {
                tumu.addEventListener('change', function () {
                    Array.prototype.forEach.call(subeler, function (s) { s.checked = tumu.checked; });
                    if (!tumu.checked) {
                        // En az bir şube kalmalı → aktif olanı seçili bırak.
                        Array.prototype.forEach.call(subeler, function (s) { if (s.value === aktif) s.checked = true; });
                    }
                    syncTumu();
                });
            }
            Array.prototype.forEach.call(subeler, function (s) {
                s.addEventListener('change', function () {
                    var any = false;
                    Array.prototype.forEach.call(subeler, function (x) { if (x.checked) any = true; });
                    if (!any) s.checked = true; // en az bir seçili kalsın
                    syncTumu();
                });
            });
            syncTumu();
        });
    }
    if (document.readyState !== 'loading') wire();
    else document.addEventListener('DOMContentLoaded', wire);
})();
</script>
@endif
