MediaWysiwygView = MediaView.extend(

  initialize: (options) ->
    @events = @events || {}
    @options = @reduceOption(options, [
      'modal'
      'media'
      'domContainer'
    ])
    @mediaClass = "media-select"
    @mediaLogo = "fa-check-circle"

    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaModalView',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView'
    ]

  mediaSelect: (event) ->
    event.preventDefault()
    viewContext = @
    media = @options.media
    thumbnail = media.get("thumbnails")
    thumbnail["original"] = media.get("displayed_image")

    $.ajax
      url: media.attributes.links["_self_crop"]
      method: "GET"

      success: (response) ->
        $(".modal-body-content").html(response)
        .append(viewContext.renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView'
            src: thumbnail["original"]
        ))
        .find("#media_crop_format")
        .change(
          ->
            format = $('#media_crop_format').val()
            $('#preview_thumbnail').attr 'src', thumbnail[format]
            return
        )
        .find("option[value='']")
        .prop('selected',true)
        .val("original")

      complete: () ->
        validationBtn = $("#sendToTiny")
        validationBtn.click(
          ->
            tinymce.execCommand(
              'mceInsertContent',
              false,
              '<img src="' + thumbnail[$('#media_crop_format').val()] + '"/>'
            )
            validationBtn.closest(".mediaModalContainer").find('.mediaModalClose').click()
            return false
        )
        return
)
