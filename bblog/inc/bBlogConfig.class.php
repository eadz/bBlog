<?php

/**
 * bBlog.Config.class.php - manages the config table
 *
 * @package bBlog
 * @author Kenneth Power <kenneth.power@gmail.com> - last modified by $LastChangedBy: $
 * @version $Id: $
 * @copyright 2005 Kenneth Power <kenneth.power@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

class bBlogConfig{
    function bBlogConfig(){
    }
    /**
     * Fetch configuration from database and create cosntants
     * 
     * @param object $db Instance of ezSQL, passed by reference
     */
    function loadConfiguration(&$db){
        // get config from the database
        $config_rows = $db->get_results('select name, value from '.T_CONFIG);
        // loop through and define the config
        foreach ($config_rows as $config_row) {
            $const_name = 'C_'.$config_row->name;
            if (!defined($const_name)) {
                define($const_name, $config_row->value);
            }
        }
    }
    /**
     * Save configuration to database
     * 
     * @param object $db Instance of ezSQL, passed by reference
     */
    function saveConfiguration($db){
        $rs = $db->get_results('SELECT name, value FROM `'.T_CONFIG.'` ORDER BY id ASC');
        if(!is_null($rs)){
            foreach($rs as $c){
                $name = 'frm_'.$c->name;
                if(array_key_exists($name, $_POST)){
                    $val = (get_magic_quotes_gpc()) ? trim($_POST[$name]) : my_addslashes(trim($_POST[$name]));
                    if($c->value != $val){
                        $sql = 'UPDATE `'.T_CONFIG.'` SET value="'.$val.'" WHERE name="'.$c->name.'"';
                        $db->query($sql);
                    }
                }
            }
        }
    }
    /**
     * Retrieve configuration from database in form suitable for display in the admin form
     * 
     * Returned data format:
     * index : pair
     *         left  = Label displayed on form
     *         right = HTML Input tag already prepared for display
     * @param object $db Instance of ezSQL, passed by reference
     * @return array Indexed array of pairs
     */
    function showConfigForm(&$db){
        $rows = array();
        $rs = $db->get_results('SELECT name, value, label, type, possible FROM `'.T_CONFIG.'` ORDER BY id ASC');
        if(!is_null($rs)){
            foreach($rs as $c){
                if($c->type !== ''){
                    $label = htmlspecialchars($c->label);
                    $func = 'input'.ucfirst($c->type);
                    if($c->type === 'text')
                        $field = bBlogConfig::inputText($c->name, $c->value);
                    else if($c->type === 'select'){
                        $values = bBlogConfig::getPossibleValues($c->possible, &$db);
                        $field = bBlogConfig::inputSelect($c->name, $values, $c->value);
                    }
                    $rows[] = array("left" => $label,"right" => $field);
                }
            }
        }
        return $rows;
    }
    
    /**
     * Prepares a HTML input tag for display
     * 
     * @param string $name Used to form the name attribute of the input tag
     * @param array $values Current list of possible values
     * @param string $default The default value
     */
    function inputSelect($name, $values, $default=null){
        if(is_array($values)){
            $fld = '<select name="frm_'.htmlspecialchars($name).'" class="bf">';
            foreach($values as $ind=>$val){
                $fld .='<option value="';
                //When an associative array, the index is the value
                if(is_int($ind)){
                    $fld .= htmlspecialchars($val);
                    if($val === $default)
                        $fld .= '" selected';
                }
                else{
                    $fld .= htmlspecialchars($ind);
                    if($ind === $default)
                        $fld .= '" selected';
                }
                $fld .='">'.htmlspecialchars(ucfirst($val)).'</option>';
            }
            $fld .='</select>';
            return $fld;
       }
    }
    
    /**
     * Create a text input tag for form display
     */
    function inputText($name, $value=null){
        return '<input type="text" name="frm_'.htmlspecialchars($name).'" class="bf" value="'.htmlspecialchars(trim($value)).' "/>';
    }
    function getPossibleValues($values, &$db){
        $vals ='';
        if(strpos($values, 'Array') !== false){
            $values = $values.';';
            eval("\$vals = $values");
        }
        else{
            $func = 'load'.$values.'Values';
            $vals = bBlogConfig::$func($db);
        }
        return $vals;
    }
    function loadModifierValues(&$db){
        $values = array();
        $mods = $db->get_results('select * from '.T_PLUGINS.' where type="modifier" order by id');
        if(!is_null($mods)){
            foreach($mods as $m){
                $values[$m->name] = $m->nicename;
            }
        }
        return $values;
    }
    function loadTemplateValues(){
        $values = array();
        $d = dir("templates");
        while (false !== ($entry = $d->read())) {
            if(ereg("^[a-zA-Z0-9]{1,20}$",$entry)){
                $values[] = $entry;
            }
        }
        $d->close();
        return $values;
    }
}
?>
