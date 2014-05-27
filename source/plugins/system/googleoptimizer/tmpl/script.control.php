<?php
/**
 * Joomla! System plugin - Google Website Optimizer A/B
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2012 Yireo.com. All rights reserved
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!-- Google Website Optimizer Control Script -->
<?php echo $script_start; ?>
function utmx_section(){}function utmx(){}(function(){var k='<?php echo $experiment_id; ?>',d=document,l=d.location,c=d.cookie;function f(n){if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.indexOf(';',i);return escape(c.substring(i+n.length+1,j<0?c.length:j))}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;d.write('<sc'+'ript src="'+'http'+(l.protocol=='https:'?'s://ssl':'://www')+'.google-analytics.com'+'/siteopt.js?v=1&utmxkey='+k+'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='+new Date().valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+'" type="text/javascript" charset="utf-8"></sc'+'ript>')})();
<?php echo $script_end; ?>
<?php echo $script_start; ?>
utmx("url",'A/B');
<?php echo $script_end; ?>
<!-- End of Google Website Optimizer Control Script -->
