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
<?php echo $script_start; ?>
utmx_section("<?php echo $section; ?>")
<?php echo $script_end; ?>
