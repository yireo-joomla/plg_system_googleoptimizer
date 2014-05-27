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
<!-- Google Website Optimizer Tracking Script -->
<?php echo $script_start; ?>
var _gaq = _gaq || [];
_gaq.push(['gwo._setAccount', '<?php echo $account_id; ?>']);
_gaq.push(['gwo._trackPageview', '/<?php echo $experiment_id; ?>/goal']);
(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
<?php echo $script_end; ?>
<!-- End of Google Website Optimizer Tracking Script -->
