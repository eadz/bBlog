<?php

// function.externalcontent.php - 
// a Smarty function for displaying external content within bBlog 
//
// Written by Paul Balogh <javaducky@gmail.com>
//
/*
** bBlog Weblog http://www.bblog.com/
** Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function identify_function_externalcontent() {
    $help = '<p>ExternalContent is a Smarty function to be used in templates.
             <p>Usage:
             <ul>
               <li>Return content from a "registered" provider<br>
               {ExternalContent provider="some provider name"}</li>
               <li>Return content in "adhoc" manner by supplying URL<br>
               {ExternalContent url="http://provider.com/mycontent"}</li>
             </ul>';
    
    return array (
        'name'        =>'externalcontent',
        'type'        =>'function',
        'nicename'    =>'External Content',
        'description' =>'Embeds markup from the specified content provider',
        'authors'     =>'Paul Balogh <javaducky@gmail.com>',
        'licence'     =>'GPL',
        'help'        => $help
    );

}

function smarty_function_externalcontent($params, &$smartyObj) {
    $bBlog = & $smartyObj->get_template_vars("bBlog_object");

    if (isset($params['provider'])) {
        $results = $bBlog->get_results("select url, enabled from ".T_EXT_CONTENT." where nicename='".$params['provider']."'");
        $content = $results[0]->url;
        $display = $results[0]->enabled;
        
    } elseif (isset($params['url'])) {
        $content = $params['url'];
        $display = 'true';
              
    }
    // Check for enabled display
    if ($display == 'false') return '';
    
    if ($content == '') {
        return "<i>Content Missing!</i>";
    }
    
    $bytes = @readfile($content);
    if ($bytes = 0) return "<i>No Content!</i>";
    
    return '';
}

 ?>
