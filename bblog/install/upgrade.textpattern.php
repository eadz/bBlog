<?php

if($_POST['textpattern_db_db']) {

$config['textpattern_db_db']   = $_POST['textpattern_db_db'];
$config['textpattern_db_user'] = $_POST['textpattern_db_user'];
$config['textpattern_db_pass'] = $_POST['textpattern_db_pass'];
$config['textpattern_db_host'] = $_POST['textpattern_db_host'];

}

function upgrade_from_textpattern_intro() {
	// things to say on the 2nd page
	echo "<p>To convert to  bBlog from textpattern we just need to know the database details of your textpattern install. This can be the same database as the one used for bBlog. Once the bBlog tables are created bBlog will attempt to transfer posts, comments, and categories from textpattern. Themes and css etc are not converted.
	</p><p>The questions on the next page are for your new bBlog install, you'll be asked about your textpattern install after.</p>";
}

function upgrade_from_textpattern_pre() {
	global $db,$config,$step;
	// things to do after the config is done, but before the rest.
	echo "
	<h4>Textpattern Database Details</h4>
	<div class='frame'>
	<li>MySQL database <input type='text' name='textpattern_db_db' style='text' value='".$config['mysql_database']."'/></li>
	<li>MySQL username <input type='text' name='textpattern_db_user' style='text' value='".$config['mysql_username']."' /></li>
	<li>MySQL password <input type='text' name='textpattern_db_pass' style='text' value='".$config['mysql_password']."'/></li>
	<li>MySQL hostname <input type='text' name='textpattern_db_host' style='text' value='".$config['mysql_host']."' /></li>
	</ul>
	</div>
	";
   echo "<p><input type='submit' name='continue' value='Click here to continue' /></p>";
   $step = 4; // new bblog install so create tables

}

function upgrade_from_textpattern_post() {
// things to say / do after the config file is written, and the database stuff done.
// maybe append to $config['extra_config'] for extra config to be written to the config file.

// here we will actually convert the data.
	echo "<h3>Converting textpattern data</h3><p>";
	global $config,$db,$EZSQL_ERROR,$step;
	$dbtxp = new db($config['textpattern_db_user'], $config['textpattern_db_pass'], $config['textpattern_db_db'], $config['textpattern_db_host']);
	echo "<h4>Converting Categories</h4>";
	// this may seen funny, but er, so is textpatterns way of storing categories ( cat1 and cat2 )

	$cats = array();
	$cats_r = $dbtxp->get_results("select Category1,Category2 from textpattern ");

	foreach($cats_r as $cat) {
		if($cat->Category1 != '' && !$gotcats[$cat->Category1]) $cats[] = $cat->Category1;
		if($cat->Category2 != '' && !$gotcats[$cat->Category2]) $cats[] = $cat->Category2;
		$gotcats[$cat->Category1] = TRUE;
		$gotcats[$cat->Category2] = TRUE;
	}
	// now, delete the default bBlog sections, and insert the textpattern ones.
	// it seems ez_sql can't handle multi databases :(
	// sorry about this db switching, it's a bit messy. I think may be a function could do it. 
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	$db->query("delete from bB_sections");
	foreach($cats as $cat) {
		echo "Adding $cat <br/>";
		$db->query("insert into bB_sections set nicename='$cat', name='$cat'");
	}
	echo "Done.</p>";
	echo "<h4>Converting Posts</h4><p>";
	$db->query("delete from bB_posts"); // delete the sample bB post

	// again,it seems ez_sql can't handle multi databases :(
	$dbtxp = new db($config['textpattern_db_user'], $config['textpattern_db_pass'], $config['textpattern_db_db'], $config['textpattern_db_host']);

	$posts = $dbtxp->get_results("select *, UNIX_TIMESTAMP(Posted) dateposted, UNIX_TIMESTAMP(LastMod) lastmodified from textpattern");
	foreach($posts as $post) {
		echo "Converting {$post->title} ...";
		$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
		if($post->Category1 != '') {
			$catid = $db->get_var("select sectionid from bB_sections where name like '{$post->Category1}'");
			$ncat = ":$catid:";

		}
		if($post->Category2 != '') {
			$catid = $db->get_var("select sectionid from bB_sections where name like '{$post->Category1}'");
			if($post->Category1 != '') $ncat .= "$catid:";
			else $ncat = ":$catid:";

		}

		if($post->Status == 4) $status='live';
		else $status='draft';

		$db->query("insert into bB_posts set postid='{$post->ID}', title='".addslashes($post->Title)."', body='".addslashes($post->Body)."', posttime='".$post->dateposted."', modifytime='".$post->lastmodified."', status='$status', modifier='textile', sections='$ncat', commentcount='0'");

		// get comments.
		$dbtxp = new db($config['textpattern_db_user'], $config['textpattern_db_pass'], $config['textpattern_db_db'], $config['textpattern_db_host']);
		$commentsforpost = $dbtxp->get_results("select *,UNIX_TIMESTAMP(posted) postedtimestamp from txp_Discuss where parentid='{$post->ID}'");
		if(is_array($commentsforpost)) {
			echo " comments ... ";
			$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
			foreach($commentsforpost as $comment) {

				$db->query("insert into bB_comments set commentid='{$comment->discussid}', postid='{$comment->parentid}', posttime='{$comment->postedtimestamp}', postername='".addslashes($comment->name)."', posteremail='".$comment->email."', posterwebsite='".$comment->web."', pubemail=1, pubwebsite=1, ip='{$comment->ip}', commenttext='".addslashes($comment->message)."'");

			}
		}

		echo "<br />";
	}
	//print_r($EZSQL_ERROR);
	//print_r($config);
	echo "All Done <br /><input type='submit' name='continue' value='Next &gt;' /></p>";
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	$step=7;

}
?>
