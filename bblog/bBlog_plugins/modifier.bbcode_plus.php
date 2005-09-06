<?php

/**
* bBlog text formatting plugin
*
* Converts bbcode style tags to html and makes urls clickable. It also
* highlights code snippets according to the geshi library via the following
* syntax additions to the bbcode standard:
*
* [code=language]your code here[/code]
*
* supported languages are:
*   actionscript
*   actionscript-french
*   ada
*   apache
*   applescript
*   asm
*   asp
*   bash
*   c
*   c_mac
*   caddcl
*   cadlisp
*   cpp
*   csharp
*   css
*   css-gen
*   d
*   delphi
*   diff
*   div
*   dos
*   eiffel
*   freebasic
*   gml
*   html4strict
*   ini
*   inno
*   java
*   javascript
*   lisp
*   lua
*   matlab
*   mpasm
*   nsis
*   objc
*   oobas
*   oracle8
*   pascal
*   perl
*   php
*   php-brief
*   python
*   qbasic
*   sdlbasic
*   smarty
*   sql
*   vb
*   vbnet
*   vhdl
*   visualfoxpro
*   xml
*
* @package bblog
*/

//	Libraries
$library_dir    = dirname(__FILE__).'/geshi/';
require_once($library_dir.'geshi.php');

/**
* need a description
*/
function identify_modifier_bbcode_plus ()
{
    $help = bblog_modifier_bbcode_plus_help();
    return array (
        'name'          =>'bbcode_plus',
        'type'          =>'modifier',
        'nicename'      =>'BBCode+',
        'description'   =>'Converts BBCode style tags to HTML and makes URLs clickable (plus support for language specific syntax highlighting and more)',
        'authors'       =>'Toby Miller',
        'licence'       =>'GPL',
        'help'          => $help
    );
}

