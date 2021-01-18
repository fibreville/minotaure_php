function setTheme (theme_name) {
  localStorage.setItem('theme_name', theme_name)
  document.documentElement.setAttribute('data-theme', theme_name)
}

if (localStorage.getItem('theme_name')) {
  setTheme(localStorage.getItem('theme_name'))
}

var init = false;

// Ecran MJ
$(document).ready(function() {
  if ($('#page-mj').length && !init) {
    $("#tabs div").click(function() {
      openTab($(this));
    });

    if (typeof data_failures !== 'undefined' && data_failures !== null) {
      data_failures.forEach(element => $('#'+element).addClass('looser'));
    }
    if (typeof data_wins !== 'undefined' && data_wins !== null) {
      data_wins.forEach(element => $('#'+element).addClass('winner'));
    }

    $("#delete-game").click(function() {
      $(this).hide();
      $("#delete-game-confirm").addClass('active');
    });

    var openTab = function(element) {
      $('#tabs > div').removeClass('active');
      $(element).addClass('active');
      $('#choices > div').removeClass('active');
      $('#' + $(element).data('target')).addClass('active');
    }

    var hash = window.location.hash;
    if (hash != '') {
      openTab($("div[data-target=" + hash.substr(1) + "]"));
    }
    else {
      openTab($("div[data-target=elections]"));
    }

    init = true;
  }

  // Empêche la soumission d'une nouvelle
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href.split(/[?#]/)[0])
  }
});
