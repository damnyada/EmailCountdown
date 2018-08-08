<?php

	//Leave all this stuff as it is
	date_default_timezone_set('America/Sao_Paulo');
	include 'GIFEncoder.class.php';
	include 'php52-fix.php';

	// Convert HEX colors to RGB
	function convertToRGB($hex) {
		$hex = str_replace("#", "", $hex);
		
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);

		return $rgb;
	}

	$font_size = isset($_GET['font_size']) ? $_GET['font_size'] : 32;  // font size (default: 32)
	$font_color = isset($_GET['font_color']) ? $_GET['font_color'] : '#000000';  // font color (default: #000)
	$bg_color = isset($_GET['bg_color']) ? $_GET['bg_color'] : '#ffffff';  // bg color (default: #fff)

	$width = isset($_GET['width']) ? $_GET['width'] : 325;  // width (default: 325)
	$height = isset($_GET['height']) ? $_GET['height'] : 94;  // height (default: 94)
	$x_offset = isset($_GET['x_offset']) ? $_GET['x_offset'] : 42;  // space between left border and text (default: 42)
	$y_offset = isset($_GET['y_offset']) ? $_GET['y_offset'] : 62;  // space between upper border and text (default: 62)

	$time = $_GET['time'];
	$future_date = new DateTime(date('r',strtotime($time)));
	$time_now = time();
	$now = new DateTime(date('r', $time_now));
	$frames = array();	
	$delays = array();
	
	// Your image link
	$image = imagecreatefrompng('images/countdown.png');
	$delay = 100;// milliseconds

	// Converting font color to RGB
	$font_color_rgb = convertToRGB($font_color);

	$font = array(
		'size' => $font_size, // Font size, in pts usually.
		'angle' => 0, // Angle of the text
		'x-offset' => $x_offset, // The larger the number the further the distance from the left hand side, 0 to align to the left.
		'y-offset' => $y_offset, // The vertical alignment, trial and error between 20 and 60.
		'file' => __DIR__ . DIRECTORY_SEPARATOR . 'Futura.ttc', // Font path
		'color' => imagecolorallocate($image, $font_color_rgb[0], $font_color_rgb[1], $font_color_rgb[2]), // RGB Colour of the text
	);

	for($i = 0; $i <= 60; $i++){
		
		$interval = date_diff($future_date, $now);

		// Create a new image based on width and height
		$image = imagecreatetruecolor($width, $height);

		// Converting gb color to RGB
		$bg_color_rgb = convertToRGB($bg_color);

		$gif_bg = imagecolorallocate($image, $bg_color_rgb[0], $bg_color_rgb[1], $bg_color_rgb[2]);
		imagefill($image, 0, 0, $gif_bg);

		if($future_date < $now){			
			$text = $interval->format('00 : 00 : 00');
			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 1;
			ob_end_clean();
			break;
		} else {
			$days = $interval->format('0%a');
			$hours = $interval->format('%H');
			$hours = ($days > 0 ? $days * 24 + $hours : $hours);		// days as hours
			$text = $interval->format('%I : %S');

			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $hours.' : '.$text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 0;
			ob_end_clean();
		}

		$now->modify('+1 second');
	}

	//expire this image instantly
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
	$gif = new AnimatedGif($frames,$delays,$loops);
	$gif->display();
