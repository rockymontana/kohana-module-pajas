<?php defined('SYSPATH') or die('No direct script access.');

return array(
/*
// Create one of theese for each menu option you'd like.
// And put it in a config file in your module or application named "admin.php"
	'foo' => array( // This identifier must be unique
    'name'        => 'foo',
    '@category'   => 'Some Category', // Leave as empty string for main menu
    'description' => 'This is just a test-option that does not work',
		'href'        => 'category/foo', // This can be omitted, since it defaults to this path
		'position'    => 1, // Relative to the other menu options in the same category. If multiple menu options
		                    // exists with the same position number, it will be sorted in alphabetic order
  ),
*/

/* This will be activated later on
	'themes => array(
    'name'        => 'Themes',
    '@category'   => 'Apperance',
    'description' => 'Change the look and feel by selecting a different theme.',
		'href'        => 'themes',
		'position'    => 1,
  ),
*/

	// Users admin pages
	'users' => array(
    'name'        => 'Users',
    '@category'   => 'Users',
    'description' => 'User admin',
		'href'        => 'users',
		'position'    => 1,
  ),
  'fields' => array(
    'name'        => 'Fields',
    '@category'   => 'Users',
    'description' => 'User data fields',
		'href'        => 'fields',
		'position'    => 2,
  ),

	// CMS admin pages
  'content' => array(
    'name'        => 'Content',
    '@category'   => 'CMS',
    'description' => 'Handle content',
		'href'        => 'content',
		'position'    => 1,
  ),
  'pages' => array(
    'name'        => 'Pages',
    '@category'   => 'CMS',
    'description' => 'Handle pages',
		'href'        => 'pages',
		'position'    => 2,
  ),
  'types' => array(
    'name'        => 'Content types',
    '@category'   => 'CMS',
    'description' => 'Handle content types',
		'href'        => 'types',
		'position'    => 3,
  ),
  'images' => array(
    'name'        => 'Images',
    '@category'   => 'CMS',
    'description' => 'Handle images',
		'href'        => 'images',
		'position'    => 4,
  ),

);
