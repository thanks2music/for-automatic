//ここに追加したいJavaScript、jQueryを記入してください。
//このJavaScriptファイルは、親テーマのJavaScriptファイルのあとに呼び出されます。
//JavaScriptやjQueryで親テーマのjavascript.jsに加えて関数を記入したい時に使用します。
(function($){
  var jpHeader = 'menu-asobiba-mobile-header-menu-2023';
  var enHeader = 'menu-asobiba-mobile-header-menu-2023-for-english';
  var $pc_form_search_work = $('#search__works--pc select');
  var $sp_form_search_work = $('#search__works--sp  select');
  var $elementor_hide_elements = $('.elementor__is-hide');
  var $sp_future_genre = $('.asobiba__sp__future__genre');

  $(function() {
    $('#' + jpHeader).find('.menu-button-in').addClass('is-loaded');
    $('#' + enHeader).find('.menu-button-in').addClass('is-loaded');
    $elementor_hide_elements.removeClass('elementor__is-hide');
    $sp_future_genre.addClass('is-loaded');

    $('body').addClass('asobiba__loaded');

    $pc_form_search_work.on('change', function() {
      const category_url = $(this).val();

      setTimeout(function() {
        window.location.href = category_url;
      }, 100);
    });

    $sp_form_search_work.on('change', function() {
      const category_url = $(this).val();

      setTimeout(function() {
        window.location.href = category_url;
      }, 100);
    });
  });

  // Memo: CocoonはデフォルトでTop:0が前提で実装されているため、ナビゲーションの位置を調整する
  function asobibaHeaderPosition(menuId) {
    var $headerMenu = $('#' + menuId);
    var headerHeight = $headerMenu.outerHeight();
    var headerStartPos = 45;

    $headerMenu.on('click', function() {
      $(this).css("z-index", "100");
    });

    $(window).scroll(function() {
      var headerCurrentPos = $(this).scrollTop();

      if ( headerCurrentPos > headerStartPos ) {
        if (headerCurrentPos >= 100) {
          $headerMenu.css('top', '-' + headerHeight + 'px');
        }
      } else if (90 > headerCurrentPos) {
        headerStartPos = 45;
        $headerMenu.css('top', headerStartPos + 'px');
      } else if (100 > headerCurrentPos) { 
        $headerMenu.css('top', 0);
      } else {
        $headerMenu.css('top', 0);
      }
      headerStartPos = headerCurrentPos;
    });
  }

  !! document.getElementById(jpHeader) ? asobibaHeaderPosition(jpHeader) : false;
  !! document.getElementById(enHeader) ? asobibaHeaderPosition(enHeader) : false;
})(jQuery);


$(function() {
  var prevScrollTop = -1;
  var $window = $(window);
  var pageTopEle = $('#elementor__page-top');
  var $fav_button = $('.simplefavorite-button:last-child');

  $(window).scroll(function() {
    var scrollTop = $window.scrollTop();
    var threashold = 600;
    var s1 = (prevScrollTop > threashold);
    var s2 = (scrollTop > threashold);
    var $fav_explain = $fav_button.find('.asobiba__favorites__explain');

    if (s1 ^ s2) {
      if (s2) {
        pageTopEle.fadeIn('slow');
      } else {
        pageTopEle.fadeOut('slow');
      }
    }

    if (scrollTop > threashold) {
      $fav_button.addClass('is--active');
    } else {
      $fav_button.removeClass('is--active');
    }

    if (scrollTop > (threashold * 4)) {
      $fav_explain.addClass('is--deep');
    } else {
      $fav_explain.removeClass('is--deep');
    }

    prevScrollTop = scrollTop;
  });
});
