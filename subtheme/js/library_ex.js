(function ($) {
  $('a[href^="#"]').click(function(){
       var target = $(this).attr('href');
       $('html, body').animate({scrollTop: $(target).offset().top}, 1000);
       return false;
});
})(jQuery);




Document.onscroll = function() {
  let position = window.scrollY || document.documentElement.scrollTop;
  position += 50;

  if (position <= 50) {
    header.classList.remove('scroll');
  } else {
    header.classList.add('scroll');
  }

  activateColor(position);
  activateLinks(position);
}

