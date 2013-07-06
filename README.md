Tumblr Tag Cloud
================
### A simple PHP app that serves a cached tag cloud for your Tumblr

[**(See it in action on my blog!)**][3]

Introduction
------------
This app solves a single problem: Tumblr doesn't provide a native way to show a
tag cloud on its blogs. There is a [popular JavaScript embed][1] by Heather
Rivers which generates a tag cloud, but because this is done on the client-
side it comes with a huge drawback: tag cloud generation must be done on every
page load, and for larger blogs it is very slow.

This Tumblr Tag Cloud app generates and caches tag clouds on your web server,
allowing your Tumblr blog to pull in pre-formed HTML via a simple JavaScript
embed. This can drastically reduce page loading times on larger blogs and
reduces strain on Tumblr's API. The app provides an intuitive admin interface,
which makes it easy to configure your tag cloud and get the embed code.

This app is distributed under the MIT License and is free software.


Requirements
------------
* A web hosting server with PHP, MySQL and optionally Memcache or APC
* A subdomain to host the app on
* A few minutes of your time to install


Installation
------------
1. Create or choose a MySQL database to hold the app's data. (By default,
tables are prefixed with 'cloud_' so you can choose an existing database
without fear of it being gummed up with weird new tables everywhere.)

2. Place the application files on your web server such that your subdomain's
public web directory is hosting the contents of the app's webroot/ directory,
and the other application directories such as controllers/ and models/ live in
the parent directory of your web directory. Note that your web directory
does not actually have to be named webroot, it just needs to serve the files
contained in this project's webroot/ directory.

3. Copy the configuration file template from includes/local.default.php to
includes/local.php and edit the file to specify your local configuration. The
file is heavily commented up, so it should be pretty straightforward, _unless
you're a n00b_, in which case contact me and I'll help you and fix the docs.

4. Go to the subdomain you set the app up on and it should walk you through
the database installation process. You're good to go!


Contributing
------------
Please let me know if you have any suggestions for improvements. If you're code
savvy, fork the project and make the change yourself! I will do my best to help
if something doesn't work or isn't clear. You can find me on Twitter
@rubbingalcohol

[1]: http://rive.rs/projects/tumblr-tag-clouds
[2]: https://github.com/lyonbros/a-frame
[3]: http://blog.rubbingalcoholic.com
