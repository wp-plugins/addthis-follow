(function($) {
    $('.atmorelink').live('click', function(e){
        e.preventDefault();
        $('.atfollowservice').filter('.atmore').toggleClass('hidden');
        $(this).find('.atmore').toggleClass('hidden');
        $(this).find('.atless').toggleClass('hidden');
    });
})(jQuery);
