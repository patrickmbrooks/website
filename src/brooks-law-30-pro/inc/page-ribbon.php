<?php
/**
 * Page Title Ribbons — v2.3.2.
 *
 * A decorative title band (icon — title — icon) rendered automatically
 * above the content of every page and post (front page excluded). The
 * navy band can be replaced by a photo per page. Sensible defaults are
 * chosen from the page slug (traffic, drugs, DUI, immigration, etc.),
 * and every page/post gets a "Page Ribbon" box in the editor to
 * override the title, pick left/right icons from the shared library,
 * set a background photo, or hide the ribbon on that page.
 *
 * The title is a styled <p>, never a heading, so H1/SEO is untouched.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------
 * Defaults by slug — the "deep dive" map of the site's clusters.
 * First match wins; keys are substrings of the slug.
 * ---------------------------------------------------------------- */
function brooks_law_ribbon_auto( $slug ) {
	$map = array(
		'cdl'                 => array( 'Traffic Matters', 'semi', 'stoplight' ),
		'traffic'             => array( 'Traffic Matters', 'semi', 'stoplight' ),
		'speeding'            => array( 'Traffic Matters', 'hotrod', 'stoplight' ),
		'reckless'            => array( 'Traffic Matters', 'hotrod', 'stoplight' ),
		'suspended-license'   => array( 'Traffic Matters', 'stoplight', 'document' ),
		'lookout'             => array( 'Traffic Matters', 'stoplight', 'document' ),
		'dui'                 => array( 'DUI Defense', 'shield', 'scales' ),
		'drug'                => array( 'Drug Charges', 'pills', 'scales' ),
		'fentanyl'            => array( 'Drug Charges', 'pills', 'scales' ),
		'ecstasy'             => array( 'Drug Charges', 'pills', 'scales' ),
		'domestic'            => array( 'Domestic Assault Defense', 'shield', 'scales' ),
		'order-of-protection' => array( 'Domestic Assault Defense', 'shield', 'document' ),
		'assault'             => array( 'Criminal Defense', 'shield', 'gavel' ),
		'theft'               => array( 'Criminal Defense', 'shield', 'gavel' ),
		'probation'           => array( 'Probation Violations', 'document', 'gavel' ),
		'appeal'              => array( 'Criminal Appeals', 'pen', 'scales' ),
		'federal'             => array( 'Federal Defense', 'eagle', 'scales' ),
		'white-collar'        => array( 'White Collar Defense', 'pen', 'document' ),
		'gun'                 => array( 'Weapons Charges', 'gun', 'scales' ),
		'weapon'              => array( 'Weapons Charges', 'gun', 'scales' ),
		'expunge'             => array( 'Expungement', 'document', 'pen' ),
		'veteran'             => array( 'Veterans Defense', 'eagle', 'shield' ),
		'immigration'         => array( 'Immigration Resources', 'scroll', 'eagle' ),
		'resources'           => array( 'Legal Resources', 'scroll', 'pen' ),
		'divorce'             => array( 'Family Law', 'document', 'scales' ),
		'wrongful-death'      => array( 'Civil Litigation', 'scales', 'document' ),
		'personal-injury'     => array( 'Civil Litigation', 'scales', 'document' ),
		'litigation'          => array( 'Civil Litigation', 'scales', 'document' ),
		'courts-we-serve'     => array( 'Courts We Serve', 'courthouse', 'pin' ),
		'county-criminal'     => array( 'Courts We Serve', 'courthouse', 'pin' ),
		'criminal-defense'    => array( 'Criminal Defense', 'scales', 'gavel' ),
		'brooks'              => array( 'Brooks Law Firm', 'scales', 'pillar' ),
		'firm-profile'        => array( 'Brooks Law Firm', 'scales', 'pillar' ),
		'contact'             => array( 'Brooks Law Firm', 'scales', 'pillar' ),
	);
	foreach ( $map as $needle => $set ) {
		if ( false !== strpos( $slug, $needle ) ) {
			return $set;
		}
	}
	return array( 'Brooks Law Firm', 'scales', 'gavel' );
}

/* -------------------------------------------------------------------
 * Customizer: global switch + fallback title/icons.
 * ---------------------------------------------------------------- */
