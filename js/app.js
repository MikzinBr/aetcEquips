// Simple Bootstrap-friendly table filter
(function () {
  function normalize(str) {
    return (str || '').toString().toLowerCase().trim();
  }

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
})();
