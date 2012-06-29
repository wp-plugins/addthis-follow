(function($) {
    $('.atmorelink').live('click', function(e){
        e.preventDefault();
        $(this).siblings('.atmore').toggleClass('hidden');
        $(this).find('.atmore').toggleClass('hidden');
        $(this).find('.atless').toggleClass('hidden');
    });
})(jQuery);
