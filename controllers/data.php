<?php
/**
 * @var Silex\Application $app
 */

/**
 * Update the current value of the specified field in the given loan application.  Validation is applied and any errors
 * are returned in the JSON response.
 *
 * {id} is the application id
 * {token} is the application token for verification
 * {field} is the name of the field to be modified, which must exist
 */
$app->post('/data/application/{id}/{token}/{field}', function (\Symfony\Component\HttpFoundation\Request $request, $id, $token, $field) use ($app) {
	// TODO Validation
	return $app->json(array(
		'result' => $app['db']->executeUpdate('update applications a set a.' . $field . ' = ? where a.id = ? and a.token = ?', array( $request->get('value'), $id, $token ))
	));
});