function brooks_law_page_ribbon_customize( $wp_customize ) {

	$wp_customize->add_section( 'brooks_law_page_ribbon', array(
		'title'       => __( 'Page Title Ribbons', 'brooks-law-30-pro' ),
		'description' => __( 'A navy title band with brass icons shown at the top of every page and post. Each page has its own Page Ribbon box in the editor to change the title, icons, or photo — or hide the ribbon on that page.', 'brooks-law-30-pro' ),
		'priority'    => 133,
	) );

	$wp_customize->add_setting( 'pr_enable', array(
		'default'           => true,
		'sanitize_callback' => 'brooks_law_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'pr_enable', array(
		'section' => 'brooks_law_page_ribbon',
		'label'   => __( 'Show page title ribbons site-wide', 'brooks-law-30-pro' ),
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'pr_posts', array(
		'default'           => true,
		'sanitize_callback' => 'brooks_law_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'pr_posts', array(
		'section' => 'brooks_law_page_ribbon',
		'label'   => __( 'Also show on blog posts', 'brooks-law-30-pro' ),
		'type'    => 'checkbox',
	) );
}
add_action( 'customize_register', 'brooks_law_page_ribbon_customize', 20 );

/* -------------------------------------------------------------------
 * Editor meta box.
 * ---------------------------------------------------------------- */
function brooks_law_ribbon_meta_box() {
	foreach ( array( 'page', 'post' ) as $type ) {
		add_meta_box( 'brooks_ribbon', __( 'Page Ribbon', 'brooks-law-30-pro' ), 'brooks_law_ribbon_meta_box_html', $type, 'side' );
	}
}
add_action( 'add_meta_boxes', 'brooks_law_ribbon_meta_box' );

function brooks_law_ribbon_meta_box_html( $post ) {
	wp_nonce_field( 'brooks_ribbon_save', 'brooks_ribbon_nonce' );

	$auto  = brooks_law_ribbon_auto( $post->post_name ? $post->post_name : '' );
	$show  = get_post_meta( $post->ID, '_br_ribbon', true );
	$title = get_post_meta( $post->ID, '_br_ribbon_title', true );
	$left  = get_post_meta( $post->ID, '_br_ribbon_icon_l', true );
	$right = get_post_meta( $post->ID, '_br_ribbon_icon_r', true );
	$photo = absint( get_post_meta( $post->ID, '_br_ribbon_photo', true ) );
	$icons = brooks_law_sa_icons();

	echo '<p><label><input type="checkbox" name="br_ribbon_hide" value="1" ' . checked( 'off', $show, false ) . '> ' . esc_html__( 'Hide ribbon on this page', 'brooks-law-30-pro' ) . '</label></p>';

	echo '<p><label for="br_ribbon_title"><strong>' . esc_html__( 'Title', 'brooks-law-30-pro' ) . '</strong></label><br>';
	echo '<input type="text" id="br_ribbon_title" name="br_ribbon_title" value="' . esc_attr( $title ) . '" placeholder="' . esc_attr( $auto[0] ) . '" style="width:100%">';
	echo '<span class="description">' . esc_html__( 'Blank = automatic title shown above.', 'brooks-law-30-pro' ) . '</span></p>';

	foreach ( array( 'l' => __( 'Left icon', 'brooks-law-30-pro' ), 'r' => __( 'Right icon', 'brooks-law-30-pro' ) ) as $side => $label ) {
		$current = 'l' === $side ? $left : $right;
		$auto_ic = 'l' === $side ? $auto[1] : $auto[2];
		echo '<p><label for="br_ribbon_icon_' . esc_attr( $side ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		echo '<select id="br_ribbon_icon_' . esc_attr( $side ) . '" name="br_ribbon_icon_' . esc_attr( $side ) . '" style="width:100%">';
		$auto_label = isset( $icons[ $auto_ic ]['label'] ) ? $icons[ $auto_ic ]['label'] : __( 'none', 'brooks-law-30-pro' );
		echo '<option value="">' . esc_html( sprintf( /* translators: %s: icon name. */ __( 'Automatic (%s)', 'brooks-law-30-pro' ), $auto_label ) ) . '</option>';
		foreach ( $icons as $key => $icon ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $current, $key, false ) . '>' . esc_html( $icon['label'] ) . '</option>';
		}
		echo '</select></p>';
	}

	if ( function_exists( 'brooks_law_ribbon_art' ) ) {
		$art_key  = get_post_meta( $post->ID, '_br_ribbon_art', true );
		$art_side = get_post_meta( $post->ID, '_br_ribbon_art_side', true );
		$art_op   = get_post_meta( $post->ID, '_br_ribbon_art_opacity', true );
		$art_lib  = brooks_law_ribbon_art();
		$art_def  = brooks_law_sanitize_ribbon_art( (string) brooks_law_get_option( 'ribbon_art_default' ) );

		echo '<hr style="margin:14px 0">';
		echo '<p><label for="br_ribbon_art"><strong>' . esc_html__( 'Background artwork', 'brooks-law-30-pro' ) . '</strong></label><br>';
		echo '<select id="br_ribbon_art" name="br_ribbon_art" style="width:100%">';
		echo '<option value="">' . esc_html(
			$art_def
				? sprintf( /* translators: %s: motif name. */ __( 'Site default (%s)', 'brooks-law-30-pro' ), $art_lib[ $art_def ]['label'] )
				: __( 'Site default (none)', 'brooks-law-30-pro' )
		) . '</option>';
		echo '<option value="none" ' . selected( $art_key, 'none', false ) . '>' . esc_html__( '— No artwork —', 'brooks-law-30-pro' ) . '</option>';
		foreach ( $art_lib as $key => $item ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $art_key, $key, false ) . '>' . esc_html( $item['label'] ) . '</option>';
		}
		echo '</select>';
		echo '<span class="description">' . esc_html__( 'Ignored when a background photo is set.', 'brooks-law-30-pro' ) . '</span></p>';

		echo '<p><label for="br_ribbon_art_side"><strong>' . esc_html__( 'Artwork side', 'brooks-law-30-pro' ) . '</strong></label><br>';
		echo '<select id="br_ribbon_art_side" name="br_ribbon_art_side" style="width:100%">';
		echo '<option value="">' . esc_html__( 'Automatic', 'brooks-law-30-pro' ) . '</option>';
		echo '<option value="left" ' . selected( $art_side, 'left', false ) . '>' . esc_html__( 'Left (text sits right)', 'brooks-law-30-pro' ) . '</option>';
		echo '<option value="right" ' . selected( $art_side, 'right', false ) . '>' . esc_html__( 'Right (text sits left)', 'brooks-law-30-pro' ) . '</option>';
		echo '</select></p>';

		echo '<p><label for="br_ribbon_art_opacity"><strong>' . esc_html__( 'Artwork opacity', 'brooks-law-30-pro' ) . '</strong></label><br>';
		echo '<input type="number" id="br_ribbon_art_opacity" name="br_ribbon_art_opacity" min="8" max="45" step="1" value="' . esc_attr( '' !== $art_op ? $art_op : '' ) . '" placeholder="' . esc_attr( (string) brooks_law_get_option( 'ribbon_art_opacity' ) ) . '" style="width:90px"> %';
		echo '<br><span class="description">' . esc_html__( 'Blank uses the site default. 8 to 45.', 'brooks-law-30-pro' ) . '</span></p>';
	}

	echo '<p><strong>' . esc_html__( 'Background photo', 'brooks-law-30-pro' ) . '</strong><br>';
	echo '<span class="description">' . esc_html__( 'Replaces the navy band. Leave empty for the standard blue ribbon.', 'brooks-law-30-pro' ) . '</span></p>';
	echo '<div id="br_ribbon_photo_preview" style="margin-bottom:6px">';
	if ( $photo ) {
		echo wp_get_attachment_image( $photo, 'medium', false, array( 'style' => 'max-width:100%;height:auto;border-radius:4px' ) );
	}
	echo '</div>';
	echo '<input type="hidden" id="br_ribbon_photo" name="br_ribbon_photo" value="' . esc_attr( $photo ? $photo : '' ) . '">';
	echo '<button type="button" class="button" id="br_ribbon_photo_pick">' . esc_html__( 'Choose photo', 'brooks-law-30-pro' ) . '</button> ';
	echo '<button type="button" class="button" id="br_ribbon_photo_clear"' . ( $photo ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Remove', 'brooks-law-30-pro' ) . '</button>';
	?>
	<script>
	jQuery(function($){
		var frame;
		$('#br_ribbon_photo_pick').on('click', function(e){
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: '<?php echo esc_js( __( 'Ribbon photo', 'brooks-law-30-pro' ) ); ?>', multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('#br_ribbon_photo').val(att.id);
				var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
				$('#br_ribbon_photo_preview').html('<img src="'+url+'" style="max-width:100%;height:auto;border-radius:4px">');
				$('#br_ribbon_photo_clear').show();
			});
			frame.open();
		});
		$('#br_ribbon_photo_clear').on('click', function(){
			$('#br_ribbon_photo').val('');
			$('#br_ribbon_photo_preview').empty();
			$(this).hide();
		});
	});
	</script>
	<?php
}

