			<tr>
				<th scope="row">
					<label><?php _e('Listings price', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="price"
						type="text"
						size="5"
						value="<?php if (isset($level->price)) echo $level->price; ?>" /> <?php echo get_option('w2dc_payments_symbol_code'); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Listings raise up price', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="raiseup_price"
						type="text"
						size="5"
						value="<?php if (isset($level->raiseup_price)) echo $level->raiseup_price; ?>" /> <?php echo get_option('w2dc_payments_symbol_code'); ?>
				</td>
			</tr>