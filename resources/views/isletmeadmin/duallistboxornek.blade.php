<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Müşteri Seçimi ve Arama</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    #customerList > div {
      margin-bottom: 5px;
    }
    .loading {
      margin-top: 10px;
      font-style: italic;
      color: #888;
    }
  </style>
</head>
<body>

  <h2>Müşteri Listesi</h2>

  <input type="text" id="searchInput" placeholder="Müşteri ara..." style="margin-bottom: 10px; padding: 5px; width: 300px;" />

  <button id="selectAllBtn">Tümünü Seç</button>
  <button id="deselectAllBtn">Tümünü Kaldır</button>

  <div id="customerList" style="margin-top: 15px;"></div>

  <div class="loading" style="display: none;">Yükleniyor...</div>

  <div id="selectedCount" style="margin-top: 20px; font-weight: bold;">
    0 müşteri seçildi
  </div>

  <!-- CSRF token -->
  <input type="hidden" name="_token" value="{{ csrf_token() }}">

  <script>
    let allSelected = false;
    let selectedIds = new Set();
    let unselectedIds = new Set();
    let totalCustomers = 0;
    let currentPage = 1;
    const perPage = 100;
    let loading = false;
    let searchTerm = '';

    function updateSelectedCount() {
      const count = allSelected
        ? totalCustomers - unselectedIds.size
        : selectedIds.size;

      $('#selectedCount').text(`${count} müşteri seçildi`);
    }

    function renderCustomers(customers) {
      customers.forEach(c => {
        const isSelected = allSelected
          ? !unselectedIds.has(c.user_id)
          : selectedIds.has(c.user_id);

        const checkbox = $('<input type="checkbox">')
          .prop('checked', isSelected)
          .on('change', function () {
            if (allSelected) {
              if (!this.checked) {
                unselectedIds.add(c.user_id);
              } else {
                unselectedIds.delete(c.user_id);
              }
            } else {
              if (this.checked) {
                selectedIds.add(c.user_id);
              } else {
                selectedIds.delete(c.user_id);
              }
            }
            updateSelectedCount();
          });

        const item = $('<div>').text(c.name || '(İsimsiz)').prepend(checkbox);
        $('#customerList').append(item);
      });
    }

    function loadCustomers(page = 1, append = true) {
      if (loading) return;
      loading = true;
      $('.loading').show();

      $.ajax({
        url: 'https://app.randevumcepte.com.tr/isletmeyonetim/musteriportfoydropliste',
        method: 'POST',
        data: {
          page: page,
          perPage: perPage,
          filtre: 0,
          search: searchTerm,
          _token: $('input[name="_token"]').val()
        },
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success: function (res) {
          totalCustomers = res.total;
          currentPage = page;

          if (!append) {
            $('#customerList').empty();
          }

          renderCustomers(res.customers);
          updateSelectedCount();
        },
        complete: function () {
          loading = false;
          $('.loading').hide();
        },
        error: function (xhr) {
          alert("Bir hata oluştu: " + xhr.statusText);
          loading = false;
        }
      });
    }

    // Debounce fonksiyonu (gereksiz ajaxları önlemek için)
    function debounce(func, wait) {
      let timeout;
      return function () {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
      };
    }

    $('#searchInput').on('input', debounce(function () {
      searchTerm = $(this).val().trim();
      currentPage = 1;
      allSelected = false;
      selectedIds.clear();
      unselectedIds.clear();
      loadCustomers(1, false);
    }, 400));

    $('#selectAllBtn').on('click', function () {
      allSelected = true;
      selectedIds.clear();
      unselectedIds.clear();

      $('#customerList input[type="checkbox"]').each(function () {
        $(this).prop('checked', true);
      });

      updateSelectedCount();
    });

    $('#deselectAllBtn').on('click', function () {
      allSelected = false;
      selectedIds.clear();
      unselectedIds.clear();

      $('#customerList input[type="checkbox"]').each(function () {
        $(this).prop('checked', false);
      });

      updateSelectedCount();
    });

    $(window).on('scroll', function () {
      if ($(window).scrollTop() + $(window).height() + 50 >= $(document).height()) {
        if ((currentPage * perPage) < totalCustomers) {
          currentPage++;
          loadCustomers(currentPage, true);
        }
      }
    });

    $(document).ready(function () {
      loadCustomers(1, false);
    });
  </script>

</body>
</html>