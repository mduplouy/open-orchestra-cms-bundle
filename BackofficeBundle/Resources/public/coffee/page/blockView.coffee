BlockView = OrchestraView.extend(
  events:
    'click span.block-param': 'paramBlock'
    'click i.block-remove': 'confirmRemoveBlock'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'block'
      'area'
      'domContainer'
      'viewContainer'
      'published'
    ])
    @loadTemplates [
        "OpenOrchestraBackofficeBundle:BackOffice:Underscore/blockView"
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/blockView',
      block: @options.block
      published: @options.published
    )
    @options.domContainer.append @$el
    return

  paramBlock: (event) ->
    new adminFormView(
      url: @options.block.get('links')._self_form
      extendView: [ 'showVideo' ]
    )

  confirmRemoveBlock: (event) ->
    if @options.area.get("blocks").length > 0
      smartConfirm(
        'fa-trash-o',
        @$el.data('delete-confirm-question'),
        @$el.data('delete-confirm-explanation'),
        callBackParams:
          blockView: @
        yesCallback: (params) ->
          params.blockView.removeBlock(event)
      )

  removeBlock: (event) ->
    ul = $(event.target).parents("ul").first()
    $(event.target).parents("li").first().remove()
    refreshUl ul
    @options.viewContainer.sendBlockData({target: ul})
)
