<?php
/**
 * Migrate wp-content/upload to a new location with image resizing if images are bigger than X pixels.
 *
 * @author Jonas Björk <jonas@jonasbjork.net>
 * @copyright Copyright (c) 2010-03-27, Jonas Björk
 * @version 1.0
 * @license http://ec.europa.eu/idabc/servlets/Doc?id=31979 European Union Public License v1.1
 */
define( 'ERR_NOT_JPEG',		1 );
define( 'ERR_FILE_EXISTS', 	2 ); 
define( 'CREATE_DIR_PERM', 0755 );
define( 'NEW_DIR_NAME', '/new' );
define( 'NEW_IMAGE_PREFIX', 'tn_' );
define( 'JPEG_QUALITY', 100 );
define( 'IMAGE_NEW_WIDTH', 430 );
define( 'PATH', '.' );
define( 'DEBUG', true );
define( 'DEBUG_COUNT', -1 );
if ( !function_exists( 'gd_info')  ) die( "ERROR: Could not find GD\n" );

/**
 * jlog, a log function
 *
 * @param string $msg The log message.
 * @param string $level The log level.
 * @return void
 * @author Jonas Björk
 */
function jlog( $msg, $level = LOG_INFO ) {
	switch ( $level ) {
		case LOG_INFO:
			$pre = "[INFO]: "; break;
		case LOG_WARNING:
			$pre = "[WARNING]: "; break;
		case LOG_ERR:
			$pre = "[ERROR]: "; break;
		default:
			$pre = "[LOG]: "; break;
	}
	// TODO : Should be able to write to logfile too.
	printf( "\n%s %s\n", $pre, $msg );
}

/**
 * Copy a file 
 *
 * @param string $path The original path
 * @param string $newpath The new path
 * @param string $fileName The name of the file
 * @return void
 * @author Jonas Björk
 */
function jb_copy_file( $path, $fileName ) {
	// TODO: Great error handling, with return values.
	$newpath	= jb_get_new_path( $path, $path.NEW_DIR_NAME, $fileName );
	$p = jb_split_path( $newpath );
	jb_create_dir( $p['dir'] );
	if ( !@copy( $fileName, $p['absolute'] ) ) {
		if ( DEBUG ) jlog( sprintf("Could not copy file %s to %s", $name, $p['absolute'] ), LOG_ERR );
	} else {
		// if ( DEBUG ) printf( "INFO: Copied %s to %s\n", $name, $p['absolute'] );
	}
}


/**
 * Create a directory
 *
 * @param string $dir The path to the new directory.
 * @return void
 * @author Jonas Björk
 */
function jb_create_dir( $dir ) {
	if ( !file_exists( $dir ) ) {
		mkdir( $dir, CREATE_DIR_PERM, true );
	}
}

/**
 * Resize Image
 *
 * @param string $img 
 * @param string $width 
 * @param string $newimg 
 * @return void
 * @author Jonas Björk
 */
function jb_resize_image( $img, $width, $newimg = '' ) {

	if ( $newimg == '' ) {
		$fileName = NEW_IMAGE_PREFIX.$img;
	} else {
		$fileName = $newimg;
	}
	
	if ( file_exists( $fileName ) ) return ERR_FILE_EXISTS;

	$img_size = getimagesize( $img );
	$w = $img_size[0];
	$h = $img_size[1];
	$type = $img_size['mime'];

	if ( $type == "image/jpeg" ) {
	        $nw = $width;
       		$nh = round(($nw/$w)*$h);
        	$canvas = imagecreatetruecolor( $nw, $nh );
        	$image = imagecreatefromjpeg( $img );
        	imagecopyresampled( $canvas, $image, 0, 0, 0, 0, $nw, $nh, $w, $h );
        	imagejpeg($canvas, $fileName, JPEG_QUALITY );
        	imagedestroy( $canvas );
		return 0;
        } else {
		return ERR_NOT_JPEG;
	}

}

/**
 * undocumented function
 *
 * @param string $startDir 
 * @param string $newDir 
 * @param string $str 
 * @return void
 * @author Jonas Björk
 */
function jb_get_new_path( $startDir, $newDir, $str ) {
		return str_replace( $startDir, $newDir, $str );
}

/**
 * Splits an absolute filename (incl directory) into directory and filename.
 *
 * @param string $path The path to split, should be directory and file.
 * @return array
 * @author Jonas Björk
 */
function jb_split_path( $path ) {
	// TODO: This is not optimal!
	$s['dir']				= substr( $path, 0, strlen( $path )-strpos( strrev( $path ), '/' )  );
	$s['file']			= substr( $path, strlen( $path ) - strpos( strrev( $path ), '/' ), strlen( $path ) );
	$s['absolute']	= $s['dir'].$s['file'];
	return $s;
}

// main
if ( DEBUG ) $count = 0;

$path			= realpath( PATH );
$objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ), RecursiveIteratorIterator::SELF_FIRST );

foreach( $objects as $name => $object ){
	$fileName = $name;
	// TODO : If there is a file, but it's not a JPEG? Will I copy the file then?
	if ( substr( strtolower( $name ), -3 ) == 'jpg' ) {
		$size = getimagesize( $name );
		if ( $size[0] > 430 ) {
			// printf("%s\n", $name);
			$newpath	= jb_get_new_path( $path, $path.NEW_DIR_NAME, $name );
			// printf( "==> %s\n", $newpath );
			$p = jb_split_path( $newpath );
			jb_create_dir( $p['dir'] );
			$ri = jb_resize_image( $name, IMAGE_NEW_WIDTH, $p['absolute'] );
			if ( $ri != 0 ) {
				printf( "ERROR: Could not convert image %s CODE:%d \n", $img, $ri );
			}
		}
		jb_copy_file( $path, $fileName );
	} else if ( !is_dir( $name ) ) {
		jb_copy_file( $path, $fileName );
	}
	print( '.' );
	if ( DEBUG ) {
		$count++;
		if ( $count == DEBUG_COUNT )	break;
	}
}
if ( DEBUG ) printf( "\nTotal files: %d\n", $count );

?>
