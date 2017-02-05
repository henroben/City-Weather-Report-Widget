<?php

class City_Weather_Report_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'city_weather_report_widget', // Base ID
			esc_html__( 'City Weather Report Widget', 'cwr_domain' ), // Name
			array( 'description' => esc_html__( 'Simple weather widget', 'cwr_domain' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		// Get values
		$city = $instance['city'];
		$state = $instance['state'];
		$api_key = $instance['api_key'];
		$options = array(
			'temp_type'         => $instance['temp_type'],
			'use_geolocation'   => $instance['use_geolocation'] ? true : false,
			'show_humidity'     => $instance['show_humidity'] ? true : false,
			'show_realfeel'     => $instance['show_realfeel'] ? true : false,
			'show_forecast'     => $instance['show_forecast'] ? true : false,
			'cache_expire'     => $instance['cache_expire'] ? true : false
		);

		echo $args['before_widget'];

		echo $this->getWeather($city, $state, $api_key, $options, $args);

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$city = $instance['city'];
		$state = $instance['state'];
		$api_key = $instance['api_key'];
		$use_geolocation = $instance['use_geolocation'];
		$show_realfeel = $instance['show_realfeel'];
		$show_humidity = $instance['show_humidity'];
		$show_forecast = $instance['show_forecast'];
		$cache_expire = $instance['cache_expire'];
		$temp_type = $instance['temp_type'];

		?>
			<p>
				<input type="checkbox" class="checkbox"
					<?php checked( $instance['use_geolocation'], 'on'); ?>
						id="<?php echo $this->get_field_id( 'use_geolocation' ); ?>"
						name="<?php echo $this->get_field_name( 'use_geolocation' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'use_geolocation' ); ?>">Use Geolocation</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'api_key' ); ?>"><?php _e('<a href="http://www.wunderground.com">Wunderground API Key</a>:'); ?></label>
				<input type="text" class="widefat"
				       id="<?php echo $this->get_field_id( 'api_key' ); ?>"
				       name="<?php echo $this->get_field_name( 'api_key' ); ?>"
				       value="<?php echo esc_attr($api_key) ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e('City:'); ?></label>
				<input type="text" class="widefat"
						id="<?php echo $this->get_field_id( 'city' ); ?>"
						name="<?php echo $this->get_field_name( 'city' ); ?>"
						value="<?php echo esc_attr($city) ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'state' ); ?>"><?php _e('Country Code:'); ?></label>
				<input type="text" class="widefat"
				       id="<?php echo $this->get_field_id( 'state' ); ?>"
				       name="<?php echo $this->get_field_name( 'state' ); ?>"
				       value="<?php echo esc_attr($state) ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'temp_type' ); ?>"><?php _e('Temp Type:'); ?></label>
				<select
						name="<?php echo $this->get_field_name( 'temp_type' ); ?>"
						id="<?php echo $this->get_field_id( 'temp_type' ); ?>"
						class="widefat"  >
					<option value="Fahrenheit" <?php echo ($temp_type == 'Fahrenheit') ? 'selected' : ''; ?>>
						Fahrenheit
					</option>
					<option value="Celsius" <?php echo ($temp_type == 'Celsius') ? 'selected' : ''; ?>>
						Celsius
					</option>
					<option value="Both" <?php echo ($temp_type == 'Both') ? 'selected' : ''; ?>>
						Both
					</option>
				</select>
			</p>
			<p>
				<input type="checkbox" class="checkbox"
					<?php checked( $instance['show_realfeel'], 'on'); ?>
					   id="<?php echo $this->get_field_id( 'show_realfeel' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_realfeel' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_realfeel' ); ?>">Show Real Feel</label>
			</p>
			<p>
				<input type="checkbox" class="checkbox"
					<?php checked( $instance['show_humidity'], 'on'); ?>
					   id="<?php echo $this->get_field_id( 'show_humidity' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_humidity' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_humidity' ); ?>">Show Humidity</label>
			</p>
			<p>
				<input type="checkbox" class="checkbox"
					<?php checked( $instance['show_forecast'], 'on'); ?>
					   id="<?php echo $this->get_field_id( 'show_forecast' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_forecast' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_forecast' ); ?>">Show 3 Day Forecast</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'cache_expire' ); ?>"><?php _e('Cache Expiry In Seconds:'); ?></label>
				<input type="text" class="widefat"
				       id="<?php echo $this->get_field_id( 'cache_expire' ); ?>"
				       name="<?php echo $this->get_field_name( 'cache_expire' ); ?>"
				       value="<?php echo esc_attr($cache_expire) ?>" />
			</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// Process Widget options to be saved
		$instance = array(
			'title' => (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '',
			'city' => (!empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '',
			'state' => (!empty($new_instance['state'])) ? strip_tags($new_instance['state']) : '',
			'api_key' => (!empty($new_instance['api_key'])) ? strip_tags($new_instance['api_key']) : '',
			'use_geolocation' => (!empty($new_instance['use_geolocation'])) ? strip_tags($new_instance['use_geolocation']) : '',
			'show_realfeel' => (!empty($new_instance['show_realfeel'])) ? strip_tags($new_instance['show_realfeel']) : '',
			'show_humidity' => (!empty($new_instance['show_humidity'])) ? strip_tags($new_instance['show_humidity']) : '',
			'show_forecast' => (!empty($new_instance['show_forecast'])) ? strip_tags($new_instance['show_forecast']) : '',
			'cache_expire' => (!empty($new_instance['cache_expire'])) ? strip_tags($new_instance['cache_expire']) : '',
			'temp_type' => (!empty($new_instance['temp_type'])) ? strip_tags($new_instance['temp_type']) : '',
		);

		return $instance;
	}

	// Get And Display Weather
	function getWeather($city, $state, $api_key, $options, $args) {
		// GeoPlugin Init
		$geoplugin = new geoPlugin();
		$geoplugin->locate('86.12.242.36'); // remove before going live

		// Check to see if GeoPlugin enabled
		if($options['use_geolocation']) {
			$city = $geoplugin->city;
			$state = $geoplugin->countryCode;
		}

		if($api_key) {
			// set up request url first
			$request_url = 'http://api.wunderground.com/api/' . $api_key . '/geolookup/conditions/forecast/q/' . $state . '/' . $city . '.json';
			// Check cache for url
			$json_current_weather = get_transient($request_url);
			// If not in case, make request
			if($json_current_weather === false) {
				echo 'api request not cached, making new request.';
				$json_current_weather = file_get_contents($request_url);
				set_transient($request_url, $json_current_weather, $options['cache_expire']);
			}
			// Parse request
			$parsed_json = json_decode($json_current_weather);

			// Get Current Observations And Location
			$location = $parsed_json->{'location'}->{'city'} . ', ' . $parsed_json->{'location'}->{'country_name'};
			$weather = $parsed_json->{'current_observation'}->{'weather'};
			$icon = $parsed_json->{'current_observation'}->{'icon'};
			$temp_f = $parsed_json->{'current_observation'}->{'temp_f'};
			$feelslike_f = $parsed_json->{'current_observation'}->{'feelslike_f'};
			$temp_c = $parsed_json->{'current_observation'}->{'temp_c'};
			$feelslike_c = $parsed_json->{'current_observation'}->{'feelslike_c'};
			$relative_humidity = $parsed_json->{'current_observation'}->{'relative_humidity'};

			// Get Next 3 Day Forecast
			$threeDayForecast = $parsed_json->{'forecast'}->{'simpleforecast'}->{'forecastday'};
			// Don't need todays forecast, so start at [1]
			$forecastOne['day'] = $threeDayForecast[1]->{'date'}->{'day'};
			$forecastOne['month'] = $threeDayForecast[1]->{'date'}->{'monthname_short'};
			$forecastOne['temp_high_f'] = $threeDayForecast[1]->{'high'}->{'fahrenheit'};
			$forecastOne['temp_high_c'] = $threeDayForecast[1]->{'high'}->{'celsius'};
			$forecastOne['temp_low_f'] = $threeDayForecast[1]->{'low'}->{'fahrenheit'};
			$forecastOne['temp_low_c'] = $threeDayForecast[1]->{'low'}->{'celsius'};
			$forecastOne['icon'] = $threeDayForecast[1]->{'icon'};

			$forecastTwo['day'] = $threeDayForecast[2]->{'date'}->{'day'};
			$forecastTwo['month'] = $threeDayForecast[2]->{'date'}->{'monthname_short'};
			$forecastTwo['temp_high_f'] = $threeDayForecast[2]->{'high'}->{'fahrenheit'};
			$forecastTwo['temp_high_c'] = $threeDayForecast[2]->{'high'}->{'celsius'};
			$forecastTwo['temp_low_f'] = $threeDayForecast[2]->{'low'}->{'fahrenheit'};
			$forecastTwo['temp_low_c'] = $threeDayForecast[2]->{'low'}->{'celsius'};
			$forecastTwo['icon'] = $threeDayForecast[2]->{'icon'};

			$forecastThree['day'] = $threeDayForecast[3]->{'date'}->{'day'};
			$forecastThree['month'] = $threeDayForecast[3]->{'date'}->{'monthname_short'};
			$forecastThree['temp_high_f'] = $threeDayForecast[3]->{'high'}->{'fahrenheit'};
			$forecastThree['temp_high_c'] = $threeDayForecast[3]->{'high'}->{'celsius'};
			$forecastThree['temp_low_f'] = $threeDayForecast[3]->{'low'}->{'fahrenheit'};
			$forecastThree['temp_low_c'] = $threeDayForecast[3]->{'low'}->{'celsius'};
			$forecastThree['icon'] = $threeDayForecast[3]->{'icon'};
			?>
			<div class="city-weather" style="background-image: url(<?php echo plugins_url() . '/city-weather-report/img/' . ${icon} . '.png'; ?>); background-size: contain; background-repeat: no-repeat; background-position: center top;">
				<?php
					echo $args['before_title'];
					echo ${location};
					echo $args['after_title']; ?>
				<?php if($options['temp_type'] == 'Fahrenheit') : ?>
					<h1><?php echo ${temp_f}; ?>°F</h1>
				<?php elseif($options['temp_type'] == 'Celsius') : ?>
					<h1><?php echo ${temp_c}; ?>°C</h1>
				<?php elseif($options['temp_type'] == 'Both') : ?>
					<h1><?php echo ${temp_f}; ?>°F / <?php echo ${temp_c}; ?>°C</h1>
				<?php endif; ?>
				<!--				<img src="--><?php //echo plugins_url() . '/city-weather-report/img/' . ${icon} . '.png'; ?><!--" alt="--><?php //echo ${weather}; ?><!--"> --><?php //echo ${weather}; ?>
				<?php if($options['show_realfeel']) : ?>
					<div class="wcr-realfeel">
						<strong>Feels Like <?php echo ${feelslike_c}; ?>°C</strong>
					</div>
				<?php endif; ?>
				<?php if($options['show_humidity']) : ?>
					<div class="wcr-humidity">
						<strong>Relative Humidity: <?php echo ${relative_humidity}; ?></strong>
					</div>
				<?php endif; ?>
				<?php if($options['show_forecast']) : ?>
					<!-- Show 3 Day Forecast -->
					<div class="row forecast">
						<div class="col-xs-12">
							<div class="col-xs-4 day">
								<?php echo $forecastOne['day']; ?> <?php echo $forecastOne['month']; ?>
								<div class="col-xs-12">
									<img src="<?php echo plugins_url() . '/city-weather-report/img/' . $forecastOne['icon'] . '.png'; ?>" alt="">
								</div>
								<div class="col-xs-6 cwr-high-low">High</div>
								<div class="col-xs-6 cwr-high-low">Low</div>

									<?php if($options['temp_type'] == 'Fahrenheit') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastOne['temp_high_f']; ?>°F</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastOne['temp_low_f']; ?>°F</div>
									<?php elseif($options['temp_type'] == 'Celsius') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastOne['temp_high_c']; ?>°C</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastOne['temp_low_c']; ?>°C</div>
									<?php elseif($options['temp_type'] == 'Both') : ?>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastOne['temp_high_c']; ?>°C<br>
											<?php echo $forecastOne['temp_high_f']; ?>°F
										</div>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastOne['temp_low_c']; ?>°C<br>
											<?php echo $forecastOne['temp_low_f']; ?>°F
										</div>
									<?php endif; ?>

							</div>
							<div class="col-xs-4 day">
								<?php echo $forecastTwo['day']; ?> <?php echo $forecastTwo['month']; ?>
								<div class="col-xs-12">
									<img src="<?php echo plugins_url() . '/city-weather-report/img/' . $forecastTwo['icon'] . '.png'; ?>" alt="">
								</div>
								<div class="row">
									<div class="col-xs-6 cwr-high-low">High</div>
									<div class="col-xs-6 cwr-high-low">Low</div>


									<?php if($options['temp_type'] == 'Fahrenheit') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastTwo['temp_high_f']; ?>°F</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastTwo['temp_low_f']; ?>°F</div>
									<?php elseif($options['temp_type'] == 'Celsius') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastTwo['temp_high_c']; ?>°C</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastTwo['temp_low_c']; ?>°C</div>
									<?php elseif($options['temp_type'] == 'Both') : ?>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastTwo['temp_high_c']; ?>°C<br>
											<?php echo $forecastTwo['temp_high_f']; ?>°F
										</div>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastTwo['temp_low_c']; ?>°C<br>
											<?php echo $forecastTwo['temp_low_f']; ?>°F
										</div>
									<?php endif; ?>
								</div>
							</div>
							<div class="col-xs-4 day">
								<?php echo $forecastThree['day']; ?> <?php echo $forecastThree['month']; ?>
								<div class="col-xs-12">
									<img src="<?php echo plugins_url() . '/city-weather-report/img/' . $forecastThree['icon'] . '.png'; ?>" alt="">
								</div>
								<div class="col-xs-6 cwr-high-low">High</div>
								<div class="col-xs-6 cwr-high-low">Low</div>

									<?php if($options['temp_type'] == 'Fahrenheit') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastThree['temp_high_f']; ?>°F</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastThree['temp_low_f']; ?>°F</div>
									<?php elseif($options['temp_type'] == 'Celsius') : ?>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastThree['temp_high_c']; ?>°C</div>
										<div class="col-xs-6 cwr-high-low-data"><?php echo $forecastThree['temp_low_c']; ?>°C</div>
									<?php elseif($options['temp_type'] == 'Both') : ?>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastThree['temp_high_c']; ?>°C<br>
											<?php echo $forecastThree['temp_high_f']; ?>°F
										</div>
										<div class="col-xs-6 cwr-high-low-data">
											<?php echo $forecastThree['temp_low_c']; ?>°C<br>
											<?php echo $forecastThree['temp_low_f']; ?>°F
										</div>
									<?php endif; ?>

							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<?php
		} else {
			?>
			<div class="city-weather">
				<h3>No API Key Found</h3>
				<p>Please register for a free api key at <a href="http://www.wunderground.com">www.wunderground.com</a> and update your settings.</p>
			</div>
			<?php
		}
	}
}