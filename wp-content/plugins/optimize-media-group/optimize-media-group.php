<?php
/*
Plugin Name: Optimize Media Group
Plugin URI: http://eugenecitymap.com/my-plugin
Description: Local Google Map 
Author: Optimize Media Group
Author URI: http://eugenecitymap.com
Version: 1.0
Text Domain: optimize

--------------------------------------------------------------------------
Contributors: C Point Team, https://c-point.com

You can redistribute this plugin and/or modify this plugin
under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
--------------------------------------------------------------------------
*/

function fn_googleMaps($atts, $content = null) {
   extract(shortcode_atts(array(
      "width" => '640',
      "height" => '480',
      "src" => ''
   ), $atts));
   return '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$src.'"></iframe>';
}
add_shortcode("googlemap", "fn_googleMaps");