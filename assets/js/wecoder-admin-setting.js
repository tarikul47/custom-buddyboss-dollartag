(function ($) {
  "use strict";

  $(document).on("click", ".hashtag-delete", function (e) {
    e.preventDefault();
    var id = $(this).data("id");
    var name = $(this).data("name");
    var type = $(this).data("type");

    //  console.log('hashtag',wecoder_ajax_obj);

    var data = {
      action: "wecoder_delete_hashtag",
      id: id,
      name: name,
      type: type,
      ajax_nonce: wecoder_ajax_obj.ajax_nonce,
    };
    $.post(wecoder_ajax_obj.ajax_url, data, function (response) {
      console.log("hashtag", response);
      $("#buddypress-hashtags-" + id).remove();
      // location.reload(true);
    });
  });

  // Ajax action to clear buddypress hashtags.
  $(document).on("click", ".wecoder-clear-bp-hashtags", function (e) {
    e.preventDefault();
    var clickd_obj = $(this);
    var clickd_txt = $(".wecoder-clear-bp-hashtags").text();
    clickd_obj.text(wecoder_ajax_obj.wait_text);

    var data = {
      action: "wecoder_clear_buddypress_hashtag_table",
      ajax_nonce: wecoder_ajax_obj.ajax_nonce,
    };
    $.post(wecoder_ajax_obj.ajax_url, data, function (response) {
      clickd_obj.text(clickd_txt);
    });
  });
})(jQuery);
