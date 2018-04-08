	<form action="" method="POST" role="form">
		<input type="hidden" name="referer" value="<?php echo $frontend_controller->referer; ?>" />
		<input type="hidden" name="rich_editing" value="<?php echo ($frontend_controller->user->rich_editing) ? 1 : 0; ?>" />
		<input type="hidden" name="admin_color" value="<?php echo ($frontend_controller->user->admin_color) ? $frontend_controller->user->admin_color : 'fresh'; ?>" />
		<input type="hidden" name="admin_bar_front" value="<?php echo ($frontend_controller->user->show_admin_bar_front) ? 1 : 0; ?>" />

		<div class="w2dc-form-group">
			<p>
				<label for="user_login"><?php _e('Username', 'W2DC'); ?></label>
				<input type="text" name="user_login" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->user_login); ?>" disabled="disabled" /> <span class="description"><?php _e('Usernames cannot be changed.', 'W2DC'); ?></span>
			</p>
			<p>
				<label for="first_name"><?php _e('First Name', 'W2DC') ?></label>
				<input type="text" name="first_name" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->first_name); ?>" />
			</p>
			<p>
				<label for="last_name"><?php _e('Last Name', 'W2DC') ?></label>
				<input type="text" name="last_name" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->last_name); ?>" />
			</p>
			<p>
				<label for="nickname"><?php _e('Nickname', 'W2DC') ?> <span class="description"><?php _e('(required)', 'W2DC'); ?></span></label>
				<input type="text" name="nickname" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->nickname); ?>" />
			</p>
			<p>
				<label for="display_name"><?php _e('Display to Public as', 'W2DC') ?></label>
				<select name="display_name" class="w2dc-form-control">
				<?php
					$public_display = array();
					$public_display['display_username']  = $frontend_controller->user->user_login;
					$public_display['display_nickname']  = $frontend_controller->user->nickname;
					if (!empty($profileuser->first_name))
						$public_display['display_firstname'] = $frontend_controller->user->first_name;

					if (!empty($profileuser->last_name))
						$public_display['display_lastname'] = $frontend_controller->user->last_name;

					if (!empty($profileuser->first_name) && !empty($profileuser->last_name)) {
						$public_display['display_firstlast'] = $frontend_controller->user->first_name . ' ' . $frontend_controller->user->last_name;
						$public_display['display_lastfirst'] = $frontend_controller->user->last_name . ' ' . $frontend_controller->user->first_name;
					}

					if (!in_array($frontend_controller->user->display_name, $public_display)) // Only add this if it isn't duplicated elsewhere
						$public_display = array('display_displayname' => $frontend_controller->user->display_name) + $public_display;

					$public_display = array_map('trim', $public_display);
					$public_display = array_unique($public_display);
					foreach ($public_display as $id => $item) {
				?>
					<option id="<?php echo $id; ?>" value="<?php echo esc_attr($item); ?>"<?php selected($frontend_controller->user->display_name, $item); ?>><?php echo $item; ?></option>
				<?php
					}
				?>
				</select>
			</p>
			<p>
				<label for="email"><?php _e('E-mail', 'W2DC'); ?> <span class="description"><?php _e('(required)', 'W2DC'); ?></span></label>
				<input type="text" name="email" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->user_email); ?>" />
			</p>
			<?php if (!get_option('w2dc_hide_author_link')): ?>
			<p>
				<label for="email"><?php _e('Website', 'W2DC'); ?></label>
				<input type="text" name="url" class="w2dc-form-control" value="<?php echo esc_url(get_the_author_meta('url')); ?>" />
			</p>
			<?php endif; ?>
			<?php if (get_option('w2dc_payments_addon')): ?>
			<h3><?php _e('Billing information', 'W2DC'); ?></h3>
			<p>
				<label for="w2dc_billing_name"><?php _e('Full name', 'W2DC'); ?></label>
				<input type="text" name="w2dc_billing_name" class="w2dc-form-control" value="<?php echo esc_attr($frontend_controller->user->w2dc_billing_name) ?>" />
			</p>
			<p>
				<label for="w2dc_billing_address"><?php _e('Full Address', 'W2DC'); ?></label>
				<textarea name="w2dc_billing_address" id="w2dc_billing_address" class="w2dc-form-control" rows="3"><?php echo esc_textarea($frontend_controller->user->w2dc_billing_address); ?></textarea>
			</p>
			<?php endif; ?>
			 <div>
			 	<label for="pass1"><?php _e('New Password', 'W2DC'); ?></label>
			 	<div class="user-pass1-wrap">
					<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php _e('Generate Password', 'W2DC'); ?></button>
					<div class="wp-pwd hide-if-js">
						<span class="password-input-wrapper">
							<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr(wp_generate_password(24)); ?>" aria-describedby="pass-strength-result" />
							<div class="user-pass2-wrap hide-if-js"><input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" /></div>
						</span>
						<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e('Hide password', 'W2DC'); ?>">
							<span class="dashicons dashicons-hidden"></span>
							<span class="text"><?php _e('Hide', 'W2DC'); ?></span>
						</button>
						<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e('Cancel password change', 'W2DC'); ?>">
							<span class="text"><?php _e('Cancel', 'W2DC'); ?></span>
						</button>
						<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
					</div>
			 	</div>
			 </div>
		</div>

		<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($frontend_controller->user->ID); ?>" />
		<?php require_once(ABSPATH . 'wp-admin/includes/template.php'); ?>
		<?php submit_button(__('Save changes', 'W2DC'), 'w2dc-btn w2dc-btn-primary'); ?>
	</form>