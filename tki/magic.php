
<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>SmallEnvelop : Simple PreLoader</title>
	<style type="text/css">/* Paste this css to your style sheet file or under head tag */
/* This only works with JavaScript, 
if it's not present, don't show loader */
.no-js #loader { display: none;  }
.js #loader { display: block; position: absolute; left: 100px; top: 0; }
.se-pre-con {
	position: fixed;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 100%;
	z-index: 9999;
	background: url(Preloader_8.gif) center no-repeat #fff;
}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script><script>
	//paste this code under head tag or in a seperate js file.
	// Wait for window load
	$(window).on("load", function() {
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");;
	});
</script>
</head>
<body><!-- Paste this code after body tag -->
<div class="se-pre-con"></div>
<!-- Ends -->

<div class="content">

<img src="https://p.bigstockphoto.com/jdVZ6zirQdGjAE2vCmmK_bigstock-A-Telltale-Sign-Of-Summer-Hyd-272112430.jpg"  style="visibility: hidden; width:100%; height: auto;"/>

</div>
<style type="text/css">.content {			
			background-size: 100%;
			width: 100%;		
}
</style>
</body>
</html>
