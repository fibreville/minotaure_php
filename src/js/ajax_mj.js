$(document).ready(function() {
    var poll_results = function() {
        $.ajax({
            url: "ajax.php?role=heartbeat",
            type: 'get',
            dataType: 'JSON',
            success: function(response){
                if (response == true) {
                    $.ajax({
                        url: "ajax.php?role=mj",
                        context: document.body,
                        dataType : 'html',
                        success: function(html, code){
                            $('#poll_results').html(html);
                        }
                    });
                }
            }
        });
    };

    var interval = 5000;
    setInterval(poll_results, interval);
});
