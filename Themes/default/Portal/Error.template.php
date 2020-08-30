<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file Error.template.php
 * Error template.
 *
 * @version 1.41
 */

function template_main()
{
	global $context, $txt, $scripturl;

	echo '
		<div id="fatal_error">
			<div class="cat_bar">
				<h3 class="catbg">'. $context['pmx_error_title'] .'</h3>
			</div>
			<div class="windowbg" style="margin-top:0;border-top-left-radius:0;border-top-right-radius:0">
				<div id="portamx_error" class="padding">'. $context['pmx_error_text'] .'</div>

				<br class="clear" />
				<div class="centertext">
					<input class="button_submit" style="float:none; margin:0 auto;font-weight:bold;" type="button" name="back" value="&nbsp;&nbsp;'. $txt['page_reqerror_button'] .'&nbsp;&nbsp;" onclick="window.location.href=\''. $scripturl .'\'" />
				</div>
			</div>
		</div>';
}
?>