// Adds filter to change Gravity PDF default write location to /wp-uploads
/** This code was added to prove a concept during trial and without direct need to chagne the plugin code
*/
add_filter('gfpdf_tmp_location', function ($location) {
	$upload_dir = wp_get_upload_dir()['basedir'];
	$custom_dir = '/tmp/gravity-pdf/';
 	if (!file_exists($custom_dir)) {
 		mkdir($custom_dir, 0755, true);
 	}

 return $custom_dir;
}, 11, 1);

add_filter( 'gfpdf_installer_create_folders', function( $folders ) {
    return [];
}, 10, 1 );

add_filter( 'gfpdf_font_location', function( $path, $working_folder, $upload_url ) {
	$custom_dir = get_stylesheet_directory(). '/gravity-forms/fonts/';

	return $custom_dir;
	
}, 10, 3 );
