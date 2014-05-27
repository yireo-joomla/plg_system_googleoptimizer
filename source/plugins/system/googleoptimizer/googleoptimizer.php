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

jimport( 'joomla.plugin.plugin' );

/**
 * Google Optimizer System Plugin
 *
 * @package Joomla!
 * @subpackage System
 */
class plgSystemGoogleOptimizer extends JPlugin
{
    /*
     * Google Account ID
     */
    private $_google_account_id = '';

    /*
     * Google Experiment ID
     */
    private $_google_experiment_id = '';

    /*
     * Content string
     * @deprecated
     */
    private $_content = '';

    /*
     * Section string
     * @deprecated
     */
    private $_section = '';

    /**
     * Event onAfterRender
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        // If this is the Administrator-application, or if debugging is set, do nothing
        $application = JFactory::getApplication();
        if($application->isAdmin() || JDEBUG) {
            return;
        }

        // Fetch the document buffer
        $buffer = JResponse::getBody();
        
        // Prepare the plugin parameters
        $pluginParams = $this->getParams();

        // If the plugin is disabled, just clean-up the plugin-tags
        if(JPluginHelper::isEnabled('system', 'googleoptimizer') == false || $pluginParams->get('remove_tags') == 1) {
            $buffer = $this->clean($buffer);
            JResponse::setBody($buffer);
            return false;
        }

        // Set the default values
        $this->_google_account_id = $pluginParams->get( 'google_account_id' );
        $this->_google_experiment_id = $pluginParams->get( 'google_experiment_id' );

        // Replace the plugin-tags with the Google Optimizer scripts
        $buffer = $this->render($buffer);
        JResponse::setBody($buffer);
        return true;
    }

    /**
     * Method to render the plugin-tags into a Google Optimizer script
     *
     * @access private
     * @param null
     * @return null
     */
    private function render($buffer) 
    {
        if(preg_match_all('/\{go([^\}]*)\}([^\{]+)\{\/go\}|\{go([^\}]*)\}/', $buffer, $matches)) {
            for($i = 0; $i <= count($matches); $i++) {

                if(!empty($matches[0][$i])) {

                    if( !empty($matches[3][$i] )) {
                        $arguments = $this->parseArguments($matches[3][$i]);
                    } else {
                        $arguments = $this->parseArguments($matches[1][$i]);
                    }

                    if(isset( $arguments['section'])) {

                        $this->_section = $arguments['section'];
                        $this->_content = $matches[2][$i] ;
                        $buffer = str_replace( $matches[0][$i], $this->getScript('section'), $buffer );

                    } elseif(empty( $arguments['type'])) {

                        $this->setError( 'Unknown type specified.' );
                        $buffer = str_replace( $matches[0][$i], '', $buffer );

                    } else {

                        $this->setAny( '_google_account_id', $arguments, array( 'account', 'account_id' ));
                        $this->setAny( '_google_experiment_id', $arguments, array( 'tracker', 'tracker_id', 'track_id' ));

                        $script = trim($this->getScript($arguments['type']));
                        $buffer = str_replace($matches[0][$i], '', $buffer);
                        $buffer = str_replace('<head>', '<head>'."\n".$script, $buffer);
                    }
                }
            }
        }
        return $buffer;
    }

    /**
     * Method to render the plugin-tags into an empty string
     *
     * @access private
     * @param null
     * @return null
     */
    private function clean($buffer) 
    {
        $buffer = preg_replace('/\{go([^\}]*)\}/', '', $buffer);
        return $buffer;
    }

    /**
     * Method to split the plugin-tags arguments into an array
     *
     * @access private
     * @param null
     * @return null
     */
    private function parseArguments($string) 
    {
        // Initialize
        $arguments = array();

        // Parse the string
        $args = explode(' ', trim($string));
        if(!empty($args)) {
            foreach($args as $arg) {
                $array = explode( '=', $arg );
                if(!empty($array[0]) && !empty($array[1])) {
                    $name = $array[0];
                    $value = $array[1];
                    $value = str_replace( '\'', '', $value);
                    $value = str_replace( '"', '', $value);
                    $arguments[$name] = $value;
                }
            }
        }

        // Return the arguments
        return $arguments;
    }

