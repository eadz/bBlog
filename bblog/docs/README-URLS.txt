NOTE: You only need to read this if you want your URLS to look like : 
www.example.com/blog/post/1/ not www.example.com/blog/index.php?postid=1

bBlog supports Clean urls. However, not all servers support this. ( it's a AllowOverride setting in apache ). 


Rename htaccess-cleanurls to .htaccess. 
Edit config.php and uncomment the 3 cleanurl config lines. 

You may also need to edit the CSS / image links in the templates to make them absolute. 


