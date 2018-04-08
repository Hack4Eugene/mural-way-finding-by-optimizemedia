<?php 

class w2dc_content_field_excerpt extends w2dc_content_field {
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;
	
	public function isNotEmpty($listing) {
		if (post_type_supports(W2DC_POST_TYPE, 'excerpt') && ($listing->post->post_excerpt || (get_option('w2dc_cropped_content_as_excerpt') && $listing->post->post_content !== '')))
			return true;
		else
			return false;
	}

	public function validateValues(&$errors, $data) {
		$listing = w2dc_getCurrentListingInAdmin();
		if (post_type_supports(W2DC_POST_TYPE, 'excerpt') && $this->is_required && (!isset($data['post_excerpt']) || !$data['post_excerpt']))
			$errors[] = __('Listing excerpt is required', 'W2DC');
		else
			return $listing->post->post_excerpt;
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/excerpt_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function renderOutputForMap($location, $listing) {
		return $listing->post->post_excerpt;
	}
}
?>