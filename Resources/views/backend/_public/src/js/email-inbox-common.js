function sendAjax (url, formData, callback) {
  $.ajax({
    type: 'post',
    url: url,
    contentType: false,
    cache: false,
    processData: false,
    data: formData,
    xhr: function () {
      var jqXHR = null
      if (window.ActiveXObject) {
        jqXHR = new window.ActiveXObject('Microsoft.XMLHTTP')
      } else {
        jqXHR = new window.XMLHttpRequest()
      }

      return jqXHR
    },
    complete: function (response) {
      hideInfoPanel()
      hideErrorPanel()

      callback(response);
    }
  })
}