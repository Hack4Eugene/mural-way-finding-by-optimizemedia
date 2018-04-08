<?php 

class w2dc_content_field_address extends w2dc_content_field {
	protected $can_be_required = true;
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;
	
	public function isNotEmpty($listing) {
		foreach ($listing->locations AS $location)
			if ($location->getWholeAddress())
				return true;

		return false;
	}

	public function renderOutput($listing) {
		if ($listing->level->locations_number)
			w2dc_renderTemplate('content_fields/fields/address_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function renderOutputForMap($location, $listing) {
		if ($listing->level->locations_number)
			return $location->getWholeAddress();
	}
}
?>