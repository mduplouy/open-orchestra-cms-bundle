GalleryLoad = (link, target, galleryView) ->
  galleryView = galleryView or GalleryCollectionView;
  if typeof target is "undefined"
    target = "#content"
  title = link.text()
  listUrl = Backbone.history.fragment
  $.ajax
    url: link.data('url')
    method: 'GET'
    success: (response) ->
      medias = new GalleryElement
      medias.set response
      new galleryView(
        medias: medias
        title: title
        listUrl: listUrl
        domContainer: $(target)
        modal: target != '#content'
      )
