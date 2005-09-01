<?php

$directory=$_GET[directory];
$cas_dir = dir($directory);

?>
<html>
<head>
<script type="text/javascript">
function setimage(imagesrc, name)
{
    opener.document.forms['post'].elements['serverimage'].value = name;
    opener.document.imagethumb.src = imagesrc
    window.close();
}
</script>
</head>
<table class="lightbg">
<?php

$i = 0;

while($entry = $cas_dir->read())
{
    if (preg_match("/thumb/i", $entry))
    {
        if($i % 4 == 0)
        {
            echo "<tr>";
        }

        $i = $i + 1;
        $name = $entry;
        $name = preg_replace("/thumb_/i", "", $name);

        echo "<td><img src=\"$directory$entry\"><br>";
        echo "<a href=\"#\" onclick=\"setimage('pbimages/$entry', '$name'); return false;\">$name</a>";
        echo "</td>";

        if($i % 4 == 0)
        {
            echo "</tr>\n";
        }
    }
}

$cas_dir->close();

?>
</table>
</body>
</html>

