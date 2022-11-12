jQuery(document).ready(function($){
    // Add smooth scrolling to all links
    $("a").on('click', function(event) {
        var link = $($(this).attr('href'));
        $('html,body').animate({ scrollTop: link.offset().top - 100}, 800);
        return false;
    });
});