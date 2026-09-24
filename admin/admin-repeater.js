(function () {
  "use strict";

  function nextIndex(container) {
    var n = parseInt(container.getAttribute("data-next-index") || "0", 10);
    container.setAttribute("data-next-index", String(n + 1));
    return n;
  }

  document.addEventListener("click", function (e) {
    var addBtn = e.target.closest("[data-repeater-add]");
    if (addBtn) {
      var name = addBtn.getAttribute("data-repeater-add");
      var list = document.querySelector('[data-repeater-list="' + name + '"]');
      var tpl = document.querySelector('template[data-repeater-template="' + name + '"]');
      if (!list || !tpl) return;
      var idx = nextIndex(list);
      var html = tpl.innerHTML.split("__INDEX__").join(String(idx));
      var wrap = document.createElement("div");
      wrap.innerHTML = html.trim();
      if (wrap.firstElementChild) list.appendChild(wrap.firstElementChild);
      return;
    }
    var removeBtn = e.target.closest(".repeater-remove");
    if (removeBtn) {
      var row = removeBtn.closest(".repeater-row");
      if (row) row.remove();
    }
  });
})();
