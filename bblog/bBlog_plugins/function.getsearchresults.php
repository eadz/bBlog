<?php
/**
 * function.getsearchresults.php
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */ 

function identify_function_getsearchresults()

{

	$help = '<p>the {getsearchresults} function is used to retrieve a listing of results for a certain search term.<br />

		It takes the following parameters:<br />

		<br />

		assign: variable to assign data to<br />

		string: what we are searching for<br />

		num: the number of entries to return<br />

		skip: the number of entries to skip<br />

		section: to request results from one section<br />

		trim: amount of characters to show before cutting off result preview';



	return array (

		'name'			=> 'getsearchresults',

		'type'			=> 'function',

		'nicename'		=> 'GetSearchResults',

		'description'	=> 'Search your blog!',

		'authors'		=> 'Chris Boulton',

		'license'		=> 'GPL',

		'help'			=> $help

	);

}





function smarty_function_getsearchresults($params, &$smartyObj)

{

	

	$bBlog = & $smartyObj->get_template_vars("bBlog_object");



	$ar = array();

	$opt = array();

	

	// need to handle multiple keywords!!!

	

	$keywords_ar = preg_split('/[^\w]/',$params['string']);

	if(count($keywords_ar) > 1) {

		$kw1 = array_shift($keywords_ar); // takes [0] off the array

		$sql = "AND ( posts.body like '%$kw1%' OR posts.title LIKE '%$kw1%' ";

		foreach($keywords_ar as $kw) {

			$sql .= " OR posts.body like '%$kw%' OR posts.title LIKE '%$kw%' ";

		}

		$sql .= " ) ";

	} else {

        	$search = "'%" . $params['string'] . "%'";

		$sql = "AND ( posts.body LIKE " . $search . " OR posts.title LIKE " . $search . ") ";

	}

	$opt['where'] = $sql;

	// If "assign" is not set... we'll establish a default.

	if($params['assign'] == '') {

		$params['assign'] = 'posts';

	}



	// If num is set, we'll only get that many results in return

	if(is_numeric($params['num'])) {

		$opt['num'] = $params['num'];

	}



	// If skip is set, we'll skip that many results

	if(is_numeric($params['skip'])) {

		$opt['skip'] = $params['skip'];

	}

     

	if ($params['section'] != '') {

		  $opt['sectionid'] = $bBlog->sect_by_name[$params['section']];

	}

	if($params['skipsection'] != '') {

		$opt['skipsection'] = $bBlog->sect_by_name[$params['skipsection']];

	}



	if($params['trim'] == '') {

		$params['trim'] = '200';

	}

	

	if($bBlog->show_section) {

		$opt['sectionid'] = $bBlog->show_section;

	}





	$q = $bBlog->make_post_query($opt);

//die($q);

	$ar['posts'] = $bBlog->get_posts($q);

        

	// No posts.

	if(!is_array($ar['posts'])) {

		return '';

	  }



	$lastmonth = 0;

	$lastdate = 0;



	//$stringlen = strlen($params['string']);



	foreach($ar['posts'] as $key => $value) {

		// It seems silly to do this. Especially since,

		// this kind of check can be done in Smarty template.

		// Additionally, since {newday} and {newmonth} require

		// the data to be in a variable named "post" it may not

		// function at all.

		//

		// We'll leave it here for now.

		

		/* check if new day  - used by block.newday.php */

		if(date('Ymd',$ar['posts'][$key]['posttime']) != $lastdate) {

			$ar['posts'][$key]['newday'] = TRUE;

		}

		$lastdate = date('Ymd',$ar['posts'][$key]['posttime']);



		/* check if new month - use by block.newmonth.php */

		if(date('Fy',$ar['posts'][$key]['posttime']) != $lastmonth) {

			$ar['posts'][$key]['newmonth'] = TRUE;

		}

		$lastmonth = date('Fy',$ar['posts'][$key]['posttime']);



		// Trim the message to get us a preview :-D

		/* 

		if($params['trim']) {

			if(strlen($ar['posts'][$key]['body']) > $params['trim']) {

				$ar['posts'][$key]['preview'] = substr($ar['posts'][$key]['body'], 0, $params['trim'])."...";

				

			} else {

				$ar['posts'][$key]['preview'] = $ar['posts'][$key]['body']; 

			}

		}

		*/

		$_snippit = "";

		

		$_txt = strip_tags($ar['posts'][$key]['body']);

		$keywords_ar = preg_split('/[^\w]/',$params['string']);

		

		$matches = stripos_words($_txt,$params['string'],false);

		if($matches) {

			foreach($matches as $match) {

				$_snippit .= "..." 

					.substr($_txt,$match['start']-10,$match['end']+10)

					."...";

			}

		} else { // something went wrong!  OR keyword was found in title only.

			$_snippit = substr($_txt, 0, $params['trim'])."...";

			//echo "SOMETHING WENT WRONG!";

		

		}



		foreach($keywords_ar as $kw) $_snippit = str_ireplace("$kw","<b><i>$kw</i></b>",$_snippit);

		

		$ar['posts'][$key]['preview'] = $_snippit;

		

		$_title = "";

		foreach($keywords_ar as $kw) $_title =

			str_ireplace("$kw","<b><i>$kw</i></b>",$ar['posts'][$key]['title']);

		$ar['posts'][$key]['title'] = $_title;

		

	}

	

	

	

	$smartyObj->assign($params['assign'],$ar['posts']);

	return '';

}





