<?php defined('SYSPATH') or die('No direct script access.');

Route::set('404', '404')
	->defaults(array(
		'controller' => 'notfound',
		'action'     => 'index',
	));

Route::set('admin', 'admin/<controller>(/<action>(/<options>))',
	array(
		'action' => '[a-zA-Z0-9_-]+',
    'options' => '.*',
  ))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'admin',
		'action'     => 'index',
	));

Route::set('css', 'css/<path>.css',
	array(
    'path' => '[a-zA-Z0-9_/\.-]+',
  ))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'css',
	));

Route::set('img', 'img/<file>',
	array(
    'file' => '[a-zA-Z0-9_/\.-]+',
  ))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'img',
	));

Route::set('js', 'js/<path>.js',
	array(
    'path' => '[a-zA-Z0-9_/\.-]+',
  ))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'js',
	));

Route::set('xsl', 'xsl/<path>.xsl',
	array(
    'path' => '[a-zA-Z0-9_/\.-]+',
  ))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'xsl',
	));
