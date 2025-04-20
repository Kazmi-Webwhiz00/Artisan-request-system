jQuery(document).ready(function ($) {
  $(".artisan-meta-group")
    .first()
    .addClass("open")
    .find(".artisan-meta-group-content")
    .show();
  $(".artisan-meta-group-title").on("click", function () {
    $(this).next(".artisan-meta-group-content").slideToggle();
  });

  // --- TAB LOGIC ---
  const tabs = $(".kw-artisan-nav-tab");
  const panes = $(".kw-artisan-tab-pane");

  function activateTab(tabKey) {
    tabs.removeClass("kw-artisan-nav-tab-active");
    panes.hide();

    $(`.kw-artisan-nav-tab[data-tab="${tabKey}"]`).addClass(
      "kw-artisan-nav-tab-active"
    );
    $(`#kw-artisan-${tabKey}`).show();
  }

  tabs.on("click", function (e) {
    e.preventDefault();
    const key = $(this).data("tab");
    activateTab(key);
    history.pushState(null, "", `#${key}`);
  });

  const initial = window.location.hash.substring(1) || "general";
  activateTab(initial);

  // --- COPY TO CLIPBOARD LOGIC ---
  $(document).on("click", ".copy-button", function () {
    var $box = $(this).closest(".shortcode-box");
    var shortcode = $box.find(".shortcode-text").text().trim();

    // Create a temporary textarea, copy, then remove it
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val(shortcode).select();
    if (document.execCommand("copy")) {
      $box.find(".copy-message").fadeIn(200).delay(1000).fadeOut(200);
    }
    $temp.remove();
  });
});
