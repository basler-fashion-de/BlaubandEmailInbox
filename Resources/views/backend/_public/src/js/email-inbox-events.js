$(function () {
  registerButtons()
  registerSelect()
  registerAssignButtons()
})

function registerButtons () {
  $('*[data-url]').on('click', function () {
    location.href = $(this).data('url')
  })

  $(plugin_selector + ' #save-button').off()
  $(plugin_selector + ' #save-button').on('click', function () {
    var url = $(this).data('url')
    var formData = fetchInboxInputFormData()

    sendAjax(url, formData, function (response) {
      if (response.responseJSON.success) {
        alert(successSaveMessage)
        $(plugin_selector + ' #back-button').click()
      } else {
        showErrorPanel(response.responseJSON.data.message)
      }
    })
  })

  $(plugin_selector + ' #delete-button').off()
  $(plugin_selector + ' #delete-button').on('click', function () {
    var url = $(this).data('url')
    var deleteId = $(plugin_selector + ' #connectionSelect :selected').val()
    var formData = new FormData()
    formData.append('deleteId', deleteId)

    sendAjax(url, formData, function (response) {
      if (response.responseJSON.success) {
        alert(successDeleteMessage)
        location.reload()
      } else {
        showErrorPanel(response.responseJSON.data.message)
      }
    })
  })

  $(plugin_selector + ' #edit-button').off()
  $(plugin_selector + ' #edit-button').on('click', function () {
    var url = $(this).data('url')
    var editId = $(plugin_selector + ' #connectionSelect :selected').val()
    location.href = $(this).data('url') + '/id/' + editId
  })

}

function registerSelect () {
  $(plugin_selector + ' #connectionSelect').on('selectmenuchange', function () {
    location.href = $(this).find('option:selected').data('url')
  })
}

function registerAssignButtons () {
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

            return query;
          },
          processResults: function (data, params) {
            params.page = params.page || 1;

            return {
              results: data.data.result,
              pagination: {
                more: (params.page * 30) < data.data.total
              }
            };
          },
        },
        placeholder: searchMessage,
        minimumInputLength: 3,
      }
    );
  });

  $('.save-relationship').each(function () {
    var mailId = $(this).data('mail-id');
    var relatedSelectSelector = $(this).data('related-select');
    var selectValue = $('.' + relatedSelectSelector + ' option:selected').val()

    var formData = new FormData()
    formData.append('mailId', mailId)
    formData.append('valueId', selectValue)

    sendAjax(url, formData, function (response) {
      if (response.responseJSON.success) {
        alert(successSaveMessage)
        $(plugin_selector + ' #back-button').click()
      } else {
        showErrorPanel(response.responseJSON.data.message)
      }
    })

  });


}

function fetchInboxInputFormData () {
  var params = $('input, textarea, select')
  var formData = new FormData()
  var input = {}

  for (var i = 0; i < params.length; i++) {
    if (params[i].name && params[i].value) {
      var obj = {}
      var key = params[i].name
      obj[key] = params[i].value
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