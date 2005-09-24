<?php
/**
 * @package SmartBee
 * @author Martin Konicek - <martin_konicek@centrum.cz>
 * @copyright Copyright (c)2004, Martin Konicek
 * @link http://smartbee.sourceforge.net/
 * @license <http://opensource.org/licenses/lgpl-license.php> GNU Lesser General Public License
 * @license You have to add link to this library to Credits on project homepage
 * @license Link for this library have to be ".T_SEARCH."able
 * @todo boolean ".T_SEARCH." +word -word
 */
 
/** Examples
 * <strong>Create index of all data</strong>
 * <code>
 * $search->index_all();
 * </code>
 *
 * <strong>Create index of one item</strong>
 * <code>
 * $search->index($id);
 * </code>
 *
 * <strong>Search query</strong>
 * <code>
 * print_r($search->article_query('Root.cz'));
 * </code>
 */

/** How to install
 * $search->install();
 * $search->clanky_install();
 * $search->index_all();
 */
 
/** How to modify script
 * function changeItem($id, ...) {
 * ...
 * $search->index($id);
 * }
 */
 
/** Requirements
 * PHP 4.3
 * Mysql 3 or MySQL 4 (recommended)
 */

// CLASS
class search {
/**
 * settings for MySQL transaction support
 * for MySQL4 and above recommended true
 * for MySQL3 recommended false
 * @var boolean
 */
	var $trans = true;
	
/** Standartize text, for example "S*O*M*E" to "some"
 * @param string $text - text for standartize
 * @return string
 */
	function replace($text){
		return preg_replace('/[^a-z A-Z]/','',strtolower(czech::autoCzech($text,'asc')));
	}

/** Create searchable items database
 * @param string $col - column of $this->table
 * @param string $score - priority of column (higher for titles, lower for content)
 * @param integer $id - id of item
 * @return boolean - always true
 */
	function add_results($col,$score,$id){
		global $bBlog;
		$sql = "
			SELECT `postid`,`".$col."`
			FROM `".$this->table."`
			WHERE 1";
		if(!empty($id)){
			$sql .= " AND `postid` = '".$id."'";
		}
		$ress = $bBlog->db->get_results($sql);
	 
	 if($this->trans){
		 $bBlog->db->query("START TRANSACTION;");
	 }
	 
	 // cycle result rows
	 foreach ($ress as $res){
		 $key = $res->postid;
		 // separate string into words
		 $values = explode(' ', preg_replace('/[\[[^\[]*]|<[^<]*>]|\n|\r/',' ',$res->$col));
		 // add each word for ID
		 foreach($values as $value){
			 $value = $this->replace($value);
			 // only words between 2 and 50 characters
			 if(strlen($value) > 2 && strlen($value) < 50){
				 $this->_add($key, $value, $score);
			 }
		 }
	 }

	 if($this->trans){
		 $bBlog->db->query("COMMIT;");
	 }
	 return true;
	}

/** Delete search database or ".T_SEARCH." items
 * @param $id - items to delete (0 = all items)
 * @return boolean - result of deleting database
 */
	function del_results($id = 0){
		global $bBlog;
		if(empty($id)){
			$sql = "TRUNCATE TABLE `".T_SEARCH."`";
		} else {
			$sql = "
			DELETE
			FROM `".T_SEARCH."`
			WHERE `article_id` = '".$id."'";
		}
		return $bBlog->db->query($sql);
	}

/** Add word to search database
 * @param string $goods_id
 * @param string $value
 * @param integer $score
 * @return boolean
 */
	function _add($goods_id, $value, $score){
		global $bBlog;
		return $bBlog->db->query("
			INSERT INTO `".T_SEARCH."` ( `id` , `article_id` , `value` , `score` )
			VALUES (
			'0', '".$goods_id."', '".$value."', '".$score."'
			);");
	}

/** search for one word
 * @param string $string
 * @return boolean
 */
	function _search_tmp($string){
		global $bBlog;
		return $bBlog->db->query("
			INSERT INTO `".T_SEARCH_TMP."` (`article_id` , `points`, `string`, `time` )
			SELECT `article_id`, SUM(`score`) AS `points`, '".$string."', NOW()
			FROM `".T_SEARCH."`
			WHERE `value` LIKE '".$string."'
			GROUP BY id
			ORDER BY points DESC;
		");
	}

/** Clear temporary results
 * @param integer $time - Time in seconds for hold cache
 * @return boolean
 */
	function _clear_tmp($time = 1800){
		global $bBlog;
		return $bBlog->db->query("
			DELETE
			FROM `".T_SEARCH_TMP."`
			WHERE `time`+".$time." < NOW();
		");
	}
 
/** Check for temporary search results
 * @param string $string
 * @return boolean
 */
	function _check_tmp($string){
		global $bBlog;
		return is_null(
			$bBlog->db->get_var("SELECT `id` FROM `".T_SEARCH_TMP."` WHERE `string`='".$string."'")
		);
	}
	
/** search for string and save string to temporary results
 * @param string $string
 * @return boolean
 */
	function make_tmp($string){
		$this->_clear_tmp();
		if($this->_check_tmp($string)){
			$this->_search_tmp($string);
		}
		return true;
	}

/** search for query, for example "word word word"
 * @param string $query
 * @return array results
 */
	function search_query($query){
		global $bBlog;
		$query = $this->replace($query);
		$words = explode(' ', $query);
	 
		$sql = '';
		foreach ($words as $word){
			$this->make_tmp($word);
			$sql .= " OR `string` = '".$word."'";
		}
		return $bBlog->db->get_results("
	 	SELECT 
			`article_id` AS `id`,
			SUM(points) AS points_sum
		FROM `".T_SEARCH_TMP."`
		WHERE 0 ".$sql."
		GROUP BY `id`
		ORDER BY `points_sum` DESC
		LIMIT 20;
		");
	}

/** Create search index for all items
 * @return boolean
 */
	function index_all(){
		$this->_clear_tmp(0);
		$this->del_results();
		return $this->index(0,true);
	}
}

// PERSONALISED CLASS
class article_search extends search {
/**
 * Name of the table where we search
 * @var string
 */
	var $table = T_POSTS;
	
	function index($id, $full = false){
		if(!empty($id) || $full){
			$this->del_results($id);
			// === CHANGE IT ===
			$this->add_results('title',3,$id);
			$this->add_results('body',1,$id);
			// === CHANGE IT ===
			return true;
		} else {
			return false;
		}
	}
	
	function records_count(){
		global $bBlog;
		return $bBlog->db->get_var("
			SELECT COUNT(*) AS `count` FROM `".T_SEARCH."`");
	}
 
 
 function article_query($query,$where = ''){
	global $bBlog;
	$query = $this->replace($query);
	$words = explode(' ', $query);
	
	$sql = '';
	foreach ($words as $word){
	 $this->make_tmp($word);
	 $sql .= " OR `string` = '".$word."'";
	}
	$sql .= $where;
	// === CHANGE IT ===
	return $bBlog->db->get_results("
		SELECT
			".T_SEARCH_TMP.".article_id AS `id` ,
			SUM( ".T_SEARCH_TMP.".points ) AS `points_sum` ,
			posts.*,
			authors.nickname,
			authors.email,
			authors.fullname
		FROM `".T_SEARCH_TMP."`
		LEFT JOIN
			`".$this->table."` as `posts`
			ON posts.postid = ".T_SEARCH_TMP.".article_id
		LEFT JOIN
			`".T_AUTHORS."` as `authors`
			ON posts.ownerid = authors.id
		WHERE
			0
			".$sql."
			AND posts.status like 'live'
		GROUP BY ".T_SEARCH_TMP.".id
		ORDER BY points_sum DESC
		LIMIT 20
	");
	// === CHANGE IT ===
 }
}
?>