    /**
     * Method to get the Google Account ID
     *
     * @access private
     * @param null
     * @return null
     */
    private function getAccountId() 
    {
        if(empty($this->_google_account_id)) {
            $this->setError('You must specify your Google Account ID.');
        } elseif(preg_match('/^UA-/', $this->_google_account_id) == false) {
            $this->setError('A Google Account ID should start with "UA-".');
        } 
        return $this->_google_account_id;
    }
    
    /**
     * Method to get the Google Experiment Tracker ID
     *
     * @access private
     * @param null
     * @return null
     */
    private function getExperimentId() 
    {
        if(empty($this->_google_experiment_id)) {
            $this->setError('You must specify your Google Experiment Tracking ID.');
        } elseif(is_numeric($this->_google_experiment_id) == false) {
            $this->setError('A Google Tracker ID should contain only numbers.');
        }
        return $this->_google_experiment_id;
    }
    
    /**
     * Method to get the proper Google Optimizer script
     *
     * @access private
     * @param null
     * @return null
     */
    private function getScript($type)
    {
        // Prepare the plugin parameters
        $pluginParams = $this->getParams();

        // Prepare the output
        $output = null;

        // Check for the right type
        $types = array( 
            'control' => 'control',
            'track' => 'tracking',
            'tracker' => 'tracking',
            'tracking' => 'tracking',
            'conversion' => 'conversion',
            'convert' => 'conversion',
            'section' => 'section',
        );
        if( !array_key_exists( $type, $types )) {
            $this->setError( 'Unknown type specified.' );
            return null;
        } else {
            $type = $types[$type];
        }

        // Read the variables needed to insert the script
        $account_id = $this->getAccountId();
        $experiment_id = $this->getExperimentId();
        $section = $this->_section;
        $content = $this->_content;

        // Make sure this script is loaded only once
        static $flags = array();
        if(array_key_exists( $type, $flags)) {
            return null;
        } else {
            if($type == 'section') {
                $flags[$section] = $type;
            } else {
                $flags[$type] = $type;
            }
        }

        // Load the template
        $template = dirname(__FILE__).DS.'googleoptimizer'.DS.'script.'.$type.'.php';
        if(is_file($template) == false) {
            $template = dirname(__FILE__).DS.'tmpl'.DS.'script.'.$type.'.php';
        }

        if($pluginParams->get('html_type') == 'html5') {
            $script_start = '<script>';
            $script_end = '</script>';
        } else {
            $script_start = '<script type="text/javascript">'."\n".'<!--//--><![CDATA[//><!--';
            $script_end = '//--><!]]>'."\n".'</script>';
        }

        if(is_file($template)) {
            ob_start();
            include $template;
            $output = ob_get_contents();
            ob_end_clean();
        }

        // Add the tracker-script to the control-script
        if($type == 'control') {
            $output .= $this->getScript('tracking');
        }

        // Return the output
        return $output;
    }

    /**
     * Method to set an error
     *
     * @access private
     * @param null
     * @return null
     */
    public function setError($message = '') 
    {
        JError::raise(E_NOTICE, 'Whoops', 'Google Optimizer: '.JText::_($message));
        return true;
    }

    /**
     * Method to set any argument
     *
     * @access private
     * @param string $name
     * @param array $arguments
     * @param array $options
     * @return null
     */
    private function setAny($name, $arguments, $options) 
    {
        foreach($options as $option) {
            if(!empty($arguments[$option])) {
                $this->$name = $arguments[$option];
                return true;
            }
        }
        return false;
    }

    /**
     * Load the parameters
     *
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        jimport('joomla.version');
        $version = new JVersion();
        if(version_compare($version->RELEASE, '1.5', 'eq')) {
            $plugin = JPluginHelper::getPlugin('system', 'googleoptimizer');
            $params = new JParameter($plugin->params);
            return $params;
        } else {
            return $this->params;
        }
    }
}
