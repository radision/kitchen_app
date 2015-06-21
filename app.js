
function init_error() {
    $('#show_error').addClass('hide');
}

function show_error(msg) {
    var label = $('#show_error').find('label')[0];
    $(label).html(msg);
    $('#show_error').removeClass('hide');
}

function clean_error_class(obj) {
    obj.removeClass('has-error');
}

function add_error_class(obj) {
    obj.addClass('has-error');
}


