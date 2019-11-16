;(function ($) {
    var LM = function (ele, options) {
        this.$element = ele;
        this.defaults = {};
        this.settings = $.extend({}, this.defaults, options)
    };
    LM.prototype = {
        menu : function () {
            scrollPosition(".sidebar-current");
            var _this = this.$element;
            $('.menu-dark-backdrop').on('click', function () {
                if (_this.hasClass('menu-open')) {
                    _this.removeClass('menu-open');
                    $('.menu-dark-backdrop').removeClass('in').off();
                    $('body').css("overflow", "auto");
                    _this.find('li').removeClass('open').off().find('div').css({ "height": 0 });
                    _this.scrollTop(0)
                } else {
                    _this.addClass('menu-open');
                    $('.menu-dark-backdrop').addClass('in');
                    $('body').css("overflow", "hidden");
                }
            })
        },
        init: function () {
            var $btn = $(this.settings.triggerBtn);
            var obj = this;
            $btn.click(function () {
                if (!$('body').find('div').hasClass('menu-dark-backdrop')) {
                    $('body').append('<div class="menu-dark-backdrop"></div>')
                }
                if (obj.$element.hasClass('menu-open')) {
                    obj.$element.removeClass('menu-open');
                    $('.menu-dark-backdrop').removeClass('in').off();
                    $('body').css("overflow", "auto");
                    obj.$element.find('li').removeClass('open').off().find('div').css({ "height": 0 });
                    obj.$element.scrollTop(0)
                } else {
                    obj.$element.addClass('menu-open');
                    $('.menu-dark-backdrop').addClass('in');
                    obj.menu()
                }
            })
        }
    };

    $.fn.leftMenu = function (options) {
        var lm = new LM(this, options);
        lm.$element.addClass('leftMenu');
        return lm
    }
}(jQuery));