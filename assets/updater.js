(function ($) {
  "use strict";

  var frame;

  $(document).ready(function () {
    $("#upload_json_btn").on("click", function (e) {
      e.preventDefault();

      // Reuse existing frame — avoids duplicate modal instances on repeated clicks.
      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: elementorSettingsUpdater.title,
        button: { text: elementorSettingsUpdater.button },
        multiple: false,
        library: { type: "application/json" },
      });

      // Pre-select the previously chosen attachment when the frame reopens.
      frame.on("open", function () {
        var currentId = $("#json_attachment_id").val();

        if (!currentId) {
          return;
        }

        var selection = frame.state().get("selection");
        var attachment = wp.media.attachment(currentId);

        attachment.fetch();
        selection.add(attachment ? [attachment] : []);
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();

        $("#json_attachment_id").val(attachment.id);
        $("#selected_file").text(
          elementorSettingsUpdater.selected + attachment.filename,
        );
      });

      frame.open();
    });
  });
})(jQuery);
