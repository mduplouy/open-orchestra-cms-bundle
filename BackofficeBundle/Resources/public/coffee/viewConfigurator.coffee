OrchestraViewConfigurator = ->
  configurations: {}
  baseConfigurations:
    'editEntity': FullPageFormView
    'addEntity': FullPageFormView
    'addArea': AreaView
    'addBlock': BlockView
    'addButtonAction': TableviewAction
    'addConfigurationButton': PageConfigurationButtonView
    'showAdminForm': adminFormView
    'showBlocksPanel': BlocksPanelView
    'showNode': NodeView
    'showTemplate': TemplateView
    'showLanguage': LanguageView
    'showDuplicate': DuplicateView
    'showPreviewLinks': PreviewLinkView
    'showStatus': StatusView
    'showVersion': VersionView
    'showOrchestraModal': OrchestraModalView

  setConfiguration: (entityType, action, view) ->
    @configurations[entityType] = [] if typeof @configurations[entityType] == "undefined"
    @configurations[entityType][action] = view
    return

  getConfiguration: (entityType, action) ->
    entityTypeConfiguration = @configurations[entityType]
    if typeof entityTypeConfiguration != 'undefined'
      view = entityTypeConfiguration[action]
      if typeof view != 'undefined'
        return view
    return @baseConfigurations[action]

jQuery ->
  window.appConfigurationView = new OrchestraViewConfigurator()
