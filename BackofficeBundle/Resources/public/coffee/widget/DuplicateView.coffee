DuplicateView = OrchestraView.extend(
  events:
    'click': 'duplicateElement'

  initialize: (options) ->
    @options = options
    @loadTemplates [
      "OpenOrchestraBackofficeBundle:BackOffice:Underscore/widgetDuplicate"
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/widgetDuplicate',
      text: @options.domContainer.data('text')
    )
    @options.domContainer.append @$el
    return

  duplicateElement: (event) ->
    event.preventDefault()
    displayLoader()
    redirectUrl = appRouter.generateUrl(@options.currentDuplicate.path, appRouter.addParametersToRoute(
      language: @options.currentDuplicate.language
    ))
    $.ajax
      url: @options.currentDuplicate.self_duplicate
      method: 'POST'
      success: ->
        if (redirectUrl != Backbone.history.fragment)
          Backbone.history.navigate(redirectUrl, {trigger: true})
        else
          Backbone.history.loadUrl()
    return
)
