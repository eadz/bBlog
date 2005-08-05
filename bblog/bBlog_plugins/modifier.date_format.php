<?php
/**
 * modifier.date_format.php - smarty modifier to format a timestamp
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @copyright 2005 Kenneth Power <kenneth.power@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */

/**
* Modified a timestamp according to one of several definitions
*
* Uses the PHP strftime() and date() functions
*
* The formats available are:
* <dl>
*   <dt>full</dt>
*       <dd>Format as: Weekday, day. Month Year, Hour:Minute.
*           <strong>Example</strong>: Wednesday, 03. August 2005, 14:05
*       </dd>
*   <dt>date</dt>
*       <dd>Format as Wednesday, 03 August 2005</dd>
*   <dt>europe</dt>
*       <dd>Format as 03.08.2005</dd>
*   <dt>shortdate</dt>
*       <dd>Preferred date representation for the current locale without the time </dd>
*   <dt>month</dt>
*       <dd>Format as: August</dd>
*   <dt>year</dt>
*       <dd>Format as: 2005</dd>
*   <dt>monthyear</dt>
*       <dd>Format as: August 2005</dd>
*   <dt>time</dt>
*       <dd>Format as: 14:05</dd>
*   <dt>12hour</dt>
*       <dd>Format as: 2:05 PM</dd>
*   <dt>s1</dt>
*       <dd>Format as: August 3, 2005, 2:05 pm</dd>
*   <dt>s2</dt>
*       <dd>Format as: August 3, 2005</dd>
*   <dt>atom</dt>
*       <dd>Format as: 2005-08-03EST 14:05:13 -18000</dd>
*   <dt>rss20</dt>
*       <dd>Format as: Wed, 03 Aug 2005 14:05:13 EST</dd>
*   <dt>rss92</dt>
*       <dd>Format as: Aug, 03 Aug 2005 14:05:13 EST</dd>
*   <dt>suffix</dt>
*       <dd>Provide the English ordinal suffix for the day of the month, 2 characters.
*           <em>st</em>, <em>nd</em>, <em>rd</em> or <em>th</em>
*       </dd>
*   <dt>rss10</dt>
*       <dd>substr(date("Y-m-d\Th:i:sO", $date),0,22).":".substr(date("O", $date),3)</dd>
*   <dt>elapsed</dt>
*       <dd>Displays the year(s), month(s), day(s), hour(s), minute(s) and second(s) between $date and now</dd>
*   <dt>default</dt>
*       <dd></dd>
* </dl>
*
* @param integer $date The time stamp to modify
* @param string $format The format requested
* @return string
*/
function smarty_modifier_date_format($date, $format="%F %j, %Y, %g:%i %a") {
  if($date < 1 ) return '';
  // locale should be defined in a config file, not case by case
  define('C_LOCALE','en_GB');
  setlocale(LC_TIME,C_LOCALE);
  
  switch ($format) {
    case "full": return strftime("%A, %d. %B %Y, %H:%M", $date);
 		break;
    
    case "date": return strftime("%A, %d. %B %Y", $date);
		break;
    
    case "europe": return strftime("%d.%m.%Y", $date);
    	break;
    
    case "shortdate": return strftime("%x", $date);
		break;
    
    case "month": return strftime("%B", $date);
		break;
    
    case "year": return strftime("%Y", $date);
		break;
    
    case "monthyear": return strftime("%B %Y", $date);
		break;
    
    case "time": return strftime("%H:%M", $date);
		break;
	
    case "12hour": return strftime("%I:%M %p", $date);
        break;
        
    case "s1" : return strftime("%B %d, %Y %r", $date);
		break;
    
    case "s2" : return strftime("%B %d, %Y", $date);
		break;
    
    case "atom" : return strftime("%Y-%b-%d\%Z %T"); //date('Y-m-d\TH:i:s\Z',$date);
		break;
    
    case "rss20" : return strftime("%a, %d %b %Y %H:%M:%S %Z", $date);
		break;
    
    case "rss92" : return strftime("%a, %d %b %Y %H:%M:%S %Z", $date);
		break;
    
    case "suffix" : return date("S", $date);
		break;
    
    // a clever little hack to make date() return a ISO 8601 standard date string for use in RSS 1.0
    case "rss10" : return substr(date("Y-m-d\Th:i:sO", $date),0,22).":".substr(date("O", $date),3);
 		break;
    
    case "elapsed" :
        //return since($date)." on ".date("F j, Y",$date); 
        return time_diff($date, time());
		break;
  
  	default:
 		//default should behave like the original smarty date_format
   		//see if there is at least one % in the date. then we go for new format
  		if (substr_count("$format", '%') > 0) {
  			return strftime($format, $date); 	
  		}
		//else we go the old date() way for backward compatibility
  		else{
  			return date($format, $date);
  		}
  		break;
  }//switch
 
}//function

