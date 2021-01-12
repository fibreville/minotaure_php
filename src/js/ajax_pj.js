var update_screen = function() {
    if ($('input:checked').length == 0) {
        $.ajax({
            url: "ajax.php?role=heartbeat",
            type: 'get',
            dataType: 'JSON',
            success: function(response){
                if (response == true) {
                    $('#loader').addClass("active");
                    $.ajax({
                        url: "ajax.php?role=pj",
                        context: document.body,
                        dataType : 'html',
                        success: function(html, code){
                            $('#character-wrapper').html(html);
                            $('#loader').removeClass("active");
                        }
                    });
                }
            }
        });
    }
};

update_screen();
var interval = 4000;
setInterval(update_screen, interval);