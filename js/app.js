(function () {
  function normalize(str) {
    return (str || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .trim();
  }

  function parseMaybeDate(value) {
    if (!value) return null;
    var normalized = value.toString().trim();
    if (!normalized) return null;
    var timestamp = Date.parse(normalized);
    return isNaN(timestamp) ? null : timestamp;
  }

  function parseMaybeNumber(value) {
    if (value === null || value === undefined) return null;
    var normalized = value.toString().replace(',', '.').trim();
    if (!normalized) return null;
    var n = Number(normalized);
    return isNaN(n) ? null : n;
  }

  function cleanFlashParamsFromUrl() {
    try {
      var url = new URL(window.location.href);
      var changed = false;
      ['msg', 'erro', 'warning', 'info', 'success'].forEach(function (key) {
        if (url.searchParams.has(key)) {
          url.searchParams.delete(key);
          changed = true;
        }
      });
      if (changed) {
        history.replaceState({}, document.title, url.pathname + (url.search ? url.search : '') + url.hash);
      }
    } catch (e) {}
  }

  function buildToast(title, body, variant) {
    var container = document.getElementById('appToastContainer');
    if (!container || !body) return;

    var map = {
      success: { icon: 'fa-check-circle', label: 'Sucesso' },
      danger: { icon: 'fa-exclamation-circle', label: 'Aviso' },
      warning: { icon: 'fa-exclamation-triangle', label: 'Atenção' },
      info: { icon: 'fa-info-circle', label: 'Informação' }
    };

    var meta = map[variant] || map.info;
    var wrapper = document.createElement('div');
    wrapper.className = 'toast app-toast text-bg-light border-0';
    wrapper.setAttribute('role', 'alert');
    wrapper.setAttribute('aria-live', 'assertive');
    wrapper.setAttribute('aria-atomic', 'true');
    wrapper.setAttribute('data-bs-autohide', 'true');
    wrapper.setAttribute('data-bs-delay', variant === 'danger' ? '6500' : '4500');

    wrapper.innerHTML =
      '<div class="toast-header app-toast-header app-toast-' + variant + '">' +
        '<span class="app-toast-icon"><i class="fas ' + meta.icon + '"></i></span>' +
        '<strong class="me-auto">' + (title || meta.label) + '</strong>' +
        '<small>agora</small>' +
        '<button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Fechar"></button>' +
      '</div>' +
      '<div class="toast-body">' + body + '</div>';

    container.appendChild(wrapper);
    var toast = new bootstrap.Toast(wrapper);
    toast.show();
    wrapper.addEventListener('hidden.bs.toast', function () {
      wrapper.remove();
    });
  }

  function convertAlertsToToasts() {
    var alerts = document.querySelectorAll('.alert');
    if (!alerts.length) return;

    alerts.forEach(function (alert) {
      if (alert.closest('.modal')) return;
      if (alert.dataset.toastified === '1') return;

      var variant = 'info';
      if (alert.classList.contains('alert-danger')) variant = 'danger';
      else if (alert.classList.contains('alert-success')) variant = 'success';
      else if (alert.classList.contains('alert-warning')) variant = 'warning';

      buildToast('', alert.innerHTML, variant);
      alert.dataset.toastified = '1';
      alert.remove();
    });

    cleanFlashParamsFromUrl();
  }

  function createPaginationShell(table) {
    var wrapper = table.closest('.table-responsive') || table.parentElement;
    if (!wrapper) return null;

    var shell = document.createElement('div');
    shell.className = 'table-pagination-shell';
    shell.innerHTML =
      '<div class="table-pagination-summary text-muted small"></div>' +
      '<div class="table-pagination-controls">' +
        '<label class="table-page-size">' +
          '<span>Por página</span>' +
          '<select class="form-select form-select-sm">' +
            '<option value="5">5</option>' +
            '<option value="10" selected>10</option>' +
            '<option value="20">20</option>' +
            '<option value="50">50</option>' +
          '</select>' +
        '</label>' +
        '<div class="table-pagination-buttons">' +
          '<button type="button" class="btn btn-light btn-sm" data-page="prev"><i class="fas fa-chevron-left"></i></button>' +
          '<span class="table-pagination-current small fw-semibold">Página 1</span>' +
          '<button type="button" class="btn btn-light btn-sm" data-page="next"><i class="fas fa-chevron-right"></i></button>' +
        '</div>' +
      '</div>';

    wrapper.insertAdjacentElement('afterend', shell);
    return {
      shell: shell,
      summary: shell.querySelector('.table-pagination-summary'),
      current: shell.querySelector('.table-pagination-current'),
      pageSize: shell.querySelector('select'),
      prev: shell.querySelector('[data-page="prev"]'),
      next: shell.querySelector('[data-page="next"]')
    };
  }

  function decorateSearchInput(input) {
    if (!input || input.dataset.enhanced === '1') return;
    var wrapper = input.closest('.filter-search');
    if (!wrapper) return;

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'filter-search-clear';
    button.setAttribute('aria-label', 'Limpar pesquisa');
    button.innerHTML = '<i class="fas fa-times"></i>';
    button.hidden = !input.value;

    button.addEventListener('click', function () {
      input.value = '';
      button.hidden = true;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.focus();
    });

    input.addEventListener('input', function () {
      button.hidden = !input.value;
    });

    wrapper.appendChild(button);
    input.dataset.enhanced = '1';
  }

  function wireTableFilters() {
    document.querySelectorAll('[data-table-filter]').forEach(function (input) {
      var tableId = input.getAttribute('data-table-filter');
      var table = document.getElementById(tableId);
      if (!table) return;

      input.addEventListener('input', function () {
        var q = normalize(input.value);
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (tr) {
          var text = normalize(tr.innerText);
          tr.style.display = text.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    });

    document.querySelectorAll('[data-advanced-filter-form]').forEach(function (form) {
      var tableId = form.getAttribute('data-advanced-filter-form');
      var table = document.getElementById(tableId);
      if (!table) return;
      var tbody = table.querySelector('tbody');
      if (!tbody) return;

      var emptyStateId = form.getAttribute('data-empty-state');
      var emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;
      var counterId = form.getAttribute('data-results-count');
      var counter = counterId ? document.getElementById(counterId) : null;
      var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
      var searchControls = Array.prototype.slice.call(form.querySelectorAll('[data-global-search]'));
      var filterControls = Array.prototype.slice.call(form.querySelectorAll('[data-filter-key]'));
      var sortControls = Array.prototype.slice.call(form.querySelectorAll('[data-sort-key]'));
      var pager = createPaginationShell(table);
      var currentPage = 1;

      searchControls.forEach(decorateSearchInput);

      function matchesFilter(row, control) {
        var key = control.getAttribute('data-filter-key');
        if (!key) return true;
        var mode = control.getAttribute('data-filter-mode') || 'exact';
        var rawValue = control.value;
        if (!rawValue || rawValue === 'all' || rawValue === 'default') return true;

        var rowValue = row.getAttribute('data-' + key) || '';
        var rowText = normalize(rowValue);
        var filterText = normalize(rawValue);

        if (mode === 'includes') return rowText.indexOf(filterText) !== -1;

        if (mode === 'presence') {
          if (filterText === 'with') return rowText !== '';
          if (filterText === 'without') return rowText === '';
          return true;
        }

        if (mode === 'date-min') {
          var rowDateMin = parseMaybeDate(rowValue);
          var filterDateMin = parseMaybeDate(rawValue);
          if (filterDateMin === null) return true;
          if (rowDateMin === null) return false;
          return rowDateMin >= filterDateMin;
        }

        if (mode === 'date-max') {
          var rowDateMax = parseMaybeDate(rowValue);
          var filterDateMax = parseMaybeDate(rawValue);
          if (filterDateMax === null) return true;
          if (rowDateMax === null) return false;
          return rowDateMax <= filterDateMax + 86399999;
        }

        if (mode === 'number-min') {
          var rowNumberMin = parseMaybeNumber(rowValue);
          var filterNumberMin = parseMaybeNumber(rawValue);
          if (filterNumberMin === null) return true;
          if (rowNumberMin === null) return false;
          return rowNumberMin >= filterNumberMin;
        }

        if (mode === 'number-max') {
          var rowNumberMax = parseMaybeNumber(rowValue);
          var filterNumberMax = parseMaybeNumber(rawValue);
          if (filterNumberMax === null) return true;
          if (rowNumberMax === null) return false;
          return rowNumberMax <= filterNumberMax;
        }

        return rowText === filterText;
      }

      function compareRows(rowA, rowB, control) {
        var sortKey = control.getAttribute('data-sort-key');
        var sortType = control.getAttribute('data-sort-type') || 'text';
        var direction = control.value;
        if (!sortKey || !direction || direction === 'default') return 0;

        var valueA = rowA.getAttribute('data-' + sortKey) || '';
        var valueB = rowB.getAttribute('data-' + sortKey) || '';
        var compare = 0;

        if (sortType === 'number') {
          var numberA = parseMaybeNumber(valueA);
          var numberB = parseMaybeNumber(valueB);
          compare = (numberA === null ? -Infinity : numberA) - (numberB === null ? -Infinity : numberB);
        } else if (sortType === 'date') {
          var dateA = parseMaybeDate(valueA);
          var dateB = parseMaybeDate(valueB);
          compare = (dateA === null ? 0 : dateA) - (dateB === null ? 0 : dateB);
        } else {
          compare = normalize(valueA).localeCompare(normalize(valueB), 'pt');
        }

        return direction === 'desc' ? compare * -1 : compare;
      }

      function sortRows(visibleRows) {
        var activeSorts = sortControls.filter(function (control) {
          return control.value && control.value !== 'default';
        });
        if (!activeSorts.length) return visibleRows;

        return visibleRows.slice().sort(function (rowA, rowB) {
          for (var i = 0; i < activeSorts.length; i += 1) {
            var result = compareRows(rowA, rowB, activeSorts[i]);
            if (result !== 0) return result;
          }
          return 0;
        });
      }

      function renderPage(visibleRows) {
        rows.forEach(function (row) {
          row.style.display = 'none';
        });

        var pageSize = pager ? parseInt(pager.pageSize.value, 10) : (visibleRows.length || 1);
        var totalPages = Math.max(1, Math.ceil(visibleRows.length / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;

        var start = (currentPage - 1) * pageSize;
        var end = start + pageSize;
        var pageRows = visibleRows.slice(start, end);

        visibleRows.forEach(function (row) {
          tbody.appendChild(row);
        });

        pageRows.forEach(function (row) {
          row.style.display = '';
        });

        if (pager) {
          pager.summary.textContent = visibleRows.length
            ? 'A mostrar ' + (start + 1) + '–' + Math.min(end, visibleRows.length) + ' de ' + visibleRows.length + ' registo(s)'
            : 'Nenhum registo encontrado';
          pager.current.textContent = 'Página ' + currentPage + ' de ' + totalPages;
          pager.prev.disabled = currentPage <= 1;
          pager.next.disabled = currentPage >= totalPages || visibleRows.length === 0;
          pager.shell.classList.toggle('d-none', visibleRows.length <= pageSize && visibleRows.length !== 0);
        }
      }

      function applyFilters(resetPage) {
        if (resetPage !== false) currentPage = 1;

        var visibleRows = rows.filter(function (row) {
          for (var i = 0; i < filterControls.length; i += 1) {
            if (!matchesFilter(row, filterControls[i])) return false;
          }

          for (var j = 0; j < searchControls.length; j += 1) {
            var searchValue = normalize(searchControls[j].value);
            if (searchValue) {
              var haystack = normalize(row.getAttribute('data-search') || row.innerText);
              if (haystack.indexOf(searchValue) === -1) return false;
            }
          }

          return true;
        });

        visibleRows = sortRows(visibleRows);
        renderPage(visibleRows);

        if (emptyState) emptyState.classList.toggle('d-none', visibleRows.length !== 0);
        if (counter) counter.textContent = visibleRows.length;
      }

      form.addEventListener('input', function () {
        applyFilters(true);
      });
      form.addEventListener('change', function () {
        applyFilters(true);
      });

      var resetButton = form.querySelector('[data-filter-reset]');
      if (resetButton) {
        resetButton.addEventListener('click', function () {
          form.reset();
          sortControls.forEach(function (control) {
            if (control.querySelector('option[selected]')) {
              control.value = control.querySelector('option[selected]').value;
            }
          });
          currentPage = 1;
          applyFilters(true);
        });
      }

      if (pager) {
        pager.pageSize.addEventListener('change', function () {
          currentPage = 1;
          applyFilters(false);
        });
        pager.prev.addEventListener('click', function () {
          if (currentPage > 1) {
            currentPage -= 1;
            applyFilters(false);
          }
        });
        pager.next.addEventListener('click', function () {
          currentPage += 1;
          applyFilters(false);
        });
      }

      applyFilters(true);
    });
  }

  function animateDashboard() {
    var animated = false;

    function countTo(element) {
      var target = parseFloat(element.getAttribute('data-count-to') || '0');
      var duration = 900;
      var prefix = element.getAttribute('data-prefix') || '';
      var suffix = element.getAttribute('data-suffix') || '';
      var decimals = parseInt(element.getAttribute('data-decimals') || '0', 10);
      var startedAt = null;

      function step(timestamp) {
        if (!startedAt) startedAt = timestamp;
        var progress = Math.min((timestamp - startedAt) / duration, 1);
        var value = target * (1 - Math.pow(1 - progress, 3));
        element.textContent = prefix + value.toLocaleString('pt-PT', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        }) + suffix;
        if (progress < 1) window.requestAnimationFrame(step);
      }

      window.requestAnimationFrame(step);
    }

    function start() {
      if (animated) return;
      animated = true;

      document.querySelectorAll('[data-count-to]').forEach(countTo);
      document.querySelectorAll('[data-bar-width]').forEach(function (bar) {
        bar.style.width = bar.getAttribute('data-bar-width') + '%';
      });
      document.querySelectorAll('.dashboard-reveal').forEach(function (card, index) {
        setTimeout(function () {
          card.classList.add('is-visible');
        }, index * 90);
      });
    }

    var trigger = document.querySelector('[data-dashboard-animate]');
    if (!trigger) return;

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        if (entries.some(function (entry) { return entry.isIntersecting; })) {
          start();
          observer.disconnect();
        }
      }, { threshold: 0.18 });
      observer.observe(trigger);
    } else {
      start();
    }
  }

  function enhanceModalAccessibility() {
    document.querySelectorAll('.modal').forEach(function (modal) {
      modal.addEventListener('shown.bs.modal', function () {
        var firstField = modal.querySelector('input:not([type="hidden"]), select, textarea, button');
        if (firstField) firstField.focus();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    convertAlertsToToasts();
    wireTableFilters();
    enhanceModalAccessibility();
    animateDashboard();
  });
})();