// from comments on http://nz.php.net/strpos . SCORE!

function stripos_words($haystack,$needles='',$pos_as_key=true)

{

   $idx=0; // Used if pos_as_key is false

  

   // Convert full text to lower case to make this case insensitive

   $haystack = strtolower($haystack);

  

   // Split keywords and lowercase them

   foreach ( preg_split('/[^\w]/',strtolower($needles)) as $needle )

   {

       // Get all occurences of this keyword

       $i=0; $pos_cur=0; $pos_found=0;

       while (  $pos_found !== false && $needles !== '')

       {

           // Get the strpos of this keyword (if thereis one)

           $pos_found = strpos(substr($haystack,$pos_cur),$needle);

           if ( $pos_found !== false )

           {

               // Set up key for main array

               $index = $pos_as_key ? $pos_found+$pos_cur : $idx++;

              

               // Populate main array with this keywords positional data

               $positions[$index]['start'] = $pos_found+$pos_cur;

               $pos_cur += ($pos_found+strlen($needle));

               $positions[$index]['end']  = $pos_cur;

               $positions[$index]['word'] = $needle;

               $i++;

           }

       }

   }



   // If we found anything then sort the array and return it

   if ( isset($positions) )

   {

       ksort($positions);

       return $positions;

   }



   // If nothign was found then return false

   return false;

} 



/* vim: set expandtab tabstop=4 shiftwidth=4: */

// +----------------------------------------------------------------------+

// | PHP Version 4                                                        |

// +----------------------------------------------------------------------+

// | Copyright (c) 1997-2004 The PHP Group                                |

// +----------------------------------------------------------------------+

// | This source file is subject to version 3.0 of the PHP license,       |

// | that is bundled with this package in the file LICENSE, and is        |

// | available at through the world-wide-web at                           |

// | http://www.php.net/license/3_0.txt.                                  |

// | If you did not receive a copy of the PHP license and are unable to   |

// | obtain it through the world-wide-web, please send a note to          |

// | license@php.net so we can mail you a copy immediately.               |

// +----------------------------------------------------------------------+

// | Authors: Aidan Lister <aidan@php.net>                                |

// +----------------------------------------------------------------------+

//

// $Id: function.getsearchresults.php,v 1.7 2005/02/19 11:22:14 martin_konicek Exp $

//





/**

 * Replace str_ireplace()

 *

 * @category    PHP

 * @package     PHP_Compat

 * @link        http://php.net/function.str_ireplace

 * @author      Aidan Lister <aidan@php.net>

 * @version     $Revision: 1.7 $

 * @since       PHP 5

 * @internal    count not by returned by reference - not possible in php4

 * @require     PHP 4.0.1 (trigger_error)

 */









if (!function_exists('str_ireplace'))

{

    function str_ireplace ($search, $replace, $subject, $count = null)

    {

        if (is_string($search) && is_array($replace)) {

            trigger_error('Array to string conversion', E_USER_NOTICE);

            $replace = (string) $replace;

        }



        // If search isn't an array, make it one

        if (!is_array($search)) {

            $search = array ($search);

        }

    

        // If replace isn't an array, make it one, and pad it to the length of search

        if (!is_array($replace))

        {

            $replace_string = $replace;



            $replace = array ();

            for ($i = 0, $c = count($search); $i < $c; $i++)

            {

                $replace[$i] = $replace_string;

            }

        }



        // Check the replace array is padded to the correct length

        $length_replace = count($replace);

        $length_search = count($search);

        if ($length_replace < $length_search)

        {

            for ($i = $length_replace; $i < $length_search; $i++)

            {

                $replace[$i] = '';

            }

        }



        // If subject is not an array, make it one

        $was_array = false;

        if (!is_array($subject)) {

            $was_array = true;

            $subject = array ($subject);

        }



        // Loop through each subject

        $count = 0;

        foreach ($subject as $subject_key => $subject_value)

        {

            // Loop through each search

            foreach ($search as $search_key => $search_value)

            {

                // Split the array into segments, in between each part is our search

                $segments = explode(strtolower($search_value), strtolower($subject_value));



                // The number of replacements done is the number of segments minus the first

                $count += count($segments) - 1;

                $pos = 0;



                // Loop through each segment

                foreach ($segments as $segment_key => $segment_value)

                {

                    // Replace the lowercase segments with the upper case versions

                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));

                    // Increase the position relative to the initial string

                    $pos += strlen($segment_value) + strlen($search_value);

                }

                

                // Put our original string back together

                $subject_value = implode($replace[$search_key], $segments);

            }



            $result[$subject_key] = $subject_value;

        }



        // Check if subject was initially a string and return it as a string

        if ($was_array === true) {

            return $result[0];

        }



        // Otherwise, just return the array

        return $result;

    }

}



?>

