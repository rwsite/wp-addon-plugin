(function () {
    let $, e;
    $ = jQuery, e = function () {
        function e() {
            this.widget_wrap = $(".widget-liquid-right"), this.widget_area = $("#widgets-right"), this.widget_add = $("#tmpl-stag-add-widget"), this.create_form(), this.add_elements(), this.events()
        }

        return e.prototype.create_form = function () {
            this.widget_wrap.append(this.widget_add.html()), this.widget_name = this.widget_wrap.find('input[name="stag-add-widget"]'), this.nonce = this.widget_wrap.find('input[name="scs-delete-nonce"]').val()
        }, e.prototype.add_elements = function () {

            console.log(this.widget_area); console.log(objectL10n);

            this.widget_area.find(".sidebar-stag-custom").append('<span class="scs-area-delete"><span class="dashicons dashicons-no"></span></span>'), this.widget_area.find(".sidebar-stag-custom").each(function () {
                var e, t;
                t = $(this).find(".widgets-sortables"), e = t.attr("id").replace("sidebar-", ""), t.find(".sidebar-description").length > 0 ? t.find(".sidebar-description").prepend("<p class='description'>" + objectL10n.shortcode + ": <code>[custom_sidebars id='" + e + "']</code></p>") : t.find(".sidebar-name").after("<div class='sidebar-description'><p class='description'>" + objectL10n.shortcode + ": <code>[custom_sidebars id='" + e + "']</code></p></div>")
            })

        }, e.prototype.events = function () {
            this.widget_wrap.on("click", ".scs-area-delete", $.proxy(this.delete_sidebar, this))
        }, e.prototype.delete_sidebar = function (e) {
            var t, i, d, s, a;
            s = $(e.currentTarget).parents(".widgets-holder-wrap:eq(0)"), d = s.find(".sidebar-name h2"), i = s.find(".spinner"), a = s.children().first().attr("id"), t = this, confirm(objectL10n.delete_sidebar_area) && $.ajax({
                type: "POST",
                url: window.ajaxurl,
                data: {action: "stag_ajax_delete_custom_sidebar", name: a, _wpnonce: t.nonce},
                beforeSend: function () {
                    i.addClass("activate")
                },
                success: function (e) {
                    "sidebar-deleted" === e && s.slideUp(200, function () {
                        $(".widget-control-remove", s).trigger("click"), s.remove(), wpWidgets.saveOrder()
                    })
                }
            })
        }, e
    }(), $(function () {
        var t;
        return t = new e
    })
}).call(this);