<?php

class bBlogConfig{
    function bBlogConfig(){
    }
    function loadConfiguration(&$db){
        // get config from the database
        $config_rows = $db->get_results('select name, value from '.T_CONFIG);
        // loop through and define the config
        foreach ($config_rows as $config_row) {
            $const_name = 'C_'.$config_row->name;
            if (!defined($const_name)) {
                define($const_name, stripslashes($config_row->value));
            }
        }
    }
    function saveConfiguration($db){
        $rs = $db->get_results('SELECT name, value FROM `'.T_CONFIG.'` ORDER BY id ASC');
        if(!is_null($rs)){
            foreach($rs as $c){
                $name = 'frm_'.$c->name;
                if(array_key_exists($name, $_POST)){
                    $val = (get_magic_quotes_gpc()) ? trim($_POST[$name]) : addslashes(trim($_POST[$name]));
                    if($c->value != $val){
                        $sql = 'UPDATE `'.T_CONFIG.'` SET value="'.$val.'" WHERE name="'.$c->name.'"';
                        $db->query($sql);
                    }
                }
            }
        }
    }
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