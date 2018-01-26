#require js/jquery.js
#require js/jquery-ui.min.js
#require js/jquery.ui.touch-punch.min.js
#require js/bootstrap/affix.js
#require js/bootstrap/alert.js
#require js/bootstrap/button.js
#require js/bootstrap/carousel.js
#require js/bootstrap/collapse.js
#require js/bootstrap/dropdown.js
#require js/bootstrap/modal.js
#require js/bootstrap/tooltip.js
#require js/bootstrap/popover.js
#require js/bootstrap/tab.js
#require js/bootstrap/transition.js
#require js/xing/wysihtml5-0.3.0.min.js
#require js/jhollingworth/bootstrap-wysihtml5.js
#require js/bootbox.js
#require js/jquery.autosize.min.js
#require js/ccheckin.js


(function ($) {
    $(function () {

      if(navigator.userAgent.match('CriOS')) {
        alert ('This browser is not recommended.');
      }

      $(document.body).on('click', '.disabled :input', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });

      autosize($('textarea.autosize'));

      $('.wysiwyg').wysihtml5({'html': true, 'stylesheets': []});

      $('.sticky-section-header').each(function () {

      });

      $('.fixed-position-container').each(function () {
        var $self = $(this);
        var position = $self.position();
        $self.data('originalPosition', position);
        $self.find('.fixed-position').each(function () {
            var $mySelf = $(this);
            var myPosition = $mySelf.position();
            $mySelf.data('originalPosition', myPosition);
        });
        $self.find('.fixed-position').each(function () {
            var $mySelf = $(this);
            var myPosition = $mySelf.data('originalPosition');
            var absolutePosition = {
                top: position.top + myPosition.top,
                left: position.left + myPosition.left
            };
            $mySelf.css({
                position: 'fixed',
                top: absolutePosition.top + 'px',
                left: absolutePosition.left + 'px'
            });
        });
      })

      $(window).on('resize', function () {
         $('.fixed-position-container').each(function () {
            var $self = $(this);
            var position = $self.data('originalPosition');
            var newPosition = $self.position();
            $self.find('.fixed-position').each(function () {
                var $mySelf = $(this);
                var myPosition = $mySelf.data('originalPosition');
                var absolutePosition = {
                    top: position.top + myPosition.top,
                    left: newPosition.left + myPosition.left
                };
                $mySelf.css({
                    position: 'fixed',
                    top: absolutePosition.top + 'px',
                    left: absolutePosition.left + 'px'
                });
            });
         });
      });

    });
})(jQuery);