function brooks_law_ribbon_admin_media( $hook ) {
	if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'brooks_law_ribbon_admin_media' );

function brooks_law_ribbon_save( $post_id ) {
	if ( ! isset( $_POST['brooks_ribbon_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['brooks_ribbon_nonce'] ), 'brooks_ribbon_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_br_ribbon', isset( $_POST['br_ribbon_hide'] ) ? 'off' : '' );

	$title = isset( $_POST['br_ribbon_title'] ) ? sanitize_text_field( wp_unslash( $_POST['br_ribbon_title'] ) ) : '';
	if ( '' !== $title ) {
		update_post_meta( $post_id, '_br_ribbon_title', $title );
	} else {
		delete_post_meta( $post_id, '_br_ribbon_title' );
	}

	foreach ( array( 'l', 'r' ) as $side ) {
		$key = 'br_ribbon_icon_' . $side;
		$val = isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '' !== $val && array_key_exists( $val, brooks_law_sa_icons() ) ) {
			update_post_meta( $post_id, '_br_ribbon_icon_' . $side, $val );
		} else {
			delete_post_meta( $post_id, '_br_ribbon_icon_' . $side );
		}
	}

	$photo = isset( $_POST['br_ribbon_photo'] ) ? absint( $_POST['br_ribbon_photo'] ) : 0;
	if ( $photo ) {
		update_post_meta( $post_id, '_br_ribbon_photo', $photo );
	} else {
		delete_post_meta( $post_id, '_br_ribbon_photo' );
	}
}
add_action( 'save_post', 'brooks_law_ribbon_save' );

/* -------------------------------------------------------------------
 * Front-end render.
 * ---------------------------------------------------------------- */
function brooks_law_page_ribbon_matches() {
	if ( ! get_theme_mod( 'pr_enable', true ) || is_front_page() ) {
		return false;
	}
	if ( is_singular( 'page' ) ) {
		return true;
	}
	if ( is_singular( 'post' ) && get_theme_mod( 'pr_posts', true ) ) {
		return true;
	}
	return false;
}

function brooks_law_page_ribbon_markup() {
	$post_id = get_queried_object_id();
	if ( 'off' === get_post_meta( $post_id, '_br_ribbon', true ) ) {
		return '';
	}

	$icons = brooks_law_sa_icons();
	$auto  = brooks_law_ribbon_auto( get_post_field( 'post_name', $post_id ) );

	$title = get_post_meta( $post_id, '_br_ribbon_title', true );
	$title = '' !== $title ? $title : $auto[0];

	$left = get_post_meta( $post_id, '_br_ribbon_icon_l', true );
	$left = ( $left && isset( $icons[ $left ] ) ) ? $left : $auto[1];
	$left = isset( $icons[ $left ] ) ? $left : '';

	$right = get_post_meta( $post_id, '_br_ribbon_icon_r', true );
	$right = ( $right && isset( $icons[ $right ] ) ) ? $right : $auto[2];
	$right = isset( $icons[ $right ] ) ? $right : '';

	$photo_id  = absint( get_post_meta( $post_id, '_br_ribbon_photo', true ) );
	$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'large' ) : '';

	$style = '';
	$class = 'page-ribbon';
	if ( $photo_url ) {
		$class .= ' has-photo';
		$style  = ' style="background-image:linear-gradient(rgba(18,32,46,.62),rgba(18,32,46,.62)),url(\'' . esc_url( $photo_url ) . '\')"';
	}

	$out  = '<div class="' . esc_attr( $class ) . '" role="presentation"' . $style . '>';
	if ( isset( $icons[ $left ]['svg'] ) ) {
		$out .= '<svg class="page-ribbon__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $left ]['svg'] . '</svg>';
	}
	$out .= '<p class="page-ribbon__title">' . esc_html( $title ) . '</p>';
	if ( isset( $icons[ $right ]['svg'] ) ) {
		$out .= '<svg class="page-ribbon__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $right ]['svg'] . '</svg>';
	}
	$out .= '</div>';

	return $out;
}

function brooks_law_page_ribbon_prepend( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( ! brooks_law_page_ribbon_matches() ) {
		return $content;
	}
	if ( get_queried_object_id() !== get_the_ID() ) {
		return $content;
	}

	return brooks_law_page_ribbon_markup() . $content;
}
add_filter( 'the_content', 'brooks_law_page_ribbon_prepend', 8 );
