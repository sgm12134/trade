define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var Controller = {
       index: function () {
           // 标记为已读
           $(document).on('click', '.mark-event', function () {
               Fast.api.ajax($(this).data('url'), function () {
                   location.reload();
               });
           });


           //点击包含.btn-dialog的元素时弹出dialog
           $(document).on('click', '.btn-dialog,.dialogit', function (e) {
               if(e.returnValue){
                   e.returnValue = false
               }else{
                   e.preventDefault()
               }
               var that = this;
               var options = $.extend({}, $(that).data() || {});
               var url = $(that).data("url") || $(that).attr('href');
               var title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title');
               if (typeof options.confirm !== 'undefined') {
                   Layer.confirm(options.confirm, function (index) {
                       Frontend.api.open(url, title, options);
                       Layer.close(index);
                   });
               } else {
                   window[$(that).data("window") || 'self'].Frontend.api.open(url, title, options);
               }
               return false;
           });
       }
    };
    return Controller;
});
