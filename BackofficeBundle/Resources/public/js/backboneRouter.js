var OrchestraBORouter = Backbone.Router.extend({

  // Declare here only routes that are not declared in this.routes.
  // Routes in this.routes will be automatically added to routePatterns at init time
  // cf this.generateRoutePatterns()
  routePatterns: {},

//========[ROUTES LIST]===============================//

  routes: {
    'node/show/:nodeId': 'showNode',
    'node/show/:nodeId/:language': 'showNodeWithLanguage',
    'node/show/:nodeId/:language/:version': 'showNodeWithLanguageAndVersion',
    'template/show/:templateId': 'showTemplate',
    ':entityType/list(/:page)': 'listEntities',
    ':entityType/add': 'addEntity',
    ':entityType/edit/:entityId': 'showEntity',
    ':entityType/edit/:entityId/:language': 'showEntityWithLanguage',
    ':entityType/edit/:entityId/:language/source/:sourceLanguage': 'showEntityWithLanguageAndSourceLanguage',
    ':entityType/edit/:entityId/:language/:version': 'showEntityWithLanguageAndVersion',
    'folder/:folderId/list/media/:mediaId/edit': 'mediaEdit',
    'folder/:folderId/list': 'listFolder',
    'translation': 'listTranslations',
    'dashboard': 'showDashboard',
    '': 'showHome'
  },

  initialize: function() {
    this.generateRoutePatterns();
  },

//========[ACTIONS LIST]==============================//

  showHome: function()
  {
    this.navigate('dashboard', true);
  },

  showDashboard: function()
  {
    this.initDisplayRouteChanges();
    new DashboardView({domContainer : $('#content')});
  },

  showNode: function(nodeId)
  {
    this.showNodeWithLanguageAndVersion(nodeId);
  },

  showNodeWithLanguage: function(nodeId, language)
  {
    this.showNodeWithLanguageAndVersion(nodeId, language);
  },

  showNodeWithLanguageAndVersion: function(nodeId, language, version)
  {
      if (selectorExist($("#nav-node-" + nodeId))) {
          this.initDisplayRouteChanges("#nav-node-" + nodeId);
          showNode($("#nav-node-" + nodeId).data("url"), language, version);
      } else {
          Backbone.history.navigate("");
      }
  },

  showTemplate: function(templateId)
  {
    this.initDisplayRouteChanges();
    showTemplate($("#nav-template-" + templateId).data("url"));
  },

  listFolder: function(folderId)
  {
    this.initDisplayRouteChanges();
    GalleryLoad($('#' + folderId));
  },

  listEntities: function(entityType, page)
  {
    this.initDisplayRouteChanges("#nav-" + entityType);
    tableViewLoad($("#nav-" + entityType), entityType, page);
  },

  addEntity: function(entityType)
  {
    this.manageEntity(entityType, null, null, null, null, true)
  },

  showEntity: function(entityType, entityId)
  {
    this.manageEntity(entityType, null, entityId);
  },

  showEntityWithLanguage: function(entityType, entityId, language)
  {
    this.manageEntity(entityType, null, entityId, language);
  },

  showEntityWithLanguageAndSourceLanguage: function(entityType, entityId, language, sourceLanguage)
  {
    this.manageEntity(entityType, null, entityId, language, undefined, sourceLanguage);
  },
  
  showEntityWithLanguageAndVersion: function(entityType, entityId, language, version)
  {
    this.manageEntity(entityType, null, entityId, language, version);
  },

  manageEntity: function(entityType, page, entityId, language, version, sourceLanguage)
  {
    this.initDisplayRouteChanges("#nav-" + entityType);
    entityViewLoad($("#nav-" + entityType), entityType, page, entityId, language, version, sourceLanguage);
  },

  mediaEdit: function(folderId, mediaId)
  {
    this.initDisplayRouteChanges("#" + folderId);
    this.addRoutePattern("apiMediaEdit", $("#" + folderId).data("media-edit-url"));
    SuperboxLoad(folderId, mediaId);
  },

  listTranslations: function()
  {
    drawBreadCrumb();
    var view = new TranslationView(
      {url : $("#nav-translation").data("url")}
    );
    return view;
  },

//========[INTERNAL FUNCTIONS]========================//

  addRoutePattern: function(routeName, routePattern)
  {
    this.routePatterns[routeName] = routePattern;
  },

  generateRoutePatterns: function()
  {
    var currentRouter = this;
    $.each(this.routes, function(routePattern, routeName) {
      currentRouter.addRoutePattern(routeName, routePattern);
    });
    this.addRoutePattern(
      'loadUnderscoreTemplate',
      $('#contextual-informations').data('templateUrlPattern')
    );
  },

  initDisplayRouteChanges: function(selector)
  {
    selector = (selector == undefined) ? '[href="#' + Backbone.history.fragment + '"]' : selector;
    $('nav li.active').removeClass("active");
    $('nav li:has(a' + selector + ')').addClass("active");
    document.title = $('nav a' + selector).attr('title') || document.title;
    $.ajaxSetup().abortXhr()
    
    drawBreadCrumb();
    displayLoader();
  },

  showNodeForm: function(parentNode)
  {
    $('.modal-title').text(parentNode.text());
    $.ajax({
      url: parentNode.data('url'),
      method: 'GET',
      success: function(response) {
        $('#OrchestraBOModal').modal('show');
        var view;
        return view = new adminFormView({
          html: response
        });
      }
    }); 
  },

  generateUrl: function(routeName, paramsObject)
  {
    var route = this.routePatterns[routeName];
    if (typeof route !== "undefined") {
      if (typeof paramsObject !== "undefined") {
        $.each(paramsObject, function(paramName, paramValue) {
            if (route.indexOf('(/:' + paramName + ')') != -1) {
                route = route.replace('(/:' + paramName + ')', '/'+paramValue);
            } else {
                route = route.replace(':' + paramName, paramValue);
            }
        });
      }
      route = route.replace(/\(\/:[^\/]*\)/g, '');
    } else {
      alert('Error, route name is unknown');
      return false;
    }

    return route;
  },

  addParametersToRoute: function(options)
  {
    var Router = this,
        fragment = Backbone.history.fragment,
        routes = _.pairs(Router.routes),
        route = null, matched, paramsObject = null, paramsKeys = null;
    matched = _.find(routes, function(handler) {
      route = _.isRegExp(handler[0]) ? handler[0] : Router._routeToRegExp(handler[0]);
      return route.test(fragment);
    });
    
    if(matched) {
      paramsKeys = _.compact(Router._extractParameters(route, matched[0]));
      for(var i in paramsKeys){
        paramsKeys[i] = paramsKeys[i].substring(1);
      }
      paramsObject = _.object(paramsKeys, _.compact(Router._extractParameters(route, fragment)))
      paramsObject = _.extend(paramsObject, options);
      return paramsObject;
    }
    return {};
  }
});


var appRouter = new OrchestraBORouter();

jQuery(function() {
    if (window.location.pathname.indexOf('login') == -1) {
        Backbone.history.start();
    }
});
