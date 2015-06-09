currentModal = null

MediaWysiwygView = MediaModalView.extend(

  showFolder: (event) ->
    displayLoader $(".modal-body-content", @$el)
    GalleryLoad $(event.target), $(".modal-body-content", @$el), GalleryCollectionWysiwygView
)
