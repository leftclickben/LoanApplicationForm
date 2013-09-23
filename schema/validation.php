<?php

return call_user_func(function () {
	/**
	 * Messages, collected here for ease of modification and potential reuse
	 */
	$messages = array(
		'loan_amount' => array(
			'out_of_range' => 'Loan amount must be between $100 and $2000'
		),
		'loan_term_weeks' => array(
			'invalid' => 'Only Australian residents can apply for a 12 month loan'
		),
		'first_name' => array(
			'required' => 'Please supply your first name'
		),
		'last_name' => array(
			'required' => 'Please supply your last name'
		),
		'email' => array(
			'match' => 'Please supply your email address in the form "user@domain.com"'
		),
		'phone_primary' => array(
			'match' => 'Please supply your 10-digit Australian phone number including area code'
		),
		'phone_secondary' => array(
			'match' => 'Please supply your 10-digit Australian phone number including area code'
		)
	);

	/**
	 * Regular expression patterns, collected here for ease of modification and potential reuse.
	 */
	$regexPatterns = array(
		'email' => '/^[a-zA-Z][a-zA-Z0-9_\\-\\.]*\\@[a-zA-Z][a-zA-Z0-9_\\-\\.]*$/',
		'phone' => '/^\\s*0\\s*(?:\\d\\s*){9}$/'
	);

	/**
	 * Validation method to pass every time
	 *
	 * @param $entity
	 * @param $field
	 *
	 * @return array
	 */
	$pass = function($entity, $field) {
		return array(
			array(
				'entity' => $entity,
				'field' => $field
			)
		);
	};

	/**
	 * Validation method to check for non-empty inputs
	 *
	 * @param $entity
	 * @param $field
	 * @param $value
	 * @param $message
	 *
	 * @return array
	 */
	$required = function($entity, $field, $value, $message) {
		$result = array(
			'entity' => $entity,
			'field' => $field
		);
		if (strlen($value) === 0) {
			$result['message'] = $message;
		}
		return array( $result );
	};

	/**
	 * Validation method to check an input is within a given numerical range
	 *
	 * @param $entity
	 * @param $field
	 * @param $value
	 * @param $min
	 * @param $max
	 * @param $message
	 *
	 * @return array
	 */
	$range = function($entity, $field, $value, $min, $max, $message) {
		$result = array(
			'entity' => $entity,
			'field' => $field
		);
		if ($value < $min || $value > $max) {
			$result['message'] = $message;
		}
		return $result;
	};

	/**
	 * Validation method to check inputs against a given regex pattern
	 *
	 * @param $entity
	 * @param $field
	 * @param $value
	 * @param $pattern
	 * @param $required
	 * @param $message
	 *
	 * @return array
	 */
	$match = function($entity, $field, $value, $pattern, $required, $message) {
		$result = array(
			'entity' => $entity,
			'field' => $field
		);
		if (($required || strlen($value) > 0) && !preg_match($pattern, $value)) {
			$result['message'] = $message;
		}
		return array( $result );
	};

	/**
	 * Validation data structure
	 */
	return array(
		'default' => function($application, $value, $entity, $field) use ($pass) {
			return $pass($entity, $field);
		},
		'application' => array(
			'loan_amount' => function($application, $value) use ($messages, $range) {
				return $range('application', 'loan_amount', $value, 100, 2000, $messages['loan_amount']['out_of_range']);
			},
			'australian_resident' => function($application, $value) use ($messages) {
				if ($value || $application['loan_term_weeks'] !== 6) {
					$result = array(
						array(
							'entity' => 'application',
							'field' => 'australian_resident'
						)
					);
					if (!empty($application['loan_term_weeks'])) {
						$result[] = array(
							'entity' => 'application',
							'field' => 'loan_term_weeks'
						);
					}
					return $result;
				} else {
					return array(
						array(
							'entity' => 'application',
							'field' => 'loan_term_weeks',
							'message' => $messages['loan_term_weeks']['invalid']
						)
					);
				}
			},
			'medicare' => function($application, $value) use ($pass) {
				return $pass('application', 'medicare');
			},
			'loan_term_weeks' => function($application, $value) use ($messages) {
				if ($application['australian_resident'] || $value === '6') {
					return array(
						array(
							'entity' => 'application',
							'field' => 'australian_resident'
						),
						array(
							'entity' => 'application',
							'field' => 'loan_term_weeks'
						)
					);
				} else {
					return array(
						array(
							'entity' => 'application',
							'field' => 'loan_term_weeks',
							'message' => $messages['loan_term_weeks']['invalid']
						)
					);
				}
			}
		),
		'personal' => array(
			'first_name' => function($application, $value) use ($messages, $required) {
				return $required('personal', 'first_name', $value, $messages['first_name']['required']);
			},
			'middle_name' => function($application, $value) use ($pass) {
				return $pass('personal', 'middle_name');
			},
			'last_name' => function($application, $value) use ($messages, $required) {
				return $required('personal', 'last_name', $value, $messages['last_name']['required']);
			},
			'email' => function($application, $value) use ($messages, $regexPatterns, $match) {
				return $match('personal', 'email', $value, $regexPatterns['email'], true, $messages['email']['match']);
			},
			'phone_primary' => function($application, $value) use ($messages, $regexPatterns, $match) {
				return $match('personal', 'phone_primary', $value, $regexPatterns['phone'], true, $messages['phone_primary']['match']);
			},
			'phone_secondary' => function($application, $value) use ($messages, $regexPatterns, $match) {
				return $match('personal', 'phone_secondary', $value, $regexPatterns['phone'], false, $messages['phone_secondary']['match']);
			}
		),
		'address' => array(

		),
		'income' => array(

		)
	);
});
