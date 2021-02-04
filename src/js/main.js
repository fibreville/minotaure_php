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
    var inputs = document.querySelectorAll('#tags input[type="text"]');
    for (i = 0; i < inputs.length; ++i) {
      if (default_tags_per_category) {
        inputs[i].setAttribute('readonly', 'readonly');
        inputs[i].setAttribute('disabled', 'disabled');
        tagify = new Tagify(inputs[i]);
        var tagsToAdd = default_tags_per_category[i+1];
        tagify.addTags(tagsToAdd)
      }
      else {
        tagify = new Tagify(inputs[i]);
      }
    }

    var inputs_tags = document.querySelectorAll('input.tag-whitelist');
    for (i = 0; i < inputs_tags.length; ++i) {
      if (typeof default_tags != 'undefined') {
        new Tagify(inputs_tags[i], {
          enforceWhitelist: true,
          whitelist: default_tags,
          dropdown: {
            closeOnSelect: true,
            enabled: 0,
            classname: 'users-list',
          },
        });
      }
    }

    var inputs_players = document.querySelectorAll('input.player-whitelist');
    for (i = 0; i < inputs_players.length; ++i) {
      if (typeof tags_players != 'undefined') {
        new Tagify(inputs_players[i], {
          enforceWhitelist: true,
          whitelist: tags_players,
          dropdown: {
            closeOnSelect: true,
            enabled: 0,
            classname: 'users-list',
          },
        });
      }
    }
  }

  // Empêche la soumission d'une nouvelle requête.
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href.split(/[?#]/)[0])
  }
});
