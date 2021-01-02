var update_screen = function() {
    if ($('input:checked').length == 0) {
        $.ajax({
            url: "ajax.php?role=pj",
            context: document.body,
            dataType : 'html',
            success: function(html, code){
                $('#character-wrapper').html(html);
            }
        });
    }
};

update_screen();
var interval = 4000;
setInterval(update_screen, interval);