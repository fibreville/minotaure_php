$(document).ready(function() {
    var poll_results = function() {
        $.ajax({
            url: "ajax.php?role=mj",
            context: document.body,
            dataType : 'html',
            success: function(html, code){
                $('#poll_results').html(html);
            }
        });
    };

    var openTab = function(element) {
        $('#tabs > div').removeClass('active');
        $(element).addClass('active');
        $('#choices > div').removeClass('active');
        $('#' + $(element).data('target')).addClass('active');
    }

    var hash = window.location.hash;
    if (hash != '') {
        openTab($("div[data-target=epreuve]"));
    }

    var interval = 2000;
    setInterval(poll_results, interval);

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

});