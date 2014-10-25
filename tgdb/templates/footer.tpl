<script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type='text/javascript' src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<script type='text/javascript' src="js/jquery.floatThead._.js"></script>
<script type='text/javascript' src="js/jquery.floatThead.js"></script>
<script type='text/javascript' src="js/easyAjaxBSModals.js"></script>

<script type='text/javascript'>
//enables the popover mabober

$(document).ajaxComplete(function () {$(".crossrefpopover").delay(1000).popover();});


var originalLeave = $.fn.popover.Constructor.prototype.leave;
$.fn.popover.Constructor.prototype.leave = function(obj){
  var self = obj instanceof this.constructor ?
    obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type)
  var container, timeout;

  originalLeave.call(this, obj);

  if(obj.currentTarget) {
    container = $(obj.currentTarget).siblings('.popover')
    timeout = self.timeout;
    container.one('mouseenter', function(){
      //We entered the actual popover – call off the dogs
      clearTimeout(timeout);
      //Let's monitor popover content instead
      container.one('mouseleave', function(){
        $.fn.popover.Constructor.prototype.leave.call(self, self);
      });
    })
  }
};


$(".crossrefpopover").popover({trigger: 'click hover', delay: {show: 250, hide: 400}});

//float the thead of all tables with class floatthead
$('table.floatthead:visible').floatThead({
		useAbsolutePositioning : false
	}
);

//fixes floatthead in collapsing panels

//remove floatthead from all panels
$('.panel-collapse table.floatthead').floatThead('destroy');
//re-add it to all panels that are visible
$('.panel-collapse.in table.floatthead').floatThead({
		useAbsolutePositioning : false
});

//adds/removes floatingthead when table is being hidden/shown
$('.panel-collapse').on('hide.bs.collapse', function () {
	$('table.floatthead').floatThead('destroy');
});
$('.panel-collapse').on('hidden.bs.collapse', function () {
	$('table.floatthead:visible').delay(1000).floatThead({
		useAbsolutePositioning : false
	});
	$('table.floatthead').floatThead('reflow'); //reflow all tables because vertical positioning has changed
});
$('.panel-collapse').on('show.bs.collapse', function () {
	$('table.floatthead').floatThead('destroy');
});
$('.panel-collapse').on('shown.bs.collapse', function () {
	$('table.floatthead:visible').delay(500).floatThead({
			useAbsolutePositioning : false
	});
	$('table.floatthead').delay(1000).floatThead('reflow'); //reflow all tables because vertical positioning has changed
});


//disables bootstrap modal dialog caching.

$("div[id$='Modal']").on('hidden.bs.modal',
    function () {
        $(this).removeData('bs.modal');
    }
);


</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-55954246-1', 'auto');
  ga('send', 'pageview');

</script>
{#IFDEF:DEBUG}
{TIMETABLE}
{#ENDIF}