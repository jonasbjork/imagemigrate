Image
(c) Copyright 2010 Jonas Björk <jonas@jonasbjork.net>. All Rights Reserved.

The script is licensed under European Union Public License v1.1 < http://ec.europa.eu/idabc/servlets/Doc?id=31979 > 

This script was built for a case when I needed to migrate an entire wp-content/upload file structure to a Wordpress MU installation. All images that where over 430 px in widh broked the new design, so those images should be resized to 430px. All other images and files should remain the same (untouched).

When you run this script it will start in the directory where you are and clone all the subdirs to a new directory that will be called new (see NEW_DIR_NAME).

In the beginning of the file image.php you will find some defines, those are the ones that is used for configuration. Most important for you to change is:

NEW_DIR_NAME, that defaults to "/new". This will be the new directory created in the directory you "migrates".

NEW_IMAGE_PREFIX, is used when you're not giving the function jb_resize_image() a new filename. Instead of replacing your old file it will prefix the filename with this, defaults to "tn_" (for thumbnail actually).

JPEG_QUALITY is setting the quality of the resized JPEG image. Can be 1-100 where 100 is the best. Defaults to 100, but 75 should be enough.

IMAGE_NEW_WIDTH is the new size for images. This value is what the script looks for, if a JPEG has greater width than IMAGE_NEW_WIDTH it will be resized to this value. Default to 430 (coz that was what I needed).

PATH defines the path the script shall be using. Defaults to "." (the same path where my script is executed in). Change if you need it.

DEBUG can be true (on) or false (off). If you have DEBUG set to true the script will be more verbose.

DEBUG_COUNT sets how many files you should run in debug mode (when DEBUG is set to true). Defaults to -1 wich means "all files".

