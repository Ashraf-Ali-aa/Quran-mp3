function RunAjaxJS(insertelement, data) {
    var milisec = new Date;
    var jsfound = false;
    milisec = milisec.getTime();
    var js_reg = /<script.*?>(.|[\r\n])*?<\/script>/ig;
    var js_str = js_reg.exec(data);
    if (js_str != null) {
        var js_arr = new Array(js_str.shift());
        var jsfound = true;
        while (js_str) {
            js_str = js_reg.exec(data);
            if (js_str != null) js_arr.push(js_str.shift());
        }
        for (var i = 0; i < js_arr.length; i++) {
            data = data.replace(js_arr[i], '<span id="' + milisec + i + '" style="display:none;"></span>');
        }
    }
    $("#" + insertelement).html(data);
    if (jsfound) {
        var js_content_reg = /<script.*?>((.|[\r\n])*?)<\/script>/ig;
        for (i = 0; i < js_arr.length; i++) {
            var mark_node = document.getElementById(milisec + '' + i);
            var mark_parent_node = mark_node.parentNode;
            mark_parent_node.removeChild(mark_node);
            js_content_reg.lastIndex = 0;
            var js_content = js_content_reg.exec(js_arr[i]);
            var script_node = mark_parent_node.appendChild(document.createElement('script'));
            script_node.text = js_content[1];
            var script_params_str = js_arr[i].substring(js_arr[i].indexOf(' ', 0), js_arr[i].indexOf('>', 0));
            var params_arr = script_params_str.split(' ');
            if (params_arr.length > 1) {
                for (var j = 0; j < params_arr.length; j++) {
                    if (params_arr[j].length > 0) {
                        var param_arr = params_arr[j].split('=');
                        param_arr[1] = param_arr[1].substr(1, (param_arr[1].length - 2));
                        script_node.setAttribute(param_arr[0], param_arr[1]);
                    }
                }
            }
        }
    }
};

jQuery(function ($) {
    $('#upload_audio_button').click(function () {
        $('#addaudios').modal('show');
    });
    $('#edit-audio').ajaxForm({
        success: function () {
            alert('Your audio was edited!');
            loader("manage_audio");
            $('#edit_audio').modal('hide');
        }
    });
    $('#add_audio').ajaxForm({
        beforeSend: function () {
            $('#status').empty();
            var percentVal = '0%';
            $('.barer').width(percentVal)
            $('.percenter').html(percentVal);
        },
        uploadProgress: function (event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            $('.barer').width(percentVal)
            $('.percenter').html(percentVal);
        },
        complete: function (xhr) {
            $('#status').html(xhr.responseText);
        },
        success: function () {
            alert('Your audio was added!');
            $('#addaudios').modal('hide');
            $('#add_audio')[0].reset();
        }
    });
});

$(document).ready(function () {
    $('#filetree').fileTree({
        root: '',
        script: '/admin/ajax.php?t=templates',
        folderEvent: 'click',
        expandSpeed: 750,
        collapseSpeed: 750,
        multiFolder: false
    }, function (file) {
        $.post('/admin/ajax.php?t=templates', {
            action: "load",
            file: file
        }, function (data) {
            RunAjaxJS('fileedit', data);
        });
    });
    $('.auto_artist').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "/admin/ajax.php?t=typeahead&action=artist",
                dataType: "json",
                type: "POST",
                data: {
                    query: query
                },
                success: function (data) {
                    var return_list = [],
                        i = data.length;
                    while (i--) {
                        return_list[i] = {
                            id: data[i].id,
                            value: removenull(data[i].label)
                        };
                    }
                    typeahead.process(return_list);
                }
            });
        },
        onselect: function (obj) {
            $('[name="artist_id"]').val(obj.id);
        }
    });
    $('.auto_album').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "/admin/ajax.php?t=typeahead&action=album",
                dataType: "json",
                type: "POST",
                data: {
                    query: query
                },
                success: function (data) {
                    var return_list = [],
                        i = data.length;
                    while (i--) {
                        return_list[i] = {
                            id: data[i].id,
                            value: removenull(data[i].label)
                        };
                    }
                    typeahead.process(return_list);
                }
            });
        },
        onselect: function (obj) {
            $('[name="album_id"]').val(obj.id);
        }
    });
    function removenull(str) {
        var new_str = str;
        if (str == '') {
            new_str = str.replace('', "N/A");
        } else if (str == null) {
            new_str = "N/A";
        }
        return new_str;
	};
});

function savefile(file) {
    var content = editor.getCode();
    $.post('/admin/ajax.php?t=templates', {
        action: "save",
        file: file,
        content: content
    }, function (data) {
        if (data == "ok") {
            alert("Edited");
        } else {
            alert("Can't edit");
        }
    });
};


    function removenull2(str) {
        var new_str = str;
        if (str == '') {
            new_str = str.replace('', "N/A");
        } else if (str == null) {
            new_str = "N/A";
        }
        return new_str;
	};

function auto_artist(rand) {
    $('.auto_artist').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "/admin/ajax.php?t=typeahead&action=artist",
                dataType: "json",
                type: "POST",
                data: {
                    query: query
                },
                success: function (data) {
                    var return_list = [],
                        i = data.length;
                    while (i--) {
                        return_list[i] = {
                            id: data[i].id,
                            value: removenull2(data[i].label)
                        };
                    }
                    typeahead.process(return_list);
                }
            });
        },
        onselect: function (obj) {
            $('#artist_form_'+rand).val(obj.id);
        }
    });
}
function auto_album(rand) {
    $('.auto_album').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "/admin/ajax.php?t=typeahead&action=album",
                dataType: "json",
                type: "POST",
                data: {
                    query: query
                },
                success: function (data) {
                    var return_list = [],
                        i = data.length;
                    while (i--) {
                        return_list[i] = {
                            id: data[i].id,
                            value: removenull2(data[i].label)
                        };
                    }
                    typeahead.process(return_list);
                }
            });
        },
        onselect: function (obj) {
            $('[name="album_id_'+rand+'"]').val(obj.id);
        }
    });
}