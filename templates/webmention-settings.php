<div class="wrap">
	<h1><?php esc_html_e( 'Webmention Settings', 'webmention' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'webmention' ); ?>

		<h2 class="title"><?php _e( 'Sender', 'webmention' ); ?></h2>

		<?php if ( ! class_exists( 'Semantic_Linkbacks_Plugin' ) ) : ?>
		<div class="notice notice-warning">
			<p><?php printf( __( 'The Webmention plugin primarily handles sending/receiving notifications of mentions from other websites, so the format of the comments can look odd on one\'s site. We highly recommend also installing and activating the <a class="thickbox open-plugin-details-modal" href="%1$s" target_"blank">Semantic Linkbacks Plugin</a> which has better parsing and display capabilities to allow richer looking comments as well as options for displaying many reply types as facepiles for improved user interface.', 'webmention' ), admin_url( '/plugin-install.php?tab=plugin-information&plugin=semantic-linkbacks&TB_iframe=true' ) ); ?></p>
		</div>
		<?php endif; ?>

		<p><?php esc_html_e( 'A Webmention Sender is an implementation that sends Webmentions.', 'webmention' ); ?></p>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Self-Ping settings', 'webmention' ); ?></th>
				<td>
					<fieldset>
						<label for="webmention_disable_selfpings_same_url">
							<input type="checkbox" name="webmention_disable_selfpings_same_url" id="webmention_disable_selfpings_same_url" value="1" <?php echo checked( true, get_option( 'webmention_disable_selfpings_same_url' ) ); ?> />
							<?php esc_html_e( 'Disable self-pings on the same URL', 'webmention' ); ?>
							<p class="description"><?php esc_html_e( '(for example "http://example.com/?p=123")', 'webmention' ); ?></p>
						</label>

						<br />

						<label for="webmention_disable_selfpings_same_domain">
							<input type="checkbox" name="webmention_disable_selfpings_same_domain" id="webmention_disable_selfpings_same_domain" value="1" <?php echo checked( true, get_option( 'webmention_disable_selfpings_same_domain' ) ); ?> />
							<?php esc_html_e( 'Disable self-pings on the same Domain', 'webmention' ); ?>
							<p class="description"><?php esc_html_e( '(for example "example.com")', 'webmention' ); ?></p>
						</label>

						<br />

						<label for="webmention_disable_media_mentions">
							<input type="checkbox" name="webmention_disable_media_mentions" id="webmention_disable_media_mentions" value="1" <?php echo checked( true, get_option( 'webmention_disable_media_mentions' ) ); ?> />
							<?php esc_html_e( 'Disable sending webmentions for media links(image, video, audio)', 'webmention' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<?php do_settings_fields( 'webmention', 'sender' ); ?>

		<h2 class="title"><?php esc_html_e( 'Receiver', 'webmention' ); ?></h2>

		<p><?php esc_html_e( 'A Webmention Receiver is an implementation that receives Webmentions to one or more target URLs on which the Receiver\'s Webmention endpoint is advertised.', 'webmention' ); ?></p>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Webmention support for post types', 'webmention' ); ?></th>
				<td>
					<fieldset>
						<?php esc_html_e( 'Enable Webmention support for the following post types:', 'webmention' ); ?>

						<?php $post_types = get_post_types( array( 'public' => true ), 'objects' ); ?>
						<?php $support_post_types = get_option( 'webmention_support_post_types', array( 'post', 'page' ) ) ? get_option( 'webmention_support_post_types', array( 'post', 'page' ) ) : array(); ?>
						<ul>
						<?php foreach ( $post_types as $post_type ) { ?>
							<li>
								<input type="checkbox" id="webmention_support_post_types" name="webmention_support_post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php echo checked( true, in_array( $post_type->name, $support_post_types, true ) ); ?> />
								<label for="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->label ); ?></label>
							</li>
						<?php } ?>
						</ul>

						<br />

						<label for="webmention_home_mentions">
							<?php esc_html_e( 'Set a page for mentions of the homepage to be sent to:', 'webmention' ); ?>

							<?php
							wp_dropdown_pages(
								array(
									'show_option_none' => esc_html__( 'No homepage mentions', 'webmention' ),
									'name'             => 'webmention_home_mentions',
									'id'               => 'webmention_home_mentions',
									'selected'         => get_option( 'webmention_home_mentions' ),
								)
							);
							?>

							<?php
							if ( get_option( 'webmention_home_mentions' ) ) {
								printf( '<a href="%s">%s</a>', get_permalink( get_option( 'webmention_home_mentions' ) ), esc_html__( 'Visit site', 'webmention' ) );
							}
							?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Automatically approve Webmention from these domains', 'webmention' ); ?></p></th>
				<td>
					<fieldset>
						<p>
							<textarea name="webmention_approve_domains" id="webmention_approve_domains" rows="10" cols="50" class="large-text code"><?php echo get_option( 'webmention_approve_domains' ); ?></textarea>
						</p>
						<p class="description">
							<label for="webmention_approve_domains">
								<?php esc_html_e( 'A Webmention received from a site that matches a domain in this list will be auto-approved. One domain (e.g. indieweb.org) per line.', 'webmention' ); ?>
							</label>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Comment settings', 'webmention' ); ?></th>
				<td>
					<fieldset>
						<p>
							<label for="webmention_show_comment_form">
								<input type="checkbox" name="webmention_show_comment_form" id="webmention_show_comment_form" value="1" <?php echo checked( true, get_option( 'webmention_show_comment_form' ) ); ?> />
								<?php esc_html_e( 'Show a Webmention form at the comment section, to allow anyone to notify you of a mention.', 'webmention' ); ?>
							</label>
						</p>

						<p>
							<textarea name="webmention_comment_form_text" id="webmention_comment_form_text" rows="10" cols="50" class="large-text code" placeholder="<?php echo esc_html( get_default_webmention_form_text() ); ?>"><?php echo get_option( 'webmention_comment_form_text', '' ); ?></textarea>
						</p>
						<p class="description">
							<label for="webmention_comment_form_text">
								<?php esc_html_e( 'Change the default help text of the Webmention form', 'webmention' ); ?>
							</label>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Avatars', 'webmention' ); ?></th>
				<td>
					<fieldset>
						<label for="webmention_avatars">
							<input type="checkbox" name="webmention_avatars" id="webmention_avatars" value="1" <?php echo checked( true, get_option( 'webmention_avatars', 1 ) ); ?> />
							<?php esc_html_e( 'Show avatars on webmentions if available.', 'webmention' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<?php do_settings_fields( 'webmention', 'receiver' ); ?>

		<?php do_settings_sections( 'webmention' ); ?>

		<?php submit_button(); ?>
<?php if ( WP_DEBUG ) { ?>
	</form>
		<div class="wrap">
			<h2> <?php esc_html_e( 'Webmention Parsing Debugger', 'webmention' ); ?> </h2>
				<p> <?php esc_html_e( 'You can report sites to the developer for possibly improvement in future', 'webmention' ); ?>
				</p>
				<a href="https://github.com/pfefferle/wordpress-webmention/issues"><?php esc_html_e( 'Open an Issue', 'webmention' ); ?></a>
				<p> 
				<hr />
			<form method="get" action="<?php echo esc_url( rest_url( '/webmention/1.0/parse/' ) ); ?> ">
				<p><label for="url"><?php esc_html_e( 'URL', 'webmention' ); ?></label><input type="url" class="widefat" name="url" id="url" /></p>
				<?php wp_nonce_field( 'wp_rest' ); ?>
				<?php submit_button( __( 'Parse', 'webmention' ) ); ?>
			</form>
		</div>
<?php } ?>
</div>
