<?php defined('SYSPATH') or die('No direct script access.');

class Validation
{

	private $array;
	private $errors = array();

	public function __construct($array)
	{
		$this->array = $array;

		return $this;
	}

	public function add_error($field, $error)
	{
		$this->errors[$field][] = $error;
	}

	public function as_array()
	{
		return $this->array;
	}

	public function errors()
	{
		return $this->errors;
	}

	public function filter($filter, $field = FALSE)
	{
		if (($field) && isset($this->array[$field]) && is_callable($filter))
		{
			$this->array[$field] = call_user_func($filter, $this->array[$field]);
		}
		elseif ( ! ($field) && is_callable($filter))
		{
			$this->array = $this->sub_filter($filter, $this->array);
		}
		else return FALSE;

		return TRUE;
	}
	private function sub_filter($filter, $array)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value)) $array[$key] = $this->sub_filter($filter, $value);
			else                  $array[$key] = call_user_func($filter, $value);
		}

		return $array;
	}

	public function get($field)
	{
		if (isset($this->array[$field]))
		{
			return $this->array[$field];
		}
		return FALSE;
	}

	public function rule($rule, $field = FALSE, $second_param = NULL)
	{
		if (($field) && isset($this->array[$field]) && is_callable($rule))
		{
			if ( ! call_user_func($rule, $this->array[$field], $second_param))
			{
				$this->add_error($field, $rule);
			}
		}
		elseif ( ! ($field) && is_callable($rule))
		{
			foreach ($this->array as $key => $value)
			{
				if ( ! call_user_func($rule, $value, $second_param))
				{
					$this->add_error($key, $rule);
				}
			}
		}
		else return FALSE;

		return TRUE;
	}

	public function set($field, $value)
	{
		return $this->array[$field] = $value;
	}

	public function validate()
	{
		return (bool) ! (count($this->errors()));
	}

}
