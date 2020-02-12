<?php

if(!function_exists('servicemaster_mikado_loading_spinners')) {
	function servicemaster_mikado_loading_spinners($return = false) {
		global $servicemaster_options;

		$spinner_html = '';
		$svg_spinner_html = '';

		if(isset($servicemaster_options['smooth_pt_spinner_type'])) {

			switch($servicemaster_options['smooth_pt_spinner_type']) {
				case "svg_spinner":
					$svg_spinner_html = servicemaster_mikado_svg_spinner();
					break;
				case "pulse":
					$spinner_html = servicemaster_mikado_loading_spinner_pulse();
					break;
				case "double_pulse":
					$spinner_html = servicemaster_mikado_loading_spinner_double_pulse();
					break;
				case "cube":
					$spinner_html = servicemaster_mikado_loading_spinner_cube();
					break;
				case "rotating_cubes":
					$spinner_html = servicemaster_mikado_loading_spinner_rotating_cubes();
					break;
				case "stripes":
					$spinner_html = servicemaster_mikado_loading_spinner_stripes();
					break;
				case "wave":
					$spinner_html = servicemaster_mikado_loading_spinner_wave();
					break;
				case "two_rotating_circles":
					$spinner_html = servicemaster_mikado_loading_spinner_two_rotating_circles();
					break;
				case "five_rotating_circles":
					$spinner_html = servicemaster_mikado_loading_spinner_five_rotating_circles();
					break;
				case "atom":
					$spinner_html = servicemaster_mikado_loading_spinner_atom();
					break;
				case "clock":
					$spinner_html = servicemaster_mikado_loading_spinner_clock();
					break;
				case "mitosis":
					$spinner_html = servicemaster_mikado_loading_spinner_mitosis();
					break;
				case "lines":
					$spinner_html = servicemaster_mikado_loading_spinner_lines();
					break;
				case "fussion":
					$spinner_html = servicemaster_mikado_loading_spinner_fussion();
					break;
				case "wave_circles":
					$spinner_html = servicemaster_mikado_loading_spinner_wave_circles();
					break;
				case "pulse_circles":
					$spinner_html = servicemaster_mikado_loading_spinner_pulse_circles();
					break;
                default:
                    $spinner_html = servicemaster_mikado_loading_spinner_pulse();
                    break;
			}
		} else {
			$spinner_html = servicemaster_mikado_svg_spinner();
		}

		if($return === true) {
			return $spinner_html . $svg_spinner_html;
		}

		echo wp_kses($spinner_html, array(
			'div' => array(
				'class' => true,
				'style' => true,
				'id'    => true
			)
		));
	}
}

if(!function_exists('servicemaster_mikado_svg_spinner')) {
	function servicemaster_mikado_svg_spinner() {
	$html = '';
	$html .= '<div class="mkd-svg-spinner-holder mkd-blink">';
	$html .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				 width="150px" height="150px" viewBox="0 0 450 450" style="enable-background:new 0 0 150 150;" xml:space="preserve">
				'.servicemaster_mikado_options()->getOptionValue('smooth_pt_spinner_svg_path').'
			</svg>
			';
	$html .= '</div>';

	return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_pulse')) {
	function servicemaster_mikado_loading_spinner_pulse() {
		$html = '';
		$html .= '<div class="pulse"></div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_double_pulse')) {
	function servicemaster_mikado_loading_spinner_double_pulse() {
		$html = '';
		$html .= '<div class="double_pulse">';
		$html .= '<div class="double-bounce1"></div>';
		$html .= '<div class="double-bounce2"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_cube')) {
	function servicemaster_mikado_loading_spinner_cube() {
		$html = '';
		$html .= '<div class="cube"></div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_rotating_cubes')) {
	function servicemaster_mikado_loading_spinner_rotating_cubes() {
		$html = '';
		$html .= '<div class="rotating_cubes">';
		$html .= '<div class="cube1"></div>';
		$html .= '<div class="cube2"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_stripes')) {
	function servicemaster_mikado_loading_spinner_stripes() {
		$html = '';
		$html .= '<div class="stripes">';
		$html .= '<div class="rect1"></div>';
		$html .= '<div class="rect2"></div>';
		$html .= '<div class="rect3"></div>';
		$html .= '<div class="rect4"></div>';
		$html .= '<div class="rect5"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_wave')) {
	function servicemaster_mikado_loading_spinner_wave() {
		$html = '';
		$html .= '<div class="wave">';
		$html .= '<div class="bounce1"></div>';
		$html .= '<div class="bounce2"></div>';
		$html .= '<div class="bounce3"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_two_rotating_circles')) {
	function servicemaster_mikado_loading_spinner_two_rotating_circles() {
		$html = '';
		$html .= '<div class="two_rotating_circles">';
		$html .= '<div class="dot1"></div>';
		$html .= '<div class="dot2"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_five_rotating_circles')) {
	function servicemaster_mikado_loading_spinner_five_rotating_circles() {
		$html = '';
		$html .= '<div class="five_rotating_circles">';
		$html .= '<div class="spinner-container container1">';
		$html .= '<div class="circle1"></div>';
		$html .= '<div class="circle2"></div>';
		$html .= '<div class="circle3"></div>';
		$html .= '<div class="circle4"></div>';
		$html .= '</div>';
		$html .= '<div class="spinner-container container2">';
		$html .= '<div class="circle1"></div>';
		$html .= '<div class="circle2"></div>';
		$html .= '<div class="circle3"></div>';
		$html .= '<div class="circle4"></div>';
		$html .= '</div>';
		$html .= '<div class="spinner-container container3">';
		$html .= '<div class="circle1"></div>';
		$html .= '<div class="circle2"></div>';
		$html .= '<div class="circle3"></div>';
		$html .= '<div class="circle4"></div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_atom')) {
	function servicemaster_mikado_loading_spinner_atom() {
		$html = '';
		$html .= '<div class="atom">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_clock')) {
	function servicemaster_mikado_loading_spinner_clock() {
		$html = '';
		$html .= '<div class="clock">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_mitosis')) {
	function servicemaster_mikado_loading_spinner_mitosis() {
		$html = '';
		$html .= '<div class="mitosis">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_lines')) {
	function servicemaster_mikado_loading_spinner_lines() {
		$html = '';
		$html .= '<div class="lines">';
		$html .= '<div class="line1"></div>';
		$html .= '<div class="line2"></div>';
		$html .= '<div class="line3"></div>';
		$html .= '<div class="line4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_fussion')) {
	function servicemaster_mikado_loading_spinner_fussion() {
		$html = '';
		$html .= '<div class="fussion">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_wave_circles')) {
	function servicemaster_mikado_loading_spinner_wave_circles() {
		$html = '';
		$html .= '<div class="wave_circles">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}

if(!function_exists('servicemaster_mikado_loading_spinner_pulse_circles')) {
	function servicemaster_mikado_loading_spinner_pulse_circles() {
		$html = '';
		$html .= '<div class="pulse_circles">';
		$html .= '<div class="ball ball-1"></div>';
		$html .= '<div class="ball ball-2"></div>';
		$html .= '<div class="ball ball-3"></div>';
		$html .= '<div class="ball ball-4"></div>';
		$html .= '</div>';

		return $html;
	}
}
