$(document).on('click', '.panel-heading', function(e){
    var $this = $(this);
	if(!$this.hasClass('panel-collapsed')) {
		$this.parent('.panel').children('.panel-body').slideUp();
		$this.addClass('panel-collapsed');
	} else {
		$this.parent('.panel').children('.panel-body').slideDown();
		$this.removeClass('panel-collapsed');
	}
})
$(function () {
	$('.panel').children('.panel-body').css("display", "none");
});