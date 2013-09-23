<?php

// TODO switch this based on a server setting
$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => 'loanapps',
		'host' => 'localhost',
		'user' => 'loanapps',
		'password' => 'wee15gzQs3C69yf'
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views'
));

// https://groups.google.com/forum/#!topic/silex-php/ceKqVPlwLg0
// https://gist.github.com/umpirsky/4001744
if ($app['debug']) {
	$logger = new \Doctrine\DBAL\Logging\DebugStack();

	$app->extend('db.config', function(\Doctrine\DBAL\Configuration $configuration) use ($logger) {
		$configuration->setSQLLogger($logger);
		return $configuration;
	});

	$app->finish(function() use ($app, $logger) {
		foreach ($logger->queries as $query) {
			error_log('Query SQL: "' . $query['sql'] . '"');
			error_log('Query parameters: ' . json_encode($query['params']));
		}
	});
}
