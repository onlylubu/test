<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'WP_Importer' ) ) {
class WC_CSV_Product_Import extends WP_Importer {
	var $id; // CSV attachment ID
	var $file_url; // CSV attachmente url
	var $import_page;
	var $delimiter;

	// information to import from CSV file
	var $posts = array();

	// mappings from old information to new
	var $processed_terms = array();
	var $processed_posts = array();
	var $post_orphans = array();

	var $fetch_attachments = false;
	var $url_remap = array();

	// Counts
	var $log;
	var $merged;
	var $skipped;
	var $imported;

	var $file_url_import_enabled = true;

	public function __construct() {
		$this->log = new WC_CSV_Log();

		$this->import_page = 'woocommerce_csv';

		$this->file_url_import_enabled = apply_filters('woocommerce_csv_product_file_url_import_enabled', true);

	}

	/**
	 * Registered callback function for the WordPress Importer
	 *
	 * Manages the three separate stages of the CSV import process
	 */
	function dispatch() {
		$this->header();

		if ( ! empty( $_POST['delimiter'] ) ) {
			$this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );
		}

		if ( ! $this->delimiter ) $this->delimiter = ',';

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
				$this->greet();
				break;
			case 1:
				check_admin_referer( 'import-upload' );
				if ( $this->handle_upload() )
					$this->import_options();
				break;
			case 2:
				check_admin_referer( 'import-woocommerce' );

				$this->id = (int) $_POST['import_id'];
				if ( $this->file_url_import_enabled )
					$this->file_url = esc_attr( $_POST['import_url'] );

				if ( $this->id )
					$file = get_attached_file( $this->id );
				else if ( $this->file_url_import_enabled )
					$file = ABSPATH . $this->file_url;

				if ( $file ) {
					echo '<div class="importer_loader"></div>';

					add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

					if ( function_exists( 'gc_enable' ) )
						gc_enable();

					@set_time_limit(0);
					@ob_flush();
					@flush();

					$this->import_start( $file );
					$this->import();
					break;
				}
		}

		$this->footer();
	}

	function format_data_from_csv( $data, $enc ) {
		return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
	}

	/**
	 * Display pre-import options
	 */
	function import_options() {
		$j = 0;

		if ( $this->id )
			$file = get_attached_file( $this->id );
		else if ( $this->file_url_import_enabled )
			$file = ABSPATH . $this->file_url;
		else
			return;

		// Set locale
		$enc = mb_detect_encoding( $file, 'UTF-8, ISO-8859-1', true );
		if ( $enc ) setlocale( LC_ALL, 'en_US.' . $enc );
		@ini_set( 'auto_detect_line_endings', true );

		// Get headers
		if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {

			$row = $raw_headers = array();
			$header = fgetcsv( $handle, 0, $this->delimiter );

		    while ( ( $postmeta = fgetcsv( $handle, 0, $this->delimiter ) ) !== FALSE ) {
	            foreach ( $header as $key => $heading ) {
	            	if ( ! $heading ) continue;
	            	$s_heading = strtolower( $heading );
	                $row[$s_heading] = ( isset( $postmeta[$key] ) ) ? $this->format_data_from_csv( $postmeta[$key], $enc ) : '';
	                $raw_headers[ $s_heading ] = $heading;
	            }
	            break;
		    }
		    fclose( $handle );
		}

		$merge = (!empty($_GET['merge']) && $_GET['merge']) ? 1 : 0;

		$taxonomies = get_taxonomies( '', 'names' );

?>
<form action="<?php echo admin_url( 'admin.php?import=' . $this->import_page . '&step=2&merge=' . $merge ); ?>" method="post">
	<?php wp_nonce_field( 'import-woocommerce' ); ?>
	<input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
	<?php if ( $this->file_url_import_enabled ) : ?>
	<input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
	<?php endif; ?>

	<h3><?php _e( 'Map Fields', 'wc_csv_import' ); ?></h3>
	<p><?php _e( 'Here you can map your imported columns to product data fields.', 'wc_csv_import' ); ?></p>

	<table class="widefat widefat_importer">
		<thead>
			<tr>
				<th><?php _e( 'Map to', 'wc_csv_import' ); ?></th>
				<th><?php _e( 'Column Header', 'wc_csv_import' ); ?></th>
				<th><?php _e( 'Example Column Value', 'wc_csv_import' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $row as $key => $value ) : ?>
			<tr>
				<td width="25%">
					<?php
						if ( strstr( $key, 'tax:' ) ) {

							$column = trim( str_replace( 'tax:', '', $key ) );
							printf(__('Taxonomy: <strong>%s</strong>', 'wc_csv_import'), $column);

						} elseif ( strstr( $key, 'meta:' ) ) {

							$column = trim( str_replace( 'meta:', '', $key ) );
							printf(__('Custom Field: <strong>%s</strong>', 'wc_csv_import'), $column);

						} elseif ( strstr( $key, 'attribute:' ) ) {

							$column = trim( str_replace( 'attribute:', '', $key ) );
							printf(__('Product Attribute: <strong>%s</strong>', 'wc_csv_import'), sanitize_title( $column ) );

						} elseif ( strstr( $key, 'attribute_data:' ) ) {

							$column = trim( str_replace( 'attribute_data:', '', $key ) );
							printf(__('Product Attribute Data: <strong>%s</strong>', 'wc_csv_import'), sanitize_title( $column ) );

						} elseif ( strstr( $key, 'attribute_default:' ) ) {

							$column = trim( str_replace( 'attribute_default:', '', $key ) );
							printf(__('Product Attribute default value: <strong>%s</strong>', 'wc_csv_import'), sanitize_title( $column ) );

						} else {
							?>
							<select name="map_to[<?php echo $key; ?>]">
								<option value=""><?php _e( 'Do not import', 'wc_csv_import' ); ?></option>
								<option value="import_as_images" <?php selected( $key, 'images' ); ?>><?php _e( 'Images/Gallery', 'wc_csv_import' ); ?></option>
								<option value="import_as_meta"><?php _e( 'Custom Field with column name', 'wc_csv_import' ); ?></option>
								<optgroup label="<?php _e( 'Taxonomies', 'wc_csv_import' ); ?>">
									<?php
										foreach ($taxonomies as $taxonomy ) {
											if ( substr( $taxonomy, 0, 3 ) == 'pa_' ) continue;
											echo '<option value="tax:' . $taxonomy . '" ' . selected( $key, 'tax:' . $taxonomy, true ) . '>' . $taxonomy . '</option>';
										}
									?>
								</optgroup>
								<optgroup label="<?php _e( 'Attributes', 'wc_csv_import' ); ?>">
									<?php
										foreach ($taxonomies as $taxonomy ) {
											if ( substr( $taxonomy, 0, 3 ) == 'pa_' )
												echo '<option value="attribute:' . $taxonomy . '" ' . selected( $key, 'attribute:' . $taxonomy, true ) . '>' . $taxonomy . '</option>';
										}
									?>
								</optgroup>
								<optgroup label="<?php _e( 'Post data', 'wc_csv_import' ); ?>">
									<option <?php selected( $key, 'post_id' ); selected( $key, 'id' ); ?>>post_id</option>
									<option <?php selected( $key, 'post_type' ); ?>>post_type</option>
									<option <?php selected( $key, 'menu_order' ); ?>>menu_order</option>
									<option <?php selected( $key, 'post_status' ); ?>>post_status</option>
									<option <?php selected( $key, 'post_title' ); ?>>post_title</option>
									<option <?php selected( $key, 'post_name' ); ?>>post_name</option>
									<option <?php selected( $key, 'post_date' ); ?>>post_date</option>
									<option <?php selected( $key, 'post_date_gmt' ); ?>>post_date_gmt</option>
									<option <?php selected( $key, 'post_content' ); ?>>post_content</option>
									<option <?php selected( $key, 'post_excerpt' ); ?>>post_excerpt</option>
									<option <?php selected( $key, 'post_parent' ); ?>>post_parent</option>
									<option <?php selected( $key, 'post_password' ); ?>>post_password</option>
									<option <?php selected( $key, 'comment_status' ); ?>>comment_status</option>
								</optgroup>
								<optgroup label="<?php _e( 'Product data', 'wc_csv_import' ); ?>">
									<option value="tax:product_type" <?php selected( $key, 'product_type' ); ?> <?php selected( $key, 'tax:product_type' ); ?>>product_type</option>
									<option <?php selected( $key, 'parent_sku' ); ?>>parent_sku</option>
									<option <?php selected( $key, 'sku' ); ?>>sku</option>
									<option <?php selected( $key, 'downloadable' ); ?>>downloadable</option>
									<option <?php selected( $key, 'virtual' ); ?>>virtual</option>
									<option <?php selected( $key, 'price' ); ?>>price</option>
									<option <?php selected( $key, 'visibility' ); ?>>visibility</option>
									<option <?php selected( $key, 'stock' ); ?>>stock</option>
									<option <?php selected( $key, 'stock_status' ); ?>>stock_status</option>
									<option <?php selected( $key, 'backorders' ); ?>>backorders</option>
									<option <?php selected( $key, 'manage_stock' ); ?>>manage_stock</option>
									<option <?php selected( $key, 'sale_price' ); ?>>sale_price</option>
									<option <?php selected( $key, 'regular_price' ); ?>>regular_price</option>
									<option <?php selected( $key, 'weight' ); ?>>weight</option>
									<option <?php selected( $key, 'length' ); ?>>length</option>
									<option <?php selected( $key, 'width' ); ?>>width</option>
									<option <?php selected( $key, 'height' ); ?>>height</option>
									<option <?php selected( $key, 'tax_status' ); ?>>tax_status</option>
									<option <?php selected( $key, 'tax_class' ); ?>>tax_class</option>
									<option <?php selected( $key, 'upsell_ids' ); ?>>upsell_ids</option>
									<option <?php selected( $key, 'crosssell_ids' ); ?>>crosssell_ids</option>
									<option <?php selected( $key, 'sale_price_dates_from' ); ?>>sale_price_dates_from</option>
									<option <?php selected( $key, 'sale_price_dates_to' ); ?>>sale_price_dates_to</option>
									<option <?php selected( $key, 'featured' ); ?>>featured</option>
									<option <?php selected( $key, 'file_path' ); ?>>file_path</option>
									<option <?php selected( $key, 'file_paths' ); ?>>file_paths</option>
									<option <?php selected( $key, 'download_limit' ); ?>>download_limit</option>
									<option <?php selected( $key, 'download_expiry' ); ?>>download_expiry</option>
									<option <?php selected( $key, 'product_url' ); ?>>product_url</option>
									<option <?php selected( $key, 'button_text' ); ?>>button_text</option>
								</optgroup>
								<?php if( function_exists( 'woocommerce_gpf_install' ) ) : ?>
								<optgroup label="<?php _e( 'Google Product Feed', 'wc_csv_import' ); ?>">
									<option value="gpf:availability" <?php selected( $key, 'gpf:availability' ); ?>><?php _e('Availability', 'wc_csv_import' ); ?></option>
									<option value="gpf:condition" <?php selected( $key, 'gpf:condition' ); ?>><?php _e('Condition', 'wc_csv_import' ); ?></option>
									<option value="gpf:brand" <?php selected( $key, 'gpf:brand' ); ?>><?php _e('Brand', 'wc_csv_import' ); ?></option>
									<option value="gpf:product_type" <?php selected( $key, 'gpf:product_type' ); ?>><?php _e('Product Type', 'wc_csv_import' ); ?></option>
									<option value="gpf:google_product_category" <?php selected( $key, 'gpf:google_product_category' ); ?>><?php _e('Google Product Category', 'wc_csv_import' ); ?></option>
									<option value="gpf:gtin" <?php selected( $key, 'gpf:gtin' ); ?>><?php _e('Global Trade Item Number (GTIN)', 'wc_csv_import' ); ?></option>
									<option value="gpf:mpn" <?php selected( $key, 'gpf:mpn' ); ?>><?php _e('Manufacturer Part Number (MPN)', 'wc_csv_import' ); ?></option>
									<option value="gpf:gender" <?php selected( $key, 'gpf:gender' ); ?>><?php _e('Gender', 'wc_csv_import' ); ?></option>
									<option value="gpf:age_group" <?php selected( $key, 'gpf:age_group' ); ?>><?php _e('Age Group', 'wc_csv_import' ); ?></option>
									<option value="gpf:color" <?php selected( $key, 'gpf:color' ); ?>><?php _e('Color', 'wc_csv_import' ); ?></option>
									<option value="gpf:size" <?php selected( $key, 'gpf:size' ); ?>><?php _e('Size', 'wc_csv_import' ); ?></option>
								</optgroup>
								<?php endif; ?>
							</select>
							<?php
						}
					?>
				</td>
				<td width="25%"><?php echo $raw_headers[$key]; ?></td>
				<td><code><?php if ( $value != '' ) echo $value; else echo '-'; ?></code></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="submit">
		<input type="submit" class="button" value="<?php esc_attr_e( 'Submit', 'wc_csv_import' ); ?>" />
		<input type="hidden" name="delimiter" value="<?php echo $this->delimiter ?>" />
	</p>
</form>
<?php
	}


	/**
	 * The main controller for the actual import stage.
	 */
	function import() {
		global $woocommerce, $wpdb;

		wp_suspend_cache_invalidation( true );
		echo '<div class="progress">';

		$this->imported = $this->merged = 0;

		$this->log->add( '---' );
		$this->log->add( __( 'Processing products.', 'wc_csv_import' ) );

		//echo memory_get_usage();
		//echo '<br/><br/>';

		foreach ( $this->parsed_data as $key => &$item ) {

			$product = $this->parser->parse_product( $item );

			if ( $product )
				$this->process_product( $product );
			else
				$this->skipped++;

			unset( $item, $product );

			//echo memory_get_usage();
			//echo '<br/><br/>';

		}

		$this->log->add( __( 'Finished processing products.', 'wc_csv_import' ) );

		echo '</div>';
		wp_suspend_cache_invalidation( false );

		// update incorrect/missing information in the DB
		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$woocommerce->clear_product_transients();
		_get_term_hierarchy('product_type');
		$wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_product_type_%')");

		// Show Result
		echo '<div class="updated settings-error below-h2"><p>
			'.sprintf(__('Import complete - imported <strong>%s</strong>, merged <strong>%s</strong>, skipped <strong>%s</strong>', 'wc_csv_import'), absint( $this->imported ), absint( $this->merged ), absint( $this->skipped ) ).'
		</p></div>';

		$this->log->show_log();

		$this->import_end();
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

		$this->log->add( __( 'Parsing products CSV.', 'wc_csv_import' ) );

		$this->parser = new WC_CSV_Parser( 'product' );
		$import_data = $this->parser->parse_data( $file, $this->delimiter );

		$this->parsed_data = $import_data[0];
		$this->raw_headers = $import_data[1];

		$this->log->add( __( 'Finished parsing products CSV.', 'wc_csv_import' ) );

		unset( $import_data );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
	}

	/**
	 * Performs post-import cleanup of files and the cache
	 */
	function import_end() {
		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		echo '<p>' . __( 'All done!', 'wc_csv_import' ) . ' <a href="' . admin_url('edit.php?post_type=product') . '">' . __( 'View Products', 'wc_csv_import' ) . '</a>' . '</p>';

		do_action( 'import_end' );
	}

	/**
	 * Handles the CSV upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {

		if ( empty( $_POST['file_url'] ) ) {

			$file = wp_import_handle_upload();

			if ( isset( $file['error'] ) ) {
				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wc_csv_import' ) . '</strong><br />';
				echo esc_html( $file['error'] ) . '</p>';
				return false;
			}

			$this->id = (int) $file['id'];

		} else {

			if ( file_exists( ABSPATH . $_POST['file_url'] ) ) {

				$this->file_url = esc_attr( $_POST['file_url'] );

			} else {

				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wc_csv_import' ) . '</strong></p>';
				return false;

			}

		}

		return true;
	}

	function product_exists( $title, $sku = '' ) {
		global $wpdb;

		// Post Title Check
		$post_title = stripslashes( sanitize_post_field( 'post_title', $title, 0, 'db' ) );

	    $query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'product' AND post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )";
	    $args = array();

	    if ( ! empty ( $title ) ) {
	        $query .= ' AND post_title = %s';
	        $args[] = $post_title;
	    }

	    if ( ! empty ( $args ) ) {
	        $posts_that_exist = $wpdb->get_col( $wpdb->prepare( $query, $args ) );

	        if ( $posts_that_exist ) {

	        	foreach( $posts_that_exist as $post_exists ) {

		        	// Check unique SKU
		        	$post_exists_sku = get_post_meta( $post_exists, '_sku', true );

					if ( $sku == $post_exists_sku ) {
						return true;
					}

	        	}

		    }
		}

		// Sku Check
		if ( $sku ) {

			 $post_exists_sku = $wpdb->get_var( $wpdb->prepare( "
				SELECT $wpdb->posts.ID
			    FROM $wpdb->posts
			    LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
			    WHERE $wpdb->posts.post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )
			    AND $wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value = '%s'
			 ", $sku ) );

			 if ( $post_exists_sku ) {
				 return true;
			 }
		}

	    return false;
	}

	/**
	 * Create new posts based on import information
	 */
	function process_product( $post ) {

		$merging = ( ! empty( $post['merging'] ) && $post['merging'] ) ? true : false;

		if ( ! empty( $post['post_id'] ) && isset( $this->processed_posts[$post['post_id']] ) ) {
			$this->skipped++;
			$this->log->add( __('> Post ID already processed. Skipping.', 'wc_csv_import'), true );
			unset( $post );
			return;
		}

		if ( ! empty ( $post['post_status'] ) && $post['post_status'] == 'auto-draft' ) {
			$this->skipped++;
			$this->log->add( __('> Skipping auto-draft.', 'wc_csv_import'), true );
			unset( $post );
			return;
		}

		// Check if post exists when importing
		if ( ! $merging ) {
			if ( $this->product_exists( $post['post_title'], $post['sku'] ) ) {
				$this->skipped++;
				$this->log->add( sprintf( __('> &#8220;%s&#8221; already exists.', 'wc_csv_import'), esc_html($post['post_title']) ), true );
				unset( $post );
				return;
			}
		}

		if ( $merging ) {

			// Only merge fields which are set
			$post_id = $post['post_id'];

			$this->log->add( sprintf( __('> Merging post ID %s.', 'wc_csv_import'), $post_id ), true );

			$postdata = array(
				'ID' => $post_id
			);
			if (!empty($post['post_date'])) $postdata['post_date'] = date("Y-m-d H:i:s", strtotime( $post['post_date'] ) );
			if (!empty($post['post_date_gmt'])) $postdata['post_date_gmt'] = date("Y-m-d H:i:s", strtotime( $post['post_date_gmt'] ) );
			if (!empty($post['post_content'])) $postdata['post_content'] = $post['post_content'];
			if (!empty($post['post_excerpt'])) $postdata['post_excerpt'] = $post['post_excerpt'];
			if (!empty($post['post_title'])) $postdata['post_title'] = $post['post_title'];
			if (!empty($post['post_name'])) $postdata['post_name'] = $post['post_name'];
			if (!empty($post['post_status'])) $postdata['post_status'] = $post['post_status'];
			if (!empty($post['post_parent'])) $postdata['post_parent'] = $post['post_parent'];
			if (!empty($post['menu_order'])) $postdata['menu_order'] = $post['menu_order'];
			if (!empty($post['post_password'])) $postdata['post_password'] = $post['post_password'];
			if (!empty($post['comment_status'])) $postdata['comment_status'] = $post['comment_status'];

			if ( sizeof( $postdata ) > 1 ) {
				$result = wp_update_post( $postdata );

				if ( ! $result ) {
					$this->skipped++;
					$this->log->add( sprintf( __('> Failed to update product %s', 'wc_csv_import'), $post_id ), true );
					unset( $post );
					return;
				}
			}

		} else {

			// Get parent
			$post_parent = (int) $post['post_parent'];
			if ( $post_parent ) {
				// if we already know the parent, map it to the new local ID
				if ( isset( $this->processed_posts[$post_parent] ) ) {
					$post_parent = $this->processed_posts[$post_parent];
				// otherwise record the parent for later
				} else {
					$this->post_orphans[intval($post['post_id'])] = $post_parent;
					$post_parent = 0;
				}
			}

			// Insert product
			$this->log->add( sprintf( __('> Inserting %s', 'wc_csv_import'), esc_html($post['post_title']) ), true );

			$postdata = array(
				'import_id'      => $post['post_id'],
				'post_author'    => get_current_user_id(),
				'post_date'      => ( $post['post_date'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date'] )) : '',
				'post_date_gmt'  => ( $post['post_date_gmt'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date_gmt'] )) : '',
				'post_content'   => $post['post_content'],
				'post_excerpt'   => $post['post_excerpt'],
				'post_title'     => $post['post_title'],
				'post_name'      => ( $post['post_name'] ) ? $post['post_name'] : sanitize_title( $post['post_title'] ),
				'post_status'    => ( $post['post_status'] ) ? $post['post_status'] : 'publish',
				'post_parent'    => $post_parent,
				'menu_order'     => $post['menu_order'],
				'post_type'      => 'product',
				'post_password'  => $post['post_password'],
				'comment_status' => $post['comment_status'],
			);

			$post_id = wp_insert_post( $postdata, true );

			if ( is_wp_error( $post_id ) ) {

				$this->skipped++;
				$this->log->add( sprintf( __( 'Failed to import product &#8220;%s&#8221;', 'wc_csv_import' ), esc_html($post['post_title']) ) );
				unset( $post );
				return;

			} else {

				$this->log->add( sprintf( __('> Inserted - post ID is %s.', 'wc_csv_import'), $post_id ) );

			}
		}

		unset( $postdata );

		// map pre-import ID to local ID
		if (!isset($post['post_id'])) $post['post_id'] = (int) $post_id;
		$this->processed_posts[intval($post['post_id'])] = (int) $post_id;

		// add categories, tags and other terms
		if ( ! empty( $post['terms'] ) && is_array($post['terms']) ) {

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

			unset( $post['terms'], $terms_to_set );
		}

		// add/update post meta
		if ( ! empty( $post['postmeta'] ) && is_array( $post['postmeta'] ) ) {
			foreach ( $post['postmeta'] as $meta ) {
				$key = apply_filters( 'import_post_meta_key', $meta['key'] );

				if ( $key ) {
					update_post_meta( $post_id, $key, maybe_unserialize( $meta['value'] ) );
				}
			}

			unset( $post['postmeta'] );
		}

		// Import images and add to post
		if ( ! empty( $post['images'] ) && is_array($post['images']) ) {

			$featured = true;

			if ($merging) {

				// Get basenames
				$image_basenames = array();

				foreach( $post['images'] as $image )
					$image_basenames[] = basename( $image );

				// Loop attachments already attached to the product
				$attachments = get_posts( 'post_parent=' . $post_id . '&post_type=attachment&fields=ids&post_mime_type=image&numberposts=-1' );

				foreach ( $attachments as $attachment_key => $attachment ) {

					$attachment_url 		= wp_get_attachment_url( $attachment );
					$attachment_basename 	= basename( $attachment_url );

					// Don't import existing images
					if ( in_array( $attachment_url, $post['images'] ) || in_array( $attachment_basename, $image_basenames ) ) {

						foreach( $post['images'] as $key => $image ) {

							if ( $image == $attachment_url || basename( $image ) == $attachment_basename ) {
								unset( $post['images'][ $key ] );

								$this->log->add( sprintf( __( '> > Image exists - skipping %s', 'wc_csv_import' ), basename( $image ) ) );

								if ( $attachment_key == 0 ) {
									update_post_meta( $post_id, '_thumbnail_id', $attachment );
									$featured = false;
								}
							}

						}

					} else {

						// Detach image which is not being merged
						$attachment_post = array();
						$attachment_post['ID'] = $attachment;
						$attachment_post['post_parent'] = '';
						wp_update_post( $attachment_post );
						unset( $attachment_post );

					}

				}

				unset( $attachments );
			}

			if ( $post['images'] ) foreach ( $post['images'] as $image_key => $image ) {

				$this->log->add( sprintf( __( '> > Importing image "%s"', 'wc_csv_import' ), $image ) );

				$filename = basename( $image );

				$attachment = array(
					 'post_title' 		=> preg_replace( '/\.[^.]+$/', '', $post['post_title'] . ' ' . ( $image_key + 1 ) ),
					 'post_content' 	=> '',
					 'post_status' 		=> 'inherit',
					 'post_parent'		=> $post_id
				);

				$attachment_id = $this->process_attachment( $attachment, $image, $post_id );

				if ( ! is_wp_error( $attachment_id ) && $attachment_id ) {

					if ( $featured )
						update_post_meta( $post_id, '_thumbnail_id', $attachment_id );

					update_post_meta( $attachment_id, '_woocommerce_exclude_image', 0 );

					$featured = false;
				} else {
					$this->log->add( '> > ' . $attachment_id->get_error_message() );
				}

				unset( $attachment, $attachment_id );
			}

			$this->log->add( __( '> > Images set', 'wc_csv_import' ) );

			unset( $post['images'], $featured );
		}

		// Import attributes
		if ( ! empty( $post['attributes'] ) && is_array($post['attributes']) ) {

			if ($merging) {
				$attributes = array_filter( (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) ) );
				$attributes = array_merge( $attributes, $post['attributes'] );
			} else {
				$attributes = $post['attributes'];
			}

			update_post_meta( $post_id, '_product_attributes', $attributes );

			unset( $post['attributes'], $attributes );
		}

		// Import GPF
		if ( ! empty( $post['gpf_data'] ) && is_array( $post['gpf_data'] ) ) {

			update_post_meta( $post_id, '_woocommerce_gpf_data', $post['gpf_data'] );

			unset( $post['gpf_data'] );
		}


		if ($merging) {
			$this->merged++;
			$this->log->add( sprintf( __('> Finished merging post ID %s.', 'wc_csv_import'), $post_id ) );
		} else {
			$this->imported++;
			$this->log->add( sprintf( __('> Finished importing post ID %s.', 'wc_csv_import'), $post_id ) );
		}

		unset( $post );
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array $post Attachment post details from WXR
	 * @param string $url URL to fetch attachment from
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	function process_attachment( $post, $url, $post_id ) {

		$attachment_id 		= '';
		$attachment_url 	= '';
		$attachment_file 	= '';
		$upload_dir 		= wp_upload_dir();

		// If same server, make it a path and move to upload directory
		/*if ( strstr( $url, $upload_dir['baseurl'] ) ) {

			$url = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );

		} else*/
		if ( strstr( $url, site_url() ) ) {
			$abs_url 	= str_replace( trailingslashit( site_url() ), trailingslashit( ABSPATH ), $url );
			$new_name 	= wp_unique_filename( $upload_dir['path'], basename( $url ) );
			$new_url 	= trailingslashit( $upload_dir['path'] ) . $new_name;

			if ( copy( $abs_url, $new_url ) ) {
				$url = basename( $new_url );
			}
		}

		if ( ! strstr( $url, 'http' ) ) {

			// Local file
			$attachment_file 	= trailingslashit( $upload_dir['path'] ) . $url;

			// We have the path, check it exists
			if ( file_exists( $attachment_file ) ) {

				$attachment_url 	= str_replace( trailingslashit( ABSPATH ), trailingslashit( site_url() ), $attachment_file );

				if ( $info = wp_check_filetype( $attachment_file ) )
					$post['post_mime_type'] = $info['type'];
				else
					return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'wordpress-importer') );

				$post['guid'] = $attachment_url;

				$attachment_id 		= wp_insert_attachment( $post, $attachment_file, $post_id );

			} else {
				return new WP_Error( 'attachment_processing_error', __('Local image did not exist!', 'wordpress-importer') );
			}

		} else {

			// if the URL is absolute, but does not contain address, then upload it assuming base_site_url
			if ( preg_match( '|^/[\w\W]+$|', $url ) )
				$url = rtrim( site_url(), '/' ) . $url;

			$upload = $this->fetch_remote_file( $url, $post );

			if ( is_wp_error( $upload ) )
				return $upload;

			if ( $info = wp_check_filetype( $upload['file'] ) )
				$post['post_mime_type'] = $info['type'];
			else
				return new WP_Error( 'attachment_processing_error', __('Invalid file type', 'wordpress-importer') );

			$post['guid'] = $upload['url'];

			$attachment_file 	= $upload['file'];
			$attachment_url 	= $upload['url'];

			// as per wp-admin/includes/upload.php
			$attachment_id = wp_insert_attachment( $post, $upload['file'], $post_id );

			unset( $upload );
		}

		if ( ! is_wp_error( $attachment_id ) && $attachment_id > 0 ) {

			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_file ) );

			// remap resized image URLs, works by stripping the extension and remapping the URL stub.
			if ( preg_match( '!^image/!', $info['type'] ) ) {
				$parts = pathinfo( $url );
				$name = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2

				$parts_new = pathinfo( $attachment_url );
				$name_new = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

				$this->url_remap[$parts['dirname'] . '/' . $name] = $parts_new['dirname'] . '/' . $name_new;
			}

		}

		return $attachment_id;
	}

	/**
	 * Attempt to download a remote file attachment
	 */
	function fetch_remote_file( $url, $post ) {

		// extract the file name and extension from the url
		$file_name 		= basename( $url );
		$wp_filetype 	= wp_check_filetype( $file_name, null );
		$parsed_url 	= @parse_url( $url );

		// Check parsed URL
		if ( ! $parsed_url || ! is_array( $parsed_url ) )
			return false;

		// Ensure url is valid
		$url = str_replace( " ", '%20', $url );

		// Get the file
		$response = wp_remote_get( $url, array(
			'timeout' => 10
		) );

		if ( is_wp_error( $response ) )
			return false;

		// Ensure we have a file name and type
		if ( ! $wp_filetype['type'] ) {

			$headers = wp_remote_retrieve_headers( $response );

			if ( isset( $headers['content-type'] ) && strstr( $headers['content-type'], 'image/' ) ) {

				$file_name = 'image.' . str_replace( 'image/', '', $headers['content-type'] );

			} elseif ( isset( $headers['content-disposition'] ) && strstr( $headers['content-disposition'], 'filename=' ) ) {

				$disposition = end( explode( 'filename=', $headers['content-disposition'] ) );

				$disposition = sanitize_file_name( $disposition );

				$file_name = $disposition;

			}

			unset( $headers );
		}

		// Upload the file
		$upload = wp_upload_bits( $file_name, '', wp_remote_retrieve_body( $response ) );

		if ( $upload['error'] )
			return new WP_Error( 'upload_dir_error', $upload['error'] );

		// Get filesize
		$filesize = filesize( $upload['file'] );

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			unset( $upload );
			return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'wc_csv_import') );
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];

		// keep track of the destination if the remote url is redirected somewhere else
		if ( isset($headers['x-final-location']) && $headers['x-final-location'] != $url )
			$this->url_remap[$headers['x-final-location']] = $upload['url'];

		unset( $response );

		return $upload;
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size() {
		return apply_filters( 'import_attachment_size_limit', 0 );
	}

	/**
	 * Attempt to associate posts and menu items with previously missing parents
	 */
	function backfill_parents() {
		global $wpdb;

		// find parents for post orphans
		foreach ( $this->post_orphans as $child_id => $parent_id ) {
			$local_child_id = $local_parent_id = false;
			if ( isset( $this->processed_posts[$child_id] ) )
				$local_child_id = $this->processed_posts[$child_id];
			if ( isset( $this->processed_posts[$parent_id] ) )
				$local_parent_id = $this->processed_posts[$parent_id];

			if ( $local_child_id && $local_parent_id )
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d' );
		}

	}

	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			// remap urls in post_content
			$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_type = 'product'", $from_url, $to_url) );
			// remap enclosure urls
			$result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key = 'enclosure'", $from_url, $to_url) );
		}
	}

	// Display import page title
	function header() {
		echo '<div class="wrap"><div class="icon32" id="icon-woocommerce-importer"><br></div>';
		echo '<h2>' . __( 'Import Products', 'wc_csv_import' ) . '</h2>';
	}

	// Close div.wrap
	function footer() {
		echo '<script> jQuery(".importer_loader, .progress").hide(); </script>';
		echo '</div>';
	}

	/**
	 * Display introductory text and file upload form
	 */
	function greet() {
		echo '<div class="narrow">';
		echo '<p>'.__( 'Hi there! Upload a CSV file containing product data to import the contents into your shop.', 'wc_csv_import' ).'</p>';
		echo '<p>'.__( 'Choose a CSV (.csv) file to upload, then click Upload file and import.', 'wc_csv_import' ).'</p>';
		//wp_import_upload_form( 'admin.php?import=woocommerce_csv&amp;step=1&amp;merge=' . ( ! empty( $_GET['merge'] ) ? 1 : 0 ) );

		$action = 'admin.php?import=woocommerce_csv&amp;step=1&amp;merge=' . ( ! empty( $_GET['merge'] ) ? 1 : 0 );

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
							<td><input type="text" name="delimiter" placeholder="," size="2" /></td>
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

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 60
	 */
	function bump_request_timeout() {
		return 60;
	}

	// return the difference in length between two strings
	function cmpr_strlen( $a, $b ) {
		return strlen($b) - strlen($a);
	}
}
}