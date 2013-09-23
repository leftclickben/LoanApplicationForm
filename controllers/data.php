<?php
/**
 * @var Silex\Application $app
 */

/**
 * Update the current value of the specified field in a child entity of the given loan application.  Validation is
 * applied and any errors are returned in the JSON response.
 *
 * {id} is the application id
 * {token} is the application token for verification
 * {entity} is the name of the entity, must be in ( 'personal', 'address', 'income' )
 * {field} is the name of the field to be modified, which must exist
 */
$app->post('/data/{id}/{token}/{entity}/{field}', function (\Symfony\Component\HttpFoundation\Request $request, $id, $token, $entity, $field) use ($app) {
	// Get the value being set
	$value = $request->get('value');

	// Get the current application object
	$application = $app['db']->fetchAssoc('select * from applications a where a.id = ? and a.token = ?', array( $id, $token ));

	// Validate the token
	if (empty($application) || !intval($application['id']) || intval($id) !== intval($application['id'])) {
		throw new Exception('Token mismatch');
	}

	// Perform validation
	$validation = require_once('../schema/validation.php');
	if (isset($validation['entities'][$entity]) && isset($validation['entities'][$entity][$field])) {
		$messages = $validation['entities'][$entity][$field]($application, $value);
	} else {
		$messages = $validation['entities']['default']($application, $value, $entity, $field);
	}

	// Update the database, even if there are errors
	if ($entity === 'application') {
		$app['db']->executeUpdate('update applications a set a.' . $field . ' = ? where a.id = ? and a.token = ?', array( $value, $id, $token ));
	} else {
		$app['db']->executeUpdate('update applications a join application_' . $entity . '_details ad on (a.id = ad.application_id) join ' . $entity . '_details d on (ad.' . $entity . '_details_id = d.id) set d.' . $field . ' = ? where a.id = ? and a.token = ?', array( $value, $id, $token ));
	}

	// Return result
	return $app->json(array(
		'entity' => $entity,
		'field' => $field,
		'messages' => $messages
	));
});
