<?php
/**
 * modifier.date_format.php - smarty modifier to format a timestamp
 * <p>
 * @copyright Copyright (C) 2003  Eaden McKee <email@eadz.co.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 * @package bblog
 */
 
function smarty_modifier_date_format($date, $format="F j, Y, g:i a") {
  if($date < 1 ) return '';

  switch ($format) {
    case "full": return strftime("%A, %d. %B %Y, %H:%M", $date);
    
    case "date": return strftime("%A, %d. %B %Y", $date);
		
		case "europe": return strftime("%d.%m.%Y", $date);
    
    case "shortdate": return strftime("%x", $date);

    case "month": return strftime("%B", $date);

    case "year": return strftime("%Y", $date);

    case "monthyear": return strftime("%B %Y", $date);

    case "time": return strftime("%H:%M", $date);

    case "s1" : return date("F j, Y, g:i a",$date);
    
    case "s2" : return date("F j, Y",$date);

    case "atom" : return date('Y-m-d\TH:i:s\Z',$date);

    case "rss20" : return strftime("%a, %d %b %Y %H:%M:%S %Z", $date);

    case "rss92" : return gmdate('D, d M Y H:i:s',$date) . ' GMT';

    // a clever little hack to make date() return a ISO 8601 standard date string for use in RSS 1.0
    case "rss10" : return substr(date("Y-m-d\Th:i:sO", $date),0,22).":".substr(date("O", $date),3);
    
    // called Jim in reference to RevJim ( revjim.net ) who first used this format ( afict )
    case "jim" : return since($date)." on ".date("F j, Y",$date); 
    
    case "since" : return since($date);

  }

  return date($format,$date);
}

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

function formatsince($sum1,$desc1,$sum2,$desc2){
    if($sum1 == 1 && $sum2 == 0){
        $diff = "1 $desc1";
    }elseif($sum1 == 1 && $sum2 == 1){
        $diff = "1 $desc1, 1 $desc2";
    }elseif($sum1 == 1 && $sum2 > 1){
        $diff = "1 $desc1, $sum2 {$desc2}s";    
    }elseif($sum1 >1 && $sum2 == 1){
        $diff = "$sum1 {$desc1}s, 1 $desc2";
    }elseif($sum1 > 1 && $sum2 > 1){
        $diff = "$sum1 {$desc1}s, $sum2 {$desc2}s";
    }else{
        return false;
    }
    
    return $diff;    
   
}
        
function since($tstamp){
    $seconds = time() - $tstamp;

    $minutes = intval($seconds/60);
    $seconds = $seconds % 60;

    $hours = intval($minutes/60);
    $minutes = $minutes % 60;

    $days = intval($hours/24);
    $hours = $hours % 24;

    $weeks = intval($days/7);
    $days = $days % 7;
    
    $months = intval($weeks/4);
    $weeks = $weeks % 4;
    
    $years = intval($months/12);
    $months = $months % 12;
    
    if($diff = formatsince($years,"year",$months,"month")){
    
    }elseif($diff = formatsince($months,"month",$days,"day")){
    
    }elseif($diff = formatsince($weeks,"week",$days,"day")){
    
    }elseif($diff = formatsince($days,"day",$hours,"hour")){
    
    }elseif($diff = formatsince($hours,"hour",$minutes,"minute")){
    
    }elseif($diff = formatsince($minutes,"minute",$seconds,"second")){
        
    }else{
        $diff = "some seconds";
    }   
    return "Posted ".$diff. " ago";
}
        
?>
