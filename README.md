fakemai
=======

Fakemai is a php based emulator for Akamai's temporary directory stuff. If you are ever stuck needing to deploy some code and Akamai can't meet your delivery dates because the cache is going to take too long to change or you're doing a new deployment, use this until they are ready and swap out the CNAME records.

Installation
============

1. Make sure you have .htaccess files working in apache.
2. Edit the config file, and move it to config.php.
3. Put your files into the directory specified in the config file.
4. That should do it! the genurl.php file will gen you some test urls.

