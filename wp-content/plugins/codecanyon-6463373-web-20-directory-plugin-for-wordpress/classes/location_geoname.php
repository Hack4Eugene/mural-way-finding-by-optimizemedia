<?php

class w2dc_locationGeoname {

	public function geonames_request($query, $return = 'geoname') {
		$use_districts = true;
		$use_provinces = true;
		
		if (get_option('w2dc_address_autocomplete_code')) {
			$iso3166 = strtolower(get_option('w2dc_address_autocomplete_code'));
			if ($iso3166 == 'gb')
				$iso3166 = 'uk';
			$region = '&region='.$iso3166;
		} else 
			$region = '';

		if (get_option('w2dc_google_api_key_server'))
			//$fullUrl = sprintf("https://maps.googleapis.com/maps/api/geocode/json?address=%s&language=en&key=%s%s", urlencode($query), get_option('w2dc_google_api_key_server'), $region);
			$fullUrl = sprintf("https://maps.googleapis.com/maps/api/place/textsearch/json?query=%s&language=en&key=%s%s", urlencode($query), get_option('w2dc_google_api_key_server'), $region);
		else
			//$fullUrl = sprintf("https://maps.googleapis.com/maps/api/geocode/json?address=%s&language=en%s", urlencode($query), $region);
			$fullUrl = sprintf("https://maps.googleapis.com/maps/api/place/textsearch/json?query=%s&language=en%s", urlencode($query), $region);
		$ch = curl_init($fullUrl);
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		$ret = json_decode($response, true);

		if ($return == 'test') {
			if (curl_errno($ch)) {
				return curl_error($ch);
			} else {
				return $ret;
			}
		}

		curl_close($ch);

		if ($ret && $ret["status"] == "OK") {
			if ($return == 'coordinates') {
				return array($ret["results"][0]["geometry"]["location"]["lng"], $ret["results"][0]["geometry"]["location"]["lat"]);
			} elseif ($return == 'geoname') {
				$geocoded_name = array();
				foreach ($ret["results"][0]["address_components"] AS $component) {
					if (@$component["types"][0] == "sublocality") {
						$town = $component["long_name"];
						$geocoded_name[] = $town;
					}
					if (@$component["types"][0] == "locality") {
						$city = $component["long_name"];
						$geocoded_name[] = $city;
					}
					if ($use_districts)
						if (@$component["types"][0] == "administrative_area_level_3") {
							$district = $component["long_name"];
							$geocoded_name[] = $district;
						}
					if ($use_provinces)
						if (@$component["types"][0] == "administrative_area_level_2") {
							$province = $component["long_name"];
							$geocoded_name[] = $province;
						}
					if (@$component["types"][0] == "administrative_area_level_1") {
						$state = $component["long_name"];
						$geocoded_name[] = $state;
					}
					if (@$component["types"][0] == "country") {
						$country = $component["long_name"];
						$geocoded_name[] = $country;
					}
				}
				return implode(', ', $geocoded_name);
			} elseif ($return == 'address') {
				return @$ret["results"][0]["formatted_address"];
			}
		} else {
			return '';
		}
	}
}
?>