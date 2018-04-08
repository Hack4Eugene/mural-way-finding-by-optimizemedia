<?php

$invoice_id = $_GET['invoice_id'];
$invoice = getInvoiceByID($invoice_id);


?>

<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php wp_head(); ?>
	
	<style type="text/css">
	.w2dc-print-buttons {
		margin: 10px;
	}
	@media print {
		.w2dc-print-buttons {
			display: none;
		}
	}
	</style>
</head>

<body <?php body_class(); ?> style="background-color: #FFF">
	<div id="page" class="hfeed site">
		<div id="main" class="wrapper">
			<div class="entry-content">
				<div class="w2dc-print-buttons">
					<input type="button" onclick="window.print();" value="<?php esc_attr_e('Print invoice', 'W2DC'); ?>">&nbsp;&nbsp;&nbsp;<input type="button" onclick="window.close();" value="<?php esc_attr_e('Close window', 'W2DC'); ?>">
				</div>

				<?php if (get_option('w2dc_allow_bank') && get_option('w2dc_bank_info')): ?>
				<h4><?php _e('Bank transfer information', 'W2DC'); ?></h4>
				<?php echo nl2br(get_option('w2dc_bank_info')); ?>
				<?php endif; ?>
				
				<br />
				<br />
				<br />
				<h4><?php _e('Invoice Info', 'W2DC'); ?></h4>
				<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'info_metabox.tpl.php'), array('invoice' => $invoice)); ?>
				
				<br />
				<br />
				<br />
				<h4><?php _e('Invoice Log', 'W2DC'); ?></h4>
				<?php w2dc_renderTemplate(array(W2DC_PAYMENTS_TEMPLATES_PATH, 'log_metabox.tpl.php'), array('invoice' => $invoice)); ?>

				<div class="w2dc-print-buttons">
					<input type="button" onclick="window.print();" value="<?php esc_attr_e('Print invoice', 'W2DC'); ?>">&nbsp;&nbsp;&nbsp;<input type="button" onclick="window.close();" value="<?php esc_attr_e('Close window', 'W2DC'); ?>">
				</div>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>