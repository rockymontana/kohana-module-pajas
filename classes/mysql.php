<?php defined('SYSPATH') or die('No direct script access.');

class Mysql
{

	public static function quote_identifier($identifier)
	{
		// Strip all but special, wanted character groups
		$identifier = preg_replace('/[^\pL\pN\pP\pS\pZ\pM]/u', '', $identifier);
		return '`'.preg_replace('/[`\\\]/', '', $identifier).'`';
	}

}
