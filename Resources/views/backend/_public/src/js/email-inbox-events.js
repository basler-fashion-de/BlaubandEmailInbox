$(function () {
    registerEventTabSelect();

    registerButtons();
    registerAssignButtons();
    registerFilterInputs();
});

function registerEventTabSelect(){

    if(Cookies.get('blauband_selected_tab')){
        $('div[class^="mail--list-item--state--"][data-mail-id='+Cookies.get('blauband_selected_tab')+']').find('a').click();
    }
    $('div[class^="mail--list-item--state--"]').on('click', function () {
        Cookies.set('blauband_selected_tab', $(this).data('mail-id'));
    });

    $('.mail--list-item--state--todo').on('click', function () {
        var me = this;
        var url = $(this).data('mail-url');
        sendAjax(url, [], function (response) {
            if (response.responseJSON.success) {
                $(me).addClass('selected');
                $(me).find('.state-icon.ui-icon')
                    .removeClass('ui-icon-notice')
                    .addClass('ui-icon-check')
            }
        })
    });
}

function registerButtons() {
    $('*[data-url]').on('click', function () {
        location.href = $(this).data('url')
    });

    $(plugin_selector + ' #save-button').on('click', function () {
        var url = $(this).data('ajax-url');
        var formData = fetchInboxInputFormData();

        sendAjax(url, formData, function (response) {
            if (response.responseJSON.success) {
                showInfoPanel(successSaveMessage)
            } else {
                showErrorPanel(response.responseJSON.data.message)
            }
        })
    });

    $(plugin_selector + ' #delete-button').on('click', function () {
        var url = $(this).data('ajax-url');
        var deleteId = $(plugin_selector + ' #connectionSelect :selected').val();
        var formData = new FormData();
        formData.append('deleteId', deleteId);

        sendAjax(url, formData, function (response) {
            if (response.responseJSON.success) {
                showInfoPanel(successDeleteMessage);
                $(plugin_selector + ' #connectionSelect :selected').remove();
                $(plugin_selector + ' #connectionSelect').selectmenu("destroy").selectmenu({ style: "dropdown" });
            } else {
                showErrorPanel(response.responseJSON.data.message)
            }
        })
    });

    $(plugin_selector + ' #edit-button').on('click', function () {
        var url = $(this).data('edit-url');
        var editId = $(plugin_selector + ' #connectionSelect :selected').val();
        location.href = url + '/id/' + editId
    });

    $(plugin_selector + ' [data-state-change-url]').on('click', function () {
        var url = $(this).data('state-change-url');

        sendAjax(url, [], function (response) {
            if (response.responseJSON.success) {
                location.reload();
            } else {
                showErrorPanel(response.responseJSON.data.message)
            }
        })
    });

    $(plugin_selector + ' .reply-mail--button').on('click', function () {
        var mailId = $(this).data('mail-id');
    })
}

function registerAssignButtons() {
    $('.select2').each(function () {
        $(this).select2(
            {
                ajax: {
                    url: $(this).data('url'),
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            term: params.term,
                            page: params.page || 1
                        }

                        return query
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.data.result,
                            pagination: {
                                more: (params.page * 30) < data.data.total
                            }
                        }
                    },
                },
                placeholder: searchMessage,
                minimumInputLength: 3,
            }
        )

        $(this).on('select2:select', function (e) {
            var url = $(this).data('save-url');
            var mailId = $(this).data('mail-id');
            var selectValue = $(this).val();

            var formData = new FormData();
            formData.append('mailId', mailId);
            formData.append('valueId', selectValue);

            $(this).prop('disabled', true);

            sendAjax(url, formData, function (response) {
                $(this).prop('disabled', false);

                if (response.responseJSON.success) {
                    location.reload();
                } else {
                    showErrorPanel(response.responseJSON.data.message)
                }
            })
        });
    });

    $(plugin_selector + ' .delete-relationship').on('click', function () {
        var url = $(this).data('ajax-url');
        var mailId = $(this).data('mail-id');

        var formData = new FormData();
        formData.append('mailId', mailId);

        sendAjax(url, formData, function (response) {
            if (response.responseJSON.success) {
                location.reload();
            } else {
                showErrorPanel(response.responseJSON.data.message)
            }
        })
    });

}

function registerFilterInputs() {
    $(plugin_selector + ' .filter-input').on('selectmenuchange', registerFilterInputsCallback);
    $(plugin_selector + ' .filter-input').on('change', registerFilterInputsCallback);
}

function registerFilterInputsCallback() {
    var url = baseUrl;

    $(plugin_selector + ' .filter-input').each(function (index, filter) {
        if ($(filter).val()) {
            if ($(filter).attr('type') === 'checkbox') {
                url += $(filter).attr('id') + '/' + $(filter).is(':checked') + '/';
            } else {
                url += $(filter).attr('id') + '/' + $(filter).val() + '/';
            }
        }
    });

    location.href = url;
}

function fetchInboxInputFormData() {
    var params = $('input, textarea, select');
    var formData = new FormData();
    var input = {};

    for (var i = 0; i < params.length; i++) {
        if (params[i].name && params[i].value) {
            var obj = {};
            var key = params[i].name;
            obj[key] = params[i].value;
            Object.assign(input, obj)
        }

        if (params[i].name && params[i].checked) {
            var obj = {}
            var key = params[i].name
            obj[key] = true
            Object.assign(input, obj)
        }
    }

    for (var k in input) {
        if (input.hasOwnProperty(k)) {
            formData.append(k, input[k])
        }
    }

    return formData
}