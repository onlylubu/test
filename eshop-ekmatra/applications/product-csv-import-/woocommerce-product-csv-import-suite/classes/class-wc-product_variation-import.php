<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'WP_Importer' ) ) {
class WC_CSV_Product_Variation_Import extends WC_CSV_Product_Import {

	public function __construct() {
		parent::__construct();

		$this->import_page = 'woocommerce_variation_csv';
	}

	/**
	 * Create new posts based on import information
	 */
	function process_product( $post ) {
		global $wpdb;

		$merging = ( ! empty( $post['merging'] ) && $post['merging'] ) ? true : false;

		if ( ! empty( $post['post_id'] ) && isset( $this->processed_posts[$post['post_id']] ) ) {
			$this->skipped++;
			$this->log->add( __('> Post ID already processed. Skipping.', 'wc_csv_import') );
			return;
		}

		if ( $post['post_status'] == 'auto-draft' ){
			$this->skipped++;
			$this->log->add( __('> Skipping auto-draft.', 'wc_csv_import') );
			return;
		}

		if ( empty( $post['post_parent'] ) ) {
			$this->skipped++;
			$this->log->add( __('> Skipping - no post parent set.', 'wc_csv_import') );
			return;
		}

		$post_parent = (int) $post['post_parent'];

		$post_parent_exists = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d", $post_parent ) );

		if ( ! $post_parent_exists ) {
			$this->log->add( sprintf( __('> Variation parent does not exist! (#%d)', 'wc_csv_import'), $post_parent ) );
			return;
		}

		if ($merging) {

			// Only merge fields which are set
			$post_id = $post['post_id'];

			$this->log->add( sprintf( __('> Merging post ID %s.', 'wc_csv_import'), $post_id ) );

			$postdata = array( 'ID' => $post_id );
			if (!empty($post['post_date'])) $postdata['post_date'] = date("Y-m-d H:i:s", strtotime( $post['post_date'] ) );
			if (!empty($post['post_date_gmt'])) $postdata['post_date_gmt'] = date("Y-m-d H:i:s", strtotime( $post['post_date_gmt'] ) );
			if (!empty($post['post_status'])) $postdata['post_status'] = $post['post_status'];
			if (!empty($post['menu_order'])) $postdata['menu_order'] = $post['menu_order'];
			$postdata['post_parent'] = $post_parent;

			if (sizeof($postdata)) wp_update_post( $postdata );

		} else {

			// Insert product
			$this->log->add( __('> Inserting variation.', 'wc_csv_import') );

			$postdata = array(
				'import_id' 	=> $post['post_id'],
				'post_date' 	=> ( $post['post_date'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date'] )) : '',
				'post_date_gmt' => ( $post['post_date_gmt'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date_gmt'] )) : '',
				'post_status' 	=> $post['post_status'],
				'post_parent' 	=> $post_parent,
				'menu_order' 	=> $post['menu_order'],
				'post_type' 	=> 'product_variation',
			);

			$post_id = wp_insert_post( $postdata, true );

			if ( is_wp_error( $post_id ) ) {

				$this->skipped++;
				$this->log->add( sprintf( __( 'Failed to import product &#8220;%s&#8221;', 'wc_csv_import' ), esc_html($post['post_title']) ) );
				return;

			} else {

				$this->log->add( sprintf( __('> Inserted - post ID is %s.', 'wc_csv_import'), $post_id ) );

			}
		}

		// map pre-import ID to local ID
		if (!isset($post['post_id'])) $post['post_id'] = (int) $post_id;
		$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

		// Set post title now we have an ID
		$postdata = array( 'ID' => $post_id );
		$postdata['post_title'] = sprintf( __('Variation #%s of %s', 'woocommerce'), $post_id, get_the_title( $post_parent ) );
		wp_update_post( $postdata );

		// add categories, tags and other terms
		if ( ! empty( $post['terms'] ) ) {

			$terms_to_set = array();

			foreach ( $post['terms'] as $term_group ) {

				$taxonomy 	= $term_group['taxonomy'];
				$terms		= $term_group['terms'];

				if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) continue;

				if ( ! is_array( $terms ) ) $terms = array( $terms );

				foreach ( $terms as $term_id ) {

					if ( ! $term_id ) continue;

					$terms_to_set[$taxonomy][] = intval( $term_id );

				}

			}

			foreach ( $terms_to_set as $tax => $ids ) {
				$tt_ids = wp_set_post_terms( $post_id, $ids, $tax, false );
			}

			$this->log->add( __( '> > Terms set', 'wc_csv_import' ) );

			unset( $post['terms'], $terms_to_set );
		}

		// add/update post meta
		if ( ! empty( $post['postmeta'] ) ) {
			foreach ( $post['postmeta'] as $meta ) {
				$key = apply_filters( 'import_post_meta_key', $meta['key'] );

				if ( $key ) {

					$this->log->add( sprintf( __('> > Updating custom field - %s.', 'wc_csv_import'), $key ) );

					$value = maybe_unserialize( $meta['value'] );

					update_post_meta( $post_id, $key, $value );
				}
			}

			unset( $post['postmeta'] );
		}

		// Import images and add to post
		if ( ! empty( $post['images'] ) ) {

			$featured = true;

			if ($merging) {

				// Remove old
				delete_post_meta( $post_id, '_thumbnail_id' );

				// Delete old attachments
				$attachments = get_posts( 'post_parent=' . $post_id . '&post_type=attachment&fields=ids&post_mime_type=image&numberposts=-1' );

				foreach ($attachments as $attachment) {

					$url = wp_get_attachment_url( $attachment );

					if ( in_array( $url, $post['images'] ) ) {
						if ( $url == $post['images'][0] ) {
							update_post_meta( $post_id, '_thumbnail_id', $attachment );
						}
						unset( $post['images'][ array_search( $url, $post['images'] ) ] );
					} else {
						// Detach
						$attachment_post = array();
						$attachment_post['ID'] = $attachment;
						$attachment_post['post_parent'] = '';
						wp_update_post( $attachment_post );
						//wp_delete_attachment( $attachment );
					}
				}

				$this->log->add( __( '> > Old images processed', 'wc_csv_import' ) );

			}

			if ( $post['images'] ) foreach ( $post['images'] as $image ) {

				$this->log->add( sprintf( __( '> > Importing image "%s"', 'wc_csv_import' ), $image ) );

				$wp_filetype = wp_check_filetype( basename( $image ), null );
				$wp_upload_dir = wp_upload_dir();
				$filename = basename( $image );

				$attachment = array(
					 'post_mime_type' 	=> $wp_filetype['type'],
					 'post_title' 		=> preg_replace('/\.[^.]+$/', '', basename( $filename )),
					 'post_content' 	=> '',
					 'post_status' 		=> 'inherit'
				);

				$attachment_id = $this->process_attachment( $attachment, $image, $post_id );

				if ( ! is_wp_error( $attachment_id ) ) {
					if ( $featured ) update_post_meta( $post_id, '_thumbnail_id', $attachment_id );

					update_post_meta( $attachment_id, '_woocommerce_exclude_image', 0 );

					$featured = false;
				} else {
					$this->log->add( '> > ' . $attachment_id->get_error_message() );
				}
			}

			$this->log->add( __( '> > Images set', 'wc_csv_import' ) );

			unset( $post['images'] );
		}

		if ($merging) {
			$this->merged++;
			$this->log->add( sprintf( __('> Finished merging variation ID %s.', 'wc_csv_import'), $post_id ) );
		} else {
			$this->imported++;
			$this->log->add( sprintf( __('> Finished importing variation ID %s.', 'wc_csv_import'), $post_id ) );
		}

		unset( $post );
	}

	/**
	 * Parses the CSV file and prepares us for the task of processing parsed data
	 *
	 * @param string $file Path to the CSV file for importing
	 */
	function import_start( $file ) {
		if ( ! is_file($file) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wc_csv_import' ) . '</strong><br />';
			echo __( 'The file does not exist, please try again.', 'wc_csv_import' ) . '</p>';
			$this->footer();
			die();
		}

		$this->log->add( __( 'Parsing product variation CSV.', 'wc_csv_import' ) );

		$this->parser = new WC_CSV_Parser( 'product_variation' );
		$import_data = $this->parser->parse_data( $file, $this->delimiter );

		$this->parsed_data = $import_data[0];
		$this->raw_headers = $import_data[1];

		$this->log->add( __( 'Finished parsing product variation CSV.', 'wc_csv_import' ) );

		unset( $import_data );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
	}

	// Display import page title
	function header() {
		echo '<div class="wrap"><div class="icon32" id="icon-woocommerce-importer"><br></div>';
		echo '<h2>' . __( 'Import Product Variations', 'wc_csv_import' ) . '</h2>';
	}

	/**
	 * Display introductory text and file upload form
	 */
	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__( 'Hi there! Upload a CSV file containing product variation data to import the contents into your shop.', 'wc_csv_import' ).'</p>';
		echo '<p>'.__( 'Choose a CSV (.csv) file to upload, then click Upload file and import.', 'wc_csv_import' ).'</p>';
		//wp_import_upload_form( 'admin.php?import=woocommerce_variation_csv&amp;step=1&amp;merge=' . ( ! empty( $_GET['merge'] ) ? 1 : 0 ) );

		$action = 'admin.php?import=woocommerce_variation_csv&amp;step=1&amp;merge=' . ( ! empty( $_GET['merge'] ) ? 1 : 0 );

		$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size = wp_convert_bytes_to_hr( $bytes );
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) :
			?><div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:'); ?></p>
			<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
		else :
			?>
			<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(wp_nonce_url($action, 'import-upload')); ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="upload"><?php _e( 'Choose a file from your computer:' ); ?></label>
							</th>
							<td>
								<input type="file" id="upload" name="import" size="25" />
								<input type="hidden" name="action" value="save" />
								<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
								<small><?php printf( __('Maximum size: %s' ), $size ); ?></small>
							</td>
						</tr>
						<?php if ( $this->file_url_import_enabled ) : ?>
						<tr>
							<th>
								<label for="file_url"><?php _e( 'OR enter path to file:', 'wc_csv_import' ); ?></label>
							</th>
							<td>
								<?php echo ' ' . ABSPATH . ' '; ?><input type="text" id="file_url" name="file_url" size="25" />
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<th><label><?php _e( 'Delimiter', 'wc_csv_import' ); ?></label><br/></th>
							<td><input type="text" name="delimiter" placeholder="," /></td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" class="button" value="<?php esc_attr_e( 'Upload file and import' ); ?>" />
				</p>
			</form>
			<?php
		endif;

		echo '</div>';
	}
}
}