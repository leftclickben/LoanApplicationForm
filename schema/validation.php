<?php

return call_user_func(function () {
	/**
	 * Messages, collected here for ease of modification and potential reuse
	 */
	$messages = array(
		'application' => array(
			'loan_amount' => array(
				'out_of_range' => 'Loan amount must be between $100 and $2000'
			),
			'loan_term_weeks' => array(
				'incompatible_with_australian_resident' => 'Only Australian residents can apply for a 12 month loan'
			),
			'australian_resident' => array(
				'incompatible_with_loan_term_weeks' => 'Non-Australian residents can only apply for a 6 week loan'
			),
			'medicare' => array(
				'warning-no' => 'If you do not have Medicare you can continue your application but you will need to complete an additional identity check before it can be approved'
			)
		),
		'personal' => array(
			'first_name' => array(
				'required' => 'Please supply your first name'
			),
			'last_name' => array(
				'required' => 'Please supply your last name'
			),
			'date_of_birth' => array(
				'required' => 'Please supply your birth date'
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
		),
		'address' => array(
			'address_line1' => array(
				'required' => 'Please supply the street address'
			),
			'suburb' => array(
				'required' => 'Please supply the suburb'
			),
			'postcode' => array(
				'required' => 'Please supply the postcode'
			),
			'state' => array(
				'required' => 'Please supply your state'
			),
			'duration_months' => array(
				'required' => 'Please indicate how long you have lived at this address'
			),
		),
		'income' => array(
			'type' => array(
				'required' => 'Please select the type of income you receive'
			),
			'amount' => array(
				'required' => 'Please supply your income amount in whole dollars'
			),
			'period' => array(
				'required' => 'Please indicate how often you are paid'
			),
			'duration_months' => array(
				'required' => 'Please indicate how long you have received this income'
			),
			'description' => array(
				'required' => 'Please describe the source of your income'
			),
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
	 * Validation method to pass every time, with optional (warning) message
	 *
	 * @param $entity
	 * @param $field
	 * @param $messageLevel
	 * @param $message
	 *
	 * @return array
	 */
	$pass = function($entity, $field, $messageLevel=null, $message=null) {
		$result = array(
			'entity' => $entity,
			'field' => $field,
			'valid' => true
		);
		if (!is_null($messageLevel) && !is_null($message)) {
			$result['messageLevel'] = $messageLevel;
			$result['message'] = $message;
		}
		return array( $result );
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
			$result['valid'] = false;
		} else {
			$result['valid'] = true;
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
			$result['valid'] = false;
		} else {
			$result['valid'] = true;
		}
		return array( $result );
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
			$result['valid'] = false;
		} else {
			$result['valid'] = true;
		}
		return array( $result );
	};

	/**
	 * Validation data structure
	 */
	return array(
		'combinedResult' => function($result, $message) {
			return ($result = $result && isset($message['valid']) && $message['valid']);
		},
		'default' => function($application, $value, $entity, $field) use ($pass) {
			return $pass($entity, $field);
		},
		'entities' => array(
			'application' => array(
				// TODO Validate number format
				'loan_amount' => function($application, $value) use ($messages, $range) {
					return $range('application', 'loan_amount', $value, 100, 2000, $messages['application']['loan_amount']['out_of_range']);
				},
				'loan_term_weeks' => function($application, $value) use ($messages) {
					if (!is_null($application['australian_resident']) && !$application['australian_resident'] && intval($value) === 52) {
						$result = array(
							array(
								'entity' => 'application',
								'field' => 'loan_term_weeks',
								'message' => $messages['application']['loan_term_weeks']['incompatible_with_australian_resident'],
								'valid' => false
							),
							array(
								'entity' => 'application',
								'field' => 'australian_resident',
								'message' => $messages['application']['australian_resident']['incompatible_with_loan_term_weeks'],
								'valid' => false
							)
						);
					} else {
						$result = array(
							array(
								'entity' => 'application',
								'field' => 'loan_term_weeks',
								'valid' => true
							)
						);
						if (!is_null($application['australian_resident'])) {
							$result[] = array(
								'entity' => 'application',
								'field' => 'australian_resident',
								'valid' => true
							);
						}
					}
					return $result;
				},
				'australian_resident' => function($application, $value) use ($messages) {
					if (!is_null($value) && !$value && intval($application['loan_term_weeks']) === 52) {
						$result = array(
							array(
								'entity' => 'application',
								'field' => 'loan_term_weeks',
								'message' => $messages['application']['loan_term_weeks']['incompatible_with_australian_resident'],
								'valid' => false
							),
							array(
								'entity' => 'application',
								'field' => 'australian_resident',
								'message' => $messages['application']['australian_resident']['incompatible_with_loan_term_weeks'],
								'valid' => false
							)
						);
					} else {
						$result = array(
							array(
								'entity' => 'application',
								'field' => 'australian_resident',
								'valid' => true
							)
						);
						if (!is_null($application['loan_term_weeks'])) {
							$result[] = array(
								'entity' => 'application',
								'field' => 'loan_term_weeks',
								'valid' => true
							);
						}
					}
					return $result;
				},
				'medicare' => function($application, $value) use ($messages, $pass) {
					return $value ?
							$pass('application', 'medicare') :
							$pass('application', 'medicare', 'warning', $messages['application']['medicare']['warning-no']);
				}
			),
			'personal' => array(
				'first_name' => function($application, $value) use ($messages, $required) {
					return $required('personal', 'first_name', $value, $messages['personal']['first_name']['required']);
				},
				'middle_name' => function($application, $value) use ($pass) {
					return $pass('personal', 'middle_name');
				},
				'last_name' => function($application, $value) use ($messages, $required) {
					return $required('personal', 'last_name', $value, $messages['personal']['last_name']['required']);
				},
				// TODO Validate date format
				'date_of_birth' => function($application, $value) use ($messages, $required) {
					return $required('personal', 'date_of_birth', $value, $messages['personal']['date_of_birth']['required']);
				},
				'email' => function($application, $value) use ($messages, $regexPatterns, $match) {
					return $match('personal', 'email', $value, $regexPatterns['email'], true, $messages['personal']['email']['match']);
				},
				'phone_primary' => function($application, $value) use ($messages, $regexPatterns, $match) {
					return $match('personal', 'phone_primary', $value, $regexPatterns['phone'], true, $messages['personal']['phone_primary']['match']);
				},
				'phone_secondary' => function($application, $value) use ($messages, $regexPatterns, $match) {
					return $match('personal', 'phone_secondary', $value, $regexPatterns['phone'], false, $messages['personal']['phone_secondary']['match']);
				}
			),
			'address' => array(
				'address_line1' => function($application, $value) use ($messages, $required) {
					return $required('address', 'address_line1', $value, $messages['address']['address_line1']['required']);
				},
				'address_line2' => function($application, $value) use ($messages, $pass) {
					return $pass('address', 'address_line2');
				},
				'suburb' => function($application, $value) use ($messages, $required) {
					return $required('address', 'suburb', $value, $messages['address']['suburb']['required']);
				},
				'postcode' => function($application, $value) use ($messages, $required) {
					return $required('address', 'postcode', $value, $messages['address']['postcode']['required']);
				},
				'state' => function($application, $value) use ($messages, $required) {
					return $required('address', 'state', $value, $messages['address']['state']['required']);
				},
				// TODO Validate number format
				'duration_months' => function($application, $value) use ($messages, $required) {
					return $required('address', 'duration_months', $value, $messages['address']['duration_months']['required']);
				},
			),
			'income' => array(
				'type' => function($application, $value) use ($messages, $required) {
					return $required('income', 'type', $value, $messages['income']['type']['required']);
				},
				// TODO Validate number format
				'amount' => function($application, $value) use ($messages, $required) {
					return $required('income', 'amount', $value, $messages['income']['amount']['required']);
				},
				'period' => function($application, $value) use ($messages, $required) {
					return $required('income', 'period', $value, $messages['income']['period']['required']);
				},
				'duration_months' => function($application, $value) use ($messages, $required) {
					return $required('income', 'duration_months', $value, $messages['income']['duration_months']['required']);
				},
				'description' => function($application, $value) use ($messages, $required) {
					return $required('income', 'description', $value, $messages['income']['description']['required']);
				},
			)
		)
	);
});
