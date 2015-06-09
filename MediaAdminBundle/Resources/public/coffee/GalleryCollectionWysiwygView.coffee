GalleryCollectionWysiwygView = GalleryCollectionView.extend(
  addElementToView: (mediaData) ->
    mediaModel = new GalleryModel
    mediaModel.set mediaData
    new GalleryWysiwygView(@addOption(
      media: mediaModel
      domContainer: this.$el.find('.superbox')
    ))
    return
)