function identify_modifier_date_format () {
  return array (
    'name'           =>'date_format',
    'type'           =>'smarty_modifier',
    'nicename'       =>'Date Format',
    'description'    =>'Date format takes a timestamp, and turns it into a nice looking date',
    'authors'         =>'Dean Allen, Eaden McKee, Tobias Schlottke',
    'licence'         =>'Textpattern'
  );
}

function bblog_modifier_date_format_help () {
?>
<p>Date format takes a timestamp, and turns it into a nice looking date.
<br />It is used as a modifier inside a template. For example, if you are in a
 <span class="tag">{post} {/post}</span> loop, you will have the varible {$post.dateposted}
 set which will contain a timestamp of when the post was made,
 and you will apply the date_format modifier to this tag.</p>
<p>Examples :<br />
<span class="tag">{$post.dateposted|date_format}</span> will return a date like May 26, 2003, 2:29 pm<br />
<span class="tag">{$post.dateposted|date_format:since}</span> will return Posted 7 hours, 3 minutes ago<br />
<span class="tag">{$post.dateposted|date_format:"F j, Y"}</span> will return May 26, 2003. The "F j, Y" is in php date() format, for more infomation see <a href="http://www.php.net/date">php.net/date</a></p>


<?php
}

function time_diff($from, $to) {
	if ($from > $to) {
		$t = $to;
		$to = $from;
		$from = $t;
	}
	$year1 = date("Y", $from);
	$year2 = date("Y", $to);
	$month1 = date("n", $from);
	$month2 = date("n", $to);
	$day1 = date("j", $from);
	$day2 = date("j", $to);
    $hour1 = date("H", $from);
    $hour2 = date("H", $to);
    $minute1 = date("i", $from);
    $minute2 = date("i", $to);
    $second1 = date("s", $from);
    $second2 = date("s", $to);
	
    
    /**
    * Make adjustments to days and months
    *
    * Distinguish among 30-day,31-day,28-day and 29-day months
    */
	if ($day2 < $day1) {
		if (($month2 - 1) == 4 || ($month2 - 1) == 6 || ($month2 - 1) == 9 || ($month2 - 1) == 11) {
			$day2 += 30;
			$month2 --;
		} else if (($month2 - 1) == 2) {
			if (date("L", $to)) {
				$day2 += 29;
				$month2 --;
			} else {
				$day2 += 28;
				$month2 --;
			}
		} else {
			$day2 += 31;
			$month2 --;
		}
	}
    //Finally, the difference between the two days
	$days = $day2 - $day1;

    //Find difference between the months
	if ($month2 < $month1) {
		$month2 += 12;
		$year2--;
	}
	$months = $month2 - $month1;
	$years = $year2 - $year1;
    
    if($hour2 < $hour1){
        $hour2 += 24;
        $days--;
    }
    $hours = $hour2 - $hour1;
    
    if($minute2 < $minute1){
        $minute2 += 60;
        $hours--;
    }
    $minutes = $minute2 - $minute1;
    
    if($second2 < $second1){
        $second2 += 60;
        $minutes--;
    }
    $seconds = $second2 - $second1;
    
    $result = '';
    if($years > 0)
        $result .= ($years> 1) ? $years.' years, ' : $years.' year, ';
    if($months > 0)
        $result .= ($months > 1) ? $months.' months, ' : $months.' month, ';
    if($days > 0)
        $result .= ($days > 1) ? $days.' days, ' : $days.' day, ';
    if($hours > 0)
        $result .= ($hours > 1) ? $hours.' hours, ' : $hours.' hour, ';
    if($minutes > 0)
        $result .= ($minutes > 1) ? $minutes.' minutes, ' : $minutes.' minute, ';
    if($seconds > 0)
        $result .= ($seconds > 1) ? $seconds.' seconds' : $seconds.' second';
        
    $result = trim($result);
    $pos = strpos($result, ',', strlen($result) -1);
    if($pos !== false)
        $result = substr($result, 0, $pos - 1);
    
	//return "$years years, $months months, $days days, $hours hours, $minutes minutes, $seconds seconds";
    return $result;
}
?>
