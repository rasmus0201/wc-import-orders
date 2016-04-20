/*jQuery(document).ready(function ($) {

var slider_options = {
	$AutoPlay: false,
	$Idle: 0,
	$AutoPlaySteps: 4,
	$SlideDuration: 5000,
	$SlideEasing: $Jease$.$Linear,
	$PauseOnHover: 4,
	$SlideWidth: 140,
	$Cols: 7
};

var slider = new $JssorSlider$("slider", slider_options);

//responsive code begin
//you can remove responsive code if you don't want the slider scales while window resizing
function ScaleSlider() {
	var refSize = slider.$Elmt.parentNode.clientWidth;
	if (refSize) {
		refSize = Math.min(refSize, 809);
		slider.$ScaleWidth(refSize);
	}
	else {
		window.setTimeout(ScaleSlider, 30);
	}
}
ScaleSlider();
	$(window).bind("load", ScaleSlider);
	$(window).bind("resize", ScaleSlider);
	$(window).bind("orientationchange", ScaleSlider);
	//responsive code end
});*/