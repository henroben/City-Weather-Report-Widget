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
		$options = array(
			'temp_type'         => $instance['temp_type'],
			'use_geolocation'   => $instance['use_geolocation'] ? true : false,
			'show_humidity'     => $instance['show_humidity'] ? true : false
		);

		echo $args['before_widget'];

		echo $this->getWeather($city, $state, $options);

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
		$use_geolocation = $instance['use_geolocation'];
		$show_humidity = $instance['show_humidity'];
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
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e('City:'); ?></label>
				<input type="text" class="widefat"
						id="<?php echo $this->get_field_id( 'city' ); ?>"
						name="<?php echo $this->get_field_name( 'city' ); ?>"
						value="<?php echo esc_attr($city) ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'state' ); ?>"><?php _e('State:'); ?></label>
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
					<?php checked( $instance['show_humidity'], 'on'); ?>
					   id="<?php echo $this->get_field_id( 'show_humidity' ); ?>"
					   name="<?php echo $this->get_field_name( 'show_humidity' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_humidity' ); ?>">Show Humidity</label>
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
			'use_geolocation' => (!empty($new_instance['use_geolocation'])) ? strip_tags($new_instance['use_geolocation']) : '',
			'show_humidity' => (!empty($new_instance['show_humidity'])) ? strip_tags($new_instance['show_humidity']) : '',
			'temp_type' => (!empty($new_instance['temp_type'])) ? strip_tags($new_instance['temp_type']) : '',
		);

		return $instance;
	}

	// Get And Display Weather
	function getWeather($city, $state, $options) {
		$json_string = file_get_contents('http://api.wunderground.com/api/8abaf1d660df37ed/geolookup/conditions/q/' . $state . '/' . $city . '.json');
//		$json_string = file_get_contents('http://api.wunderground.com/api/8abaf1d660df37ed/conditions/q/CA/San_Francisco.json');
		$parsed_json = json_decode($json_string);
		$location = $parsed_json->{'location'}->{'city'} . ', ' . $parsed_json->{'location'}->{'state'};
		$weather = $parsed_json->{'current_observation'}->{'weather'};
		$icon_url = $parsed_json->{'current_observation'}->{'icon_url'};
		$temp_f = $parsed_json->{'current_observation'}->{'temp_f'};
		$temp_c = $parsed_json->{'current_observation'}->{'temp_c'};
		$relative_humidity = $parsed_json->{'current_observation'}->{'relative_humidity'};
		?>
			<div class="city-weather">
				<h3><?php echo ${location}; ?></h3>
				<h1><?php echo ${temp_c}; ?>°C</h1>
				<img src="<?php echo ${icon_url}; ?>" alt="<?php echo ${weather}; ?>"> <?php echo ${weather}; ?>
				<div>
					<strong>Relative Humidity: <?php echo ${relative_humidity}; ?></strong>
				</div>
			</div>
		<?php
	}
}