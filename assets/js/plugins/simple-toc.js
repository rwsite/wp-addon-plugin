
(function ( $ ) {
    $.fn.TableOfContents = function( options ) {
        var settings = $.extend({
            duration:   "1000",
            title:      "Contents",
            headings:   "h1, h2, h3, h4"
        }, options );

        return this.each(function() {
            var article = $(this);
            article.prepend('<div class="table-of-contents"><h3 id="toc-title" class="toc">' + settings.title + '</h3><ul></ul></div>');
            var list = article.find('div.table-of-contents:first > ul:first');

            article.find(settings.headings).each(function(){

                if($(this).attr('id') === 'toc-title'){
                    return null;
                }
                $(this).removeAttr('id');

                var heading = $(this);
                var tag = heading[0].tagName.toLowerCase();
                var title = heading.text();
                var id = heading.attr('id');

                if(typeof id === "undefined") {
                    id = Math.random().toString(36).substring(7);
                    heading.attr('id', id);
                }
                list.append('<li class="' + tag +'"><a href="#' + id + '" title="' + title + '">' + title + '</a></li>');
            });

            list.on('click', function(event){
                var target = $(event.target);

                if(target.is('a')){
                    event.preventDefault();
                    jQuery('html, body').animate({
                        scrollTop: $(target.attr('href')).offset().top-100
                    }, settings.duration);
                    return false;
                }
            });
        });

    };

}( jQuery ));

jQuery(document).ready(function() {
    jQuery('.post-content').TableOfContents();
});