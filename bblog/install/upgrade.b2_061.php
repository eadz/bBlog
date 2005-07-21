<?php

if($_POST['b2_db_db']) {

$config['b2_db_db']   = $_POST['b2_db_db'];
$config['b2_db_user'] = $_POST['b2_db_user'];
$config['b2_db_pass'] = $_POST['b2_db_pass'];
$config['b2_db_host'] = $_POST['b2_db_host'];
$config['b2_db_tableprefix'] = $_POST['b2_db_tableprefix'];

}

function upgrade_from_b2_061_061_intro() {
	// things to say on the 2nd page
	echo "<p>To convert to  bBlog from b2 we just need to know the database details of your b2 install. This can be the same database as the one used for bBlog. Once the bBlog tables are created bBlog will attempt to transfer posts, comments, and categories from b2. Themes and css etc are not converted.</p>";
	echo "<p>Note: As bBlog is currently a single user blogging system, all your b2 posts will appear by the same user.</p>";
	echo "<p>The questions on the next page are for your new bBlog install, you'll be asked about your b2 install after.</p>";
}

function upgrade_from_b2_061_pre() {
	global $db,$config,$step;
	// things to do after the config is done, but before the rest.
	echo "
	<h4>b2 Database Details</h4>
	<div class='frame'>
	<li>MySQL database <input type='text' name='b2_db_db' style='text' value='".$config['mysql_database']."'/></li>
	<li>MySQL username <input type='text' name='b2_db_user' style='text' value='".$config['mysql_username']."' /></li>
	<li>MySQL password <input type='text' name='b2_db_pass' style='text' value='".$config['mysql_password']."'/></li>
	<li>MySQL hostname <input type='text' name='b2_db_host' style='text' value='".$config['mysql_host']."' /></li>
	<li>b2 table name prefix <input type='text' name='b2_db_tableprefix' style='text' value='b2' /></li>
	</ul>
	</div>
	";
   echo "<p><input type='submit' name='continue' value='Click here to continue' /></p>";
   $step = 4; // new bblog install so create tables

}

function upgrade_from_b2_061_post() {
// things to say / do after the config file is written, and the database stuff done.
// maybe append to $config['extra_config'] for extra config to be written to the config file.

// here we will actually convert the data.
	echo "<h3>Converting b2 data</h3><p>";
	global $config,$db,$EZSQL_ERROR,$step;
	$dbtxp = new db($config['b2_db_user'], $config['b2_db_pass'], $config['b2_db_db'], $config['b2_db_host']);
	echo "<h4>Converting Categories</h4>";

	$cats = array();
	$catid[] = array();
	$cats_r = $dbtxp->get_results("SELECT cat_ID, cat_name FROM ".$config['b2_db_tableprefix']."categories");

	foreach($cats_r as $cat) {
		$cats[$cat->cat_ID] = $cat->cat_name;
		$catname[$cat->cat_ID] = strtolower(str_replace(" ", "", $cat->cat_name));
	}
	// now, delete the default bBlog sections, and insert the b2 ones.
	// it seems ez_sql can't handle multi databases :(
	// sorry about this db switching, it's a bit messy. I think may be a function could do it. 
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	$db->query("DELETE FROM bB_sections");
	foreach($cats as $key=>$cat) {
		$name = $catname[$key];
		echo "Adding $cat (".$name.")<br/>";
		$db->query("INSERT INTO bB_sections SET nicename='".$cat."', name='".$name."'");
	}
	echo "Done.</p>";

	
	echo "<h4>Converting Posts</h4><p>";
	$db->query("DELETE FROM bB_posts"); // delete the sample bB post
	$db->query("DELETE FROM bB_comments"); // delete the sample bB post

	// again,it seems ez_sql can't handle multi databases :(
	$dbtxp = new db($config['b2_db_user'], $config['b2_db_pass'], $config['b2_db_db'], $config['b2_db_host']);

	$posts = $dbtxp->get_results("SELECT *, UNIX_TIMESTAMP(post_date) dateposted FROM ".$config['b2_db_tableprefix']."posts");

	foreach($posts as $post) {
		echo "Converting {$post->post_title} ...";

		// get comments.
		$dbtxp = new db($config['b2_db_user'], $config['b2_db_pass'], $config['b2_db_db'], $config['b2_db_host']);
		
		$commentsforpost = $dbtxp->get_results("SELECT *, UNIX_TIMESTAMP(comment_date) postedtimestamp FROM ".$config['b2_db_tableprefix']."comments WHERE comment_post_ID = '".$post->ID."'");

		$commentcount = 0;

		if(is_array($commentsforpost)) {
			echo " comments ... ";
			$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
			foreach($commentsforpost as $comment) {
				$db->query("INSERT INTO bB_comments SET commentid=".$comment->comment_ID.", postid='".$post->ID."', posttime='".$comment->postedtimestamp."', postername='".addslashes($comment->comment_author)."', posteremail='".$comment->comment_author_email."', posterwebsite='".$comment->comment_author_url."', pubemail=1, pubwebsite=1, ip='".$comment->comment_author_IP."', commenttext='".$comment->comment_content."'");
				$commentcount++;
			}
		}
		echo "converted (".$commentcount." comments)";
		
		// insert posts

		$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
		$catid = $db->get_var("SELECT sectionid FROM bB_sections WHERE name LIKE '".$catname[$post->post_category]."'");
		$ncat = ":".$catid.":";
		$body = str_replace("<br />", "\n", $post->post_content);

		$db->query("INSERT INTO bB_posts SET postid='".$post->ID."', title='".addslashes($post->post_title)."', body='".addslashes($body)."', posttime='".$post->dateposted."', modifytime='".$post->dateposted."', status='live', modifier='textile', sections='".$ncat."', commentcount='".$commentcount."'");


		echo "<br />";
	}
	//print_r($EZSQL_ERROR);
	//print_r($config);
	echo "All Done <br /><input type='submit' name='continue' value='Next &gt;' /></p>";
	$db = new db($config['mysql_username'], $config['mysql_password'], $config['mysql_database'], $config['mysql_host']);
	$step=7;

}
?>
