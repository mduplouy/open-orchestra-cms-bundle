extendView = extendView || {}
extendView['orchestraMediaType'] =
  events:
    'click .mediaModalOpen': 'launchMediaModal'

  launchMediaModal: (event) ->
    event.preventDefault()
    @method = if @options.method then @options.method else 'GET'
    target = $(event.currentTarget)
    modal = $('#' + target.data("target"), @$el)
    inputId = target.data("input")
    url = target.data("url")
    @openMediaModal(modal, inputId, url, @method)
