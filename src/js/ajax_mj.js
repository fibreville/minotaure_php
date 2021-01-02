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

    var openTab = function(id) {
        $('#choices > div').hide();
        $(id).show();
    }

    var hash = window.location.hash;
    if (hash != '') {
        openTab(hash);
    }

    var interval = 2000;
    setInterval(poll_results, interval);

    $("#tabs div").click(function() {
        openTab('#' + $(this).data('target'));
    });
});