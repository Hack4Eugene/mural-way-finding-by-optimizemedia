<div class="stat ui-rater-single">
	<div class="statVal">
		<nobr>
			<span class="ui-rater">
				<span class="ui-rater-starsOff" style="width:100px;">
					<span class="ui-rater-starsOn" style="width: <?php echo $rating->value*20; ?>px" title="<?php printf(__('User rating: %d', 'W2DC'), $rating->value); ?>"></span>
				</span>
			</span>
		</nobr>
	</div>
</div>