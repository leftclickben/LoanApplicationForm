<?php
/**
 * @var Silex\Application $app
 */

/**
 * Home page action.
 */
$app->get('/', function () use ($app) {
	return $app['twig']->render('index.twig');
});

/**
 * Static page action.
 */
$app->get('/about', function () use ($app) {
	return $app['twig']->render('about.twig');
});

/**
 * Initiate new loan application and redirect to the application form page.
 */
$app->get('/apply', function () use ($app) {
	// Generate a random token
	$token = md5(rand());

	// Create the main application record
	$app['db']->insert('applications', array(
		'accepted_warning' => true,
		'token' => $token
	));
	$id = $app['db']->lastInsertId();

	// Create a personal details record
	$app['db']->insert('personal_details', array());
	$personalDetailsId = $app['db']->lastInsertId();
	$app['db']->insert('application_personal_details', array(
		'application_id' => $id,
		'personal_details_id' => $personalDetailsId
	));

	// Create an address details record
	$app['db']->insert('address_details', array());
	$addressDetailsId = $app['db']->lastInsertId();
	$app['db']->insert('application_address_details', array(
		'application_id' => $id,
		'address_details_id' => $addressDetailsId
	));

	// Create an income details record
	$app['db']->insert('income_details', array());
	$incomeDetailsId = $app['db']->lastInsertId();
	$app['db']->insert('application_income_details', array(
		'application_id' => $id,
		'income_details_id' => $incomeDetailsId
	));

	// Redirect to the application form
	return $app->redirect('apply/' . $id . '/' . $token);
});

/**
 * Display a loan application form for the application with the given id.
 *
 * {id} is the application id
 * {token} is the application token for verification
 */
$app->get('/apply/{id}/{token}', function ($id, $token) use ($app) {
	if (!intval($app['db']->fetchColumn('select count(*) as valid from applications where id = ? and token = ?', array( $id, $token ), 0))) {
		throw new Exception('Token mismatch');
	}
//	$app['session']->set('application_in_progress', array( 'id' => $id, 'token' => $token )); TODO Use this for detecting application in progress and allowing continuation
	return $app['twig']->render('application-form.twig', array(
		'application' => array(
			'id' => $id,
			'token' => $token
		)
	));
});

/**
 * Final submission action for a loan application.
 */
$app->post('/apply/{id}/{token}', function ($id, $token) use ($app) {
	// TODO
});