/**
* need a description
*/
function bblog_modifier_bbcode_plus_help ()
{
    // help copied from phpbb (C) the PHPBB GROUP and released under the GPL
    // then modified to include geshi language support
    ob_start();
?>
<h4>Introduction</h4>

<a href="#jump0">What is BBCode?</a><br /><br />

<h4>Text Formatting</h4>
<a href="#jump1">How to create bold, italic and underlined text</a><br />
<a href="#jump2">How to change the font face, text color or size</a><br />
<a href="#jump3">How to change the text alignment</a><br/>
<a href="#jump4">Can I combine formatting tags?</a><br />

<h4>Outputting fixed-width text</h4>

<a href="#jump5">Outputting code or fixed width data</a><br />

<h4>Generating lists</h4>

<a href="#jump6">Creating an Un-ordered list</a><br />
<a href="#jump7">Creating an Ordered list</a><br />

<h4>Creating Links</h4>

<a href="#jump8">Linking to another site</a><br />

<h4>Showing images in posts</h4>

<a href="#jump9">Adding an image to a post</a><br />

<br /><br />

<h4>Introduction</h4>

<p>
    <a name="jump0"></a><b>What is BBCode?</b><br />
    BBCode is a special implementation of HTML. You enable or disable BBCode on
    a per post basis via the posting form. BBCode itself is similar in style to
    HTML, tags are enclosed in square braces [ and ] rather than &lt; and &gt;
    and it offers greater control over what and how something is displayed.
    <del>Depending on the template you are using you may find adding BBCode to
    your posts is made much easier through a clickable interface above the
    message area on the posting form.</del> Even with this you may find the
    following guide useful.<br />
    <a href="#Top">Back to top</a>
</p>

<h4>Text Formatting</h4>

<a name="jump1"></a><b>How to create bold, italic and underlined text</b><br />
BBCode includes tags to allow you to quickly change the basic style of your
text. This is achieved in the following ways:
<ul>
    <li>
        To make a piece of text bold enclose it in <b>[b][/b]</b>, eg.<br />
        <br />
        <b>[b]</b>Hello<b>[/b]</b><br />
        <br />
        will become <b>Hello</b>
    </li>
    <li>
        For underlining use <b>[u][/u]</b>, for example:<br />
        <br />
        <b>[u]</b> Good Morning<b>[/u]</b><br />
        <br />
        becomes <u>Good Morning</u>
    </li>
    <li>
        To italicise text use <b>[i][/i]</b>, eg.<br />
        <br />
        This is <b>[i]</b>Great!<b>[/i]</b><br />
        <br />
        would give This is <i>Great!</i>
    </li>
</ul><br />
<a href="#Top">Back to top</a>
<br/><br/>

<a name="jump2"></a><b>How to change the font face, text color or size</b><br />
To alter the font face, color or size of your text the following tags can be
used. Keep in mind that how the output appears will depend on the viewers
browser and system:
<ul>
    <li>
        Changing the font face is achieved by wrapping it in
        <b>[font=][/font]</b>. You can specify either a specific font name
        or a comma separated list of fonts to be applied in order of
        availability. So your value could be "Verdana" or
        "Verdana,Arial,Helvetica,sans-serif". Here are some examples:<br />
        <br />
        <b>[font=Verdana]</b>Hello!<b>[/font]</b><br />
        <br />
        or<br />
        <br />
        <b>[font="Verdana,Arial,Helvetica,sans-serif"]</b>Hello!<b>[/font]</b><br />
        <br />
        The second example will output <span style="font-family:Arial,Helvetica,sans-serif;">Hello!</span>
    </li>
    <li>
        Changing the color of text is achieved by wrapping it in
        <b>[color=][/color]</b>. You can specify either a recognised color
        name (eg. red, blue, yellow, etc.) or the hexadecimal triplet
        alternative, eg. #FFFFFF, #000000. For example, to create red text
        you could use:<br />
        <br />
        <b>[color=red]</b>Hello!<b>[/color]</b><br />
        <br />
        or
        <br />
        <br />
        <b>[color=#FF0000]</b>Hello!<b>[/color]</b><br />
        <br />
        will both output <span style="color:red">Hello!</span>
    </li>
    <li>
        Changing the text size is achieved in a similar way using
        <b>[size=][/size]</b>. This tag is dependent on the template you are
        using but the recommended format is a numerical value representing
        the text size in pixels, starting at 1 (so tiny you will not see it)
        through to 29 (very large). For example:<br />
        <br />
        <b>[size=9]</b>SMALL<b>[/size]</b><br />
        <br />
        will generally be <span style="font-size:9px">SMALL</span><br />
        <br />
        whereas:<br />
        <br />
        <b>[size=24]</b>HUGE!<b>[/size]</b><br />
        <br />
        will be <span style="font-size:24px">HUGE!</span>
    </li>
</ul><br />
<a href="#Top" >Back to top</a>
<br/><br/>

<a name="jump3"></a><b>How to change the text alignment</b><br />
To alter the text alignment of your text the following tags can be used.
Keep in mind that how the output appears will depend on the viewers browser
and system:
<ul>
    <li>
        Changing the text alignment is achieved by wrapping it in
        <b>[align=][/align]</b> or <b>[left][/left]</b> or
        <b>[center][/center]</b> or <b>[right][/right]</b>. So you can
        either specify the traditional align tag or you can use one of the
        shortcut tags. Here are some valid options:<br />
        <br />
        <b>[align=left]</b>Hello!<b>[/align]</b><br />
        <br />
        or<br />
        <br />
        <b>[align=center]</b>Hello!<b>[/align]</b><br />
        <br />
        or<br />
        <br />
        <b>[align=right]</b>Hello!<b>[/align]</b><br />
        <br />
        or<br />
        <br />
        <b>[left]</b>Hello!<b>[/left]</b><br />
        <br />
        or<br />
        <br />
        <b>[center]</b>Hello!<b>[/center]</b><br />
        <br />
        or<br />
        <br />
        <b>[right]</b>Hello!<b>[/right]</b>
    </li>
    <li>
        Changing the color of text is achieved by wrapping it in
        <b>[color=][/color]</b>. You can specify either a recognised color
        name (eg. red, blue, yellow, etc.) or the hexadecimal triplet
        alternative, eg. #FFFFFF, #000000. For example, to create red text
        you could use:<br />
        <br />
        <b>[color=red]</b>Hello!<b>[/color]</b><br />
        <br />
        or
        <br />
        <br />
        <b>[color=#FF0000]</b>Hello!<b>[/color]</b><br />
        <br />
        will both output <span style="color:red">Hello!</span>
    </li>
    <li>
        Changing the text size is achieved in a similar way using
        <b>[size=][/size]</b>. This tag is dependent on the template you are
        using but the recommended format is a numerical value representing
        the text size in pixels, starting at 1 (so tiny you will not see it)
        through to 29 (very large). For example:<br />
        <br />
        <b>[size=9]</b>SMALL<b>[/size]</b><br />
        <br />
        will generally be <span style="font-size:9px">SMALL</span><br />
        <br />
        whereas:<br />
        <br />
        <b>[size=24]</b>HUGE!<b>[/size]</b><br />
        <br />
        will be <span style="font-size:24px">HUGE!</span>
    </li>
</ul><br />
<a href="#Top" >Back to top</a>
<br/><br/>

<a name="jump4"></a><b>Can I combine formatting tags?</b><br />
Yes, of course you can, for example to get someones attention you may
write:<br />
<br />
<b>[size=18][color=red][b]</b>LOOK AT ME!<b>[/b][/color][/size]</b><br />
<br />
this would output
<span style="color:red;font-size:18px"><b>LOOK AT ME!</b></span><br />
<br />
We don't recommend you output lots of text that looks like this though!
Remember it is up to you, the poster to ensure tags are closed correctly.
For example the following is incorrect:<br />
<br />
<b>[b][u]</b>This is wrong<b>[/b][/u]</b><br />
<a href="#Top" class="gensmall">Back to top</a>
<br/><br/>

<h4>Outputting fixed-width text</h4>

<a name="jump5"></a><b>Outputting code or fixed width data</b><br />
If you want to output a piece of code or in fact anything that requires a
fixed width, eg. Courier type font you should enclose the text in
<b>[code][/code]</b> tags, eg.<br />
<br />
<b>[code]</b>echo "This is some code";<b>[/code]</b><br />
<br />
All formatting used within <b>[code][/code]</b> tags is retained when you
later view it.<br />
<br/>
If you're outputting code and you know what language that code was written
in you can use the Geshi highlighter to add color to your code. All you have
to do is include the language as <b>[code=language][/code]</b>, e.g.<br/>
<br/>
<b>[code=javascript]</b>$myvar = "Hello!";<b>[/code]</b><br/>
<br/>
this would output
<span style="color: #0000ff;">$myvar</span> = <span style="color: #ff0000;">"Hello!"</span>;<br/>
<br/>
Valid options as of this writing are:<br/>
<table cellpadding="2" cellspacing="0" border="1">
    <tr>
        <td>actionscript</td>
        <td>actionscript-french</td>
        <td>ada</td>
    </tr>
    <tr>
        <td>apache</td>
        <td>applescript</td>
        <td>asm</td>
    </tr>
    <tr>
        <td>asp</td>
        <td>bash</td>
        <td>c</td>
    </tr>
    <tr>
        <td>c_mac</td>
        <td>caddcl</td>
        <td>cadlisp</td>
    </tr>
    <tr>
        <td>cpp</td>
        <td>csharp</td>
        <td>css</td>
    </tr>
    <tr>
        <td>css-gen</td>
        <td>d</td>
        <td>delphi</td>
    </tr>
    <tr>
        <td>diff</td>
        <td>div</td>
        <td>dos</td>
    </tr>
    <tr>
        <td>eiffel</td>
        <td>freebasic</td>
        <td>gml</td>
    </tr>
    <tr>
        <td>html4strict</td>
        <td>ini</td>
        <td>inno</td>
    </tr>
    <tr>
        <td>java</td>
        <td>javascript</td>
        <td>lisp</td>
    </tr>
    <tr>
        <td>lua</td>
        <td>matlab</td>
        <td>mpasm</td>
    </tr>
    <tr>
        <td>nsis</td>
        <td>objc</td>
        <td>oobas</td>
    </tr>
    <tr>
        <td>oracle8</td>
        <td>pascal</td>
        <td>perl</td>
    </tr>
    <tr>
        <td>php</td>
        <td>php-brief</td>
        <td>python</td>
    </tr>
    <tr>
        <td>qbasic</td>
        <td>sdlbasic</td>
        <td>smarty</td>
    </tr>
    <tr>
        <td>sql</td>
        <td>vb</td>
        <td>vbnet</td>
    </tr>
    <tr>
        <td>vhdl</td>
        <td>visualfoxpro</td>
        <td>xml</td>
    </tr>
</table><br/>
<a href="#Top">Back to top</a>
<br/><br/>

<h4>Generating lists</h4>

<a name="jump6"></a><b>Creating an Un-ordered list</b><br />
BBCode supports two types of lists, unordered and ordered. They are
essentially the same as their HTML equivalents. An unordered list ouputs
each item in your list sequentially one after the other indenting each with
a bullet character. To create an unordered list you use <b>[list][/list]</b>
and define each item within the list using <b>[*]</b>. For example to list
your favorite colours you could use:<br />
<br />
<b>[list]</b><br />
<b>[*]</b>Red<br />
<b>[*]</b>Blue<br />
<b>[*]</b>Yellow<br />
<b>[/list]</b><br />
<br />
This would generate the following list:
<ul>
    <li>Red</li>
    <li>Blue</li>
    <li>Yellow</li>
</ul><br />
<a href="#Top">Back to top</a>
<br/><br/>

<a name="jump7"></a><b>Creating an Ordered list</b><br />
The second type of list, an ordered list gives you control over what is
output before each item. To create an ordered list you use
<b>[list=1][/list]</b> to create a numbered list or alternatively
<b>[list=a][/list]</b> for an alphabetical list. As with the unordered list
items are specified using <b>[*]</b>. For example:<br />
<br />
<b>[list=1]</b><br />
<b>[*]</b>Go to the shops<br />
<b>[*]</b>Buy a new computer<br />
<b>[*]</b>Swear at computer when it crashes<br />
<b>[/list]</b><br />
<br />
will generate the following:
<ol type="1">
    <li>Go to the shops</li>
    <li>Buy a new computer</li>
    <li>Swear at computer when it crashes</li>
</ol>
Whereas for an alphabetical list you would use:<br />
<br />
<b>[list=a]</b><br />
<b>[*]</b>The first possible answer<br />
<b>[*]</b>The second possible answer<br />
<b>[*]</b>The third possible answer<br />
<b>[/list]</b><br />
<br />
giving
<ol type="a">
    <li>The first possible answer</li>
    <li>The second possible answer</li>
    <li>The third possible answer</li>
</ol><br />
<a href="#Top">Back to top</a>
<br/><br/>

<h4>Creating Links</h4>

<a name="jump8"></a><b>Linking to another site</b> BBCode supports a number of
ways of creating URIs, Uniform Resource Indicators better known as URLs.
<ul>
    <li>
        The first of these uses the <b>[url=][/url]</b> tag, whatever you
        type after the = sign will cause the contents of that tag to act as
        a URL. For example to link to phpBB.com you could use:<br />
        <br />
        <b>[url=http://www.phpbb.com/]</b>Visit phpBB!<b>[/url]</b><br />
        <br />
        This would generate the following link,
        <a href="http://www.phpbb.com/" target="_blank">Visit phpBB!</a>
        You will notice the link opens in a new window so the user can
        continue browsing the forums if they wish.
    </li>
    <li>
        If you want the URL itself displayed as the link you can do this by
        simply using:<br />
        <br />
        <b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br />
        <br />
        This would generate the following link,
        <a href="http://www.phpbb.com/" target="_blank">http://www.phpbb.com/</a>
    </li>
    <li>
        Additionally phpBB features something called <i>Magic Links</i>,
        this will turn any syntatically correct URL into a link without you
        needing to specify any tags or even the leading http://. For example
        typing www.phpbb.com into your message will automatically lead to
        <a href="http://www.phpbb.com/" target="_blank">www.phpbb.com</a>
        being output when you view the message.
    </li>
    <li>
        The same thing applies equally to email addresses, you can either
        specify an address explicitly for example:<br />
        <br />
        <b>[email]</b>no.one@domain.adr<b>[/email]</b><br />
        <br />
        which will output
        <a href="emailto:no.one@domain.adr">no.one@domain.adr</a> or you can
        just type no.one@domain.adr into your message and it will be
        automatically converted when you view.
    </li>
</ul>
As with all the BBCode tags you can wrap URLs around any of the other tags
such as <b>[img][/img]</b> (see next entry), <b>[b][/b]</b>, etc. As with
the formatting tags it is up to you to ensure the correct open and close
order is following, for example:<br />
<br />
<b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br />
<br />
is <u>not</u> correct which may lead to your post being deleted so take care.<br />
<a href="#Top">Back to top</a>
<br/><br/>

<h4>Showing images in posts</h4>

<a name="jump9"></a><b>Adding an image to a post</b><br />
BBCode incorporates a tag for including images in your posts. Two very
important things to remember when using this tag are; many users do not
appreciate lots of images being shown in posts and secondly the image you
display must already be available on the internet (it cannot exist only on
your computer for example, unless you run a webserver!). There is currently
no way of storing images locally with phpBB (all these issues are expected
to be addressed in the next release of phpBB). To display an image you must
surround the URL pointing to the image with <b>[img][/img]</b> tags. For
example:<br />
<br />
<b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br />
<br />
As noted in the URL section above you can wrap an image in a
<b>[url][/url]</b> tag if you wish, eg.<br />
<br />
<b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br />
<br />
would generate:<br />
<br />
<a href="http://www.phpbb.com/" target="_blank"><img src="http://www.phpbb.com/images/phplogo.gif" border="0" alt="" /></a><br />
<br />
<a href="#Top" >Back to top</a>
<br/><br/>
<?php
    $o = ob_get_contents();
    ob_end_clean();
    return $o;
}

/**
* need description
*/
function smarty_modifier_bbcode_plus($message)
{
    $language_dir = dirname(__FILE__).'/geshi/geshi/';

    $preg = array(
        // [code=language]???[/code]
        // <div>highlighted code:<div class=language>???</div></div>
        '/(?<!\\\\)\[code(?::\w+)?=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/code\]/sie' => "'<div>highlighted code:'.geshi_highlight(stripslashes('\\2'),'\\1','" . $language_dir . "',true).'</div>'",

        // [code]???[/code]
        // <div>code:<code>???</code></div>
        '/(?<!\\\\)(\[code(?::\w+)?\])(.*?)(\[\/code(?::\w+)?\])/sie' => "'<div>code:<code>'.htmlspecialchars('\\2').'</code></div>'",

        // [b]???[/b]
        // <b>???</b>
        '/(?<!\\\\)\[b(?::\w+)?\](.*?)\[\/b(?::\w+)?\]/si' => "<b>\\1</b>",

        // [u]???[/u]
        // <u>???</u>
        '/(?<!\\\\)\[u(?::\w+)?\](.*?)\[\/u(?::\w+)?\]/si' => "<u>\\1</u>",

        // [i]???[/i]
        // <i>???</i>
        '/(?<!\\\\)\[i(?::\w+)?\](.*?)\[\/i(?::\w+)?\]/si' => "<i>\\1</i>",

        // [color=red]???[/color]
        // <span style="color:red;">???</span>
        '/(?<!\\\\)\[color(?::\w+)?=(.*?)\](.*?)\[\/color(?::\w+)?\]/si' => "<span style=\"color:\\1;\">\\2</span>",

        // [size=10]???[/size]
        // <span style="font-size:10;">???</span>
        '/(?<!\\\\)\[size(?::\w+)?=(.*?)\](.*?)\[\/size(?::\w+)?\]/si' => "<span style=\"font-size:\\1;\">\\2</span>",

        // [font=fontname]???[/font]
        // <span style="font-family:fontname;">???</span>
        '/(?<!\\\\)\[font(?::\w+)?=(.*?)\](.*?)\[\/font(?::\w+)?\]/si' => "<span style=\"font-family:\\1;\">\\2</span>",

        // [align=direction]???[/align]
        // <div style="text-align:direction;">???</div>
        '/(?<!\\\\)\[align(?::\w+)?=(.*?)\](.*?)\[\/align(?::\w+)?\]/si' => "<div style=\"text-align:\\1;\">\\2</div>",

        // [left]???[/left]
        // <div style="text-align:left;">???</div>
        '/(?<!\\\\)\[left(?::\w+)?\](.*?)\[\/left(?::\w+)?\]/si' => "<div style=\"text-align:left;\">\\1</div>",

        // [center]???[/center]
        // <div style="text-align:center;">???</div>
        '/(?<!\\\\)\[center(?::\w+)?\](.*?)\[\/center(?::\w+)?\]/si' => "<div style=\"text-align:center;\">\\1</div>",

        // [right]???[/right]
        // <div style="text-align:right;">???</div>
        '/(?<!\\\\)\[right(?::\w+)?\](.*?)\[\/right(?::\w+)?\]/si' => "<div style=\"text-align:right;\">\\1</div>",

        // [quote]???[/quote]
        // <div class="quote">Quote:<blockquote>???</blockquote></div>
        '/(?<!\\\\)\[quote(?::\w+)?\](.*?)\[\/quote(?::\w+)?\]/si' => "<div class=\"quote\">Quote:<blockquote>\\1</blockquote></div>",

        // [quote=author]???[/quote]
        // <div class="quote">author wrote:<blockquote>???</blockquote></div>
        '/(?<!\\\\)\[quote(?::\w+)?=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/quote\]/si' => "<div class=\"quote\">\\1 wrote:<blockquote>\\2</blockquote></div>",

        // [list][*]???[*]???[/list]
        // <ul><li>???</li><li>???</li></ul>
        '/(?<!\\\\)\[list(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/sie' => "'<ul>'.preg_replace('/\[\*\]/','</li><li>',preg_replace('/^\[\*\]/','<li>',preg_replace('/[\r\n]+/','','\\1'))).'</li></ul>'",

        // [list=a|1][*]???[*]???[/list]
        // <ol type="a|1"><li>???</li><li>???</li></ul>
        '/(?<!\\\\)\[list(?::\w+)?=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/list\]/sie' => "'<ol type=".'"'."\\1".'"'.">'.preg_replace('/\[\*\]/','</li><li>',preg_replace('/^\[\*\]/','<li>',preg_replace('/[\r\n]+/','','\\2'))).'</li></ol>'",

        // [url]link[/url]
        // <a href="link">link</a>
        '/(?<!\\\\)\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]/si' => "<a href=\"http://www.\\1\" target=\"_blank\">\\1</a>",
        '/(?<!\\\\)\[url(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'      => "<a href=\"\\1\" target=\"_blank\">\\1</a>",

        // [url=link]title[/url]
        // <a href="link">title</a>
        '/(?<!\\\\)\[url(?::\w+)?=(.*?)?\](.*?)\[\/url(?::\w+)?\]/si' => "<a href=\"\\1\" target=\"_blank\">\\2</a>",

        // standalone link
        // <a href="link">link</a>
        '/(\s+)(https*\:\/\/[a-zA-Z0-9\-\.]+\.?[a-zA-Z]{2,4}(\/\S*)?)(\s+)/si'  => "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>\\4",
        '/(\s+)(www\.[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,4})(\s+)/si'                  => "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>\\3",
        '/(\s+)([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,4}(\/\S*)?)(\s+)/si'              => "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>\\4",

        // [email]address[/email]
        // <a href="mailto:address">address</a>
        '/(?<!\\\\)\[email(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si' => "<a href=\"mailto:\\1\">\\1</a>",

        // [email=address]title[/email]
        // <a href="mailto:address">title</a>
        '/(?<!\\\\)\[email(?::\w+)?=(.*?)\](.*?)\[\/email(?::\w+)?\]/si' => "<a href=\"mailto:\\1\">\\2</a>",

        // standalone email
        // <a href="mailto:email">email</a>
        '/(\s+)(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6})(\s+)/si' => "\\1<a href=\"mailto:\\2\">\\2</a>\\5",

        // [img]url[/img]
        // <img src="url" alt="url"/>
        '/(?<!\\\\)\[img(?::\w+)?\](.*?)\[\/img(?::\w+)?\]/si' => "<img src=\"\\1\" alt=\"\\1\"/>",

        // [img=WxH]url[/img]
        // <img width="W" height="H" src="url" alt="url"/>
        '/(?<!\\\\)\[img(?::\w+)?=(.*?)x(.*?)\](.*?)\[\/img(?::\w+)?\]/si' => "<img src=\"\\3\" width=\"\\1\" height=\"\\2\" alt=\"\\3\"/>",

        // beginning spaces
        '/^( +)/sie' => "preg_replace('\\1','&nbsp;')"
    );
    $message = preg_replace(array_keys($preg), array_values($preg), $message);
    $message = nl2br($message);
    return $message;
}
?>
