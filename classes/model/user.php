<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_User extends Model
{

	/**
	 * The database driver
	 *
	 * @var obj
	 */
	static $driver;

	/**
	 * Saved instances of this object for later fetching
	 *
	 * @var array of objects
	 */
	static $instances = array();

	/**
	 * User data
	 *
	 * @var array
	 */
	private $user_data;

	/**
	 * User id
	 *
	 * @var integer
	 */
	private $user_id;

	/**
	 * Username
	 *
	 * @var string
	 */
	private $username;

	/**
	 * This instance name
	 *
	 * @var string (FALSE if no session is used)
	 */
	private $instance_name;

	/**
	 * Constructor
	 *
	 * @param int $user_id       - Specific user id. Pass FALSE to use username and password
	 * @param str $username      - Ignored if $user_id is passed
	 * @param str $password      - Plain text password, ignored if $user_id is passed
	 * @param str $instance_name - Instance name
	 * @param bol $session       - Defines if the logged in user id should be saved in session
	 */
	public function __construct($user_id = FALSE, $username = FALSE, $password = FALSE, $instance_name = 'default', $session = TRUE)
	{
		parent::__construct(); // Connect to the database
		Session::instance(); // Make sure sessions is turned on

		if ($session != TRUE) $this->instance_name = FALSE;
		else                  $this->instance_name = $instance_name;

		if ($user_id)
		{
			if ( ! $this->login_by_user_id($user_id)) throw new Exception('Invalid user ID');
		}
		elseif (($username) && ($password))
		{
			$this->login_by_username_and_password($username, $password);
		}
		elseif ($session)
		{
			if (isset($_SESSION['modules']['pajas'][$instance_name]))
			{
				$this->login_by_user_id($_SESSION['modules']['pajas'][$instance_name]);
			}
		}

		self::$instances[$instance_name] = $this;
	}

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_User_'.ucfirst(Kohana::$config->load('user.driver'));
		return (self::$driver = new $driver_name);
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	/**
	 * Checks if a field name is available
	 *
	 * @param str $field_name
	 * @return boolean
	 */
	public static function field_name_available($field_name)
	{
		return (bool) ! self::get_data_field_id($field_name);
	}

	/**
	 * Get data field id by field name
	 * If the field does not exists, it will be created
	 *
	 * @param str $field_name
	 * @return int
	 */
	public static function get_data_field_id($field_name)
	{
		return self::driver()->get_data_field_id($field_name);
	}

	/**
	 * Get data field name
	 *
	 * @param int $field_id
	 * @return str
	 */
	public static function get_data_field_name($field_id)
	{
		return self::driver()->get_data_field_name($field_id);
	}

	/**
	 * Get data fields
	 *
	 * @return arr - Field ID as key, field name as value
	 */
	public static function get_data_fields()
	{
		return self::driver()->get_data_fields();
	}

	/**
	 * Get logged in users username
	 *
	 * @return str
	 */
	public function get_username()
	{
		if ($this->username)
		{
			return $this->username;
		}
		return FALSE;
	}

	/**
	 * Get username by user i
	 *
	 * @return str or FALSE
	 */
	public static function get_username_by_id($user_id)
	{
		return self::driver()->get_username_by_id($user_id);
	}

	/**
	 * Get user data
	 *
	 * @param str $field - if only a single data field is wanted
	 * @return
	 *         array - example: array('firstname' => array('John'), 'lastname' => array('Smith'), 'email' => array('one@larvit.se', 'tow@larvit.se'))
	 *         or
	 *         array - example: array('one@larvit.se', 'tow@larvit.se')
	 */
	public function get_user_data($field = false)
	{
	  if (is_array($this->user_data))
	  {
	  	if ($field)
	  	{
	  		if (isset($this->user_data[$field]))
	  		{
	  			return $this->user_data[$field];
	  		}
	  	}
	  	else
	  	{
		    return $this->user_data;
	  	}
	  }
	  return FALSE;
	}

	/**
	 * Get logged in users id
	 *
	 * @return int
	 */
	public function get_user_id()
	{
		if ($this->user_id)
		{
			return $this->user_id;
		}
		return FALSE;
	}

	/**
	 * Get the users roles
	 * @return str roles
	 */
	public static function get_roles()
	{
		return self::driver()->get_roles();
	}

	/**
	 * Get the current users' roles
	 * @return str roles
	 */
	public function get_roles_uri()
	{
		return self::driver()->get_roles_uri($this->get_role());
	}

	/**
	 * Get the current users role.
	 */
	public function get_role()
	{
	 return $this->get_user_data('role');
	}

	/**
	 * Get list of users
	 *
	 * @param str $q           - Used as a search string in
	 *                           all data fields and username.
	 * @param int $start       - Limit the search to start from this row (0 means include
	 *                           all, 1 will omit the first result)
	 * @param int $limit       - Limit amount of rows to be returned. FALSE will return
	 *                           infinite number or rows.
	 * @param str $order_by    - Field to order by, string or array('field' => 'ASC/DESC')
	 * @return array           - array(
	 *                             array(
	 *                               'id' => 1,
	 *                               'username' => 'johnsmith'
	 *                             ),
	 *                             array(
	 *                               'id' => 2,
	 *                               'username' => 'adamjohansson'
	 *                             )
	 *                           )
	 */
	public static function get_users($q = FALSE, $start = FALSE, $limit = FALSE, $order_by = FALSE, $field_search = FALSE)
	{
		return self::driver()->get_users($q, $start, $limit, $order_by, $field_search);
	}

	/**
	 * Get an instance of this object
	 *
	 * @param str $instance_name - Instance name
	 * @return obj
	 */
	public static function instance($instance_name = 'default')
	{
		if (isset(self::$instances[$instance_name]))
		{
			return self::$instances[$instance_name];
		}
		else
		{
			return new User(FALSE, FALSE, FALSE, $instance_name);
		}
	}

	/**
	 * Load user data
	 * This method should be ran to load all internal variables from database
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	private function load_user_data($user_id)
	{
		if ($user_id == -1)
		{
			$this->username   = 'root';
			$this->user_id    = -1;
			$this->user_data  = array(
				'role' => array('admin')
			);
		}
		elseif (($this->username) || $this->username = self::driver()->get_username_by_id($user_id))
		{
			$this->user_id    = (int) $user_id;
			$this->user_data  = self::driver()->get_user_data($user_id);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Login by user id
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function login_by_user_id($user_id)
	{
		if (self::driver()->get_username_by_id($user_id) || $user_id == -1)
		{
			$this->username = self::driver()->get_username_by_id($user_id);

			if ($this->instance_name) $_SESSION['modules']['pajas'][$this->instance_name] = $user_id;
			return $this->load_user_data($user_id);
		}
		return FALSE;
	}

	/**
	 * Login by username and password
	 *
	 * @param str $username
	 * @param str $password - plain text
	 * @return boolean
	 */
	public function login_by_username_and_password($username, $password)
	{
		if ($user_id = self::driver()->get_user_id_by_username_and_password($username, self::password_encrypt($password, $username)))
		{
			if ($this->instance_name) $_SESSION['modules']['pajas'][$this->instance_name] = $user_id;
			return $this->load_user_data($user_id);
		}
		elseif (strtolower($username) == 'root' && $password === Kohana::$config->load('user.root_password'))
		{
			if ($this->instance_name) $_SESSION['modules']['pajas'][$this->instance_name] = -1;
			return $this->load_user_data(-1);
		}
		return FALSE;
	}

	/**
	 * Checks if this user is logged in
	 *
	 * @return boolean
	 */
	public function logged_in()
	{
		return is_int($this->user_id);
	}

	/**
	 * Log out this user
	 *
	 * @return boolean
	 */
	public function logout()
	{
		if (isset($_SESSION['modules']['pajas'][$this->instance_name]))
		{
			unset($_SESSION['modules']['pajas'][$this->instance_name]);
		}
		return TRUE;
	}

	/**
	 * Add a new field
	 *
	 * @param str $field_name
	 * @return int - New field id
	 */
	public static function new_field($field_name)
	{
		return self::driver()->new_field($field_name);
	}

	/**
	 * Create a new user
	 *
	 * @param str $username
	 * @param str $password         - plain text
	 * @param arr $user_data        - ex array('firstname' => 'John', 'lastname' => 'Smith', 'email' => array('one@larvit.se','two@larvit.se))
	 * @param str $load_to_instance - If set, this makes this method return a new instance of the object with
	 *                              the new user logged in. If TRUE is passed, instance name "default" will
	 *                              be used.
	 * @param bol $session          - If loaded into instance, also save in session
	 * @return int (user_id) or obj (a new instance of this user as logged in)
	 */
	public static function new_user($username, $password, $user_data = array(), $load_to_instance = FALSE, $session = FALSE)
	{
		Session::instance(); // Make sure sessions is turned on
		if ($load_to_instance === TRUE) $load_to_instance = 'default';

		if ( ! self::username_available($username)) return FALSE;

		$user_id = self::driver()->new_user($username, self::password_encrypt($password, $username), $user_data);

		if ($load_to_instance)
		{
			$new_user_instance = new User($user_id, FALSE, FALSE, $load_to_instance);
			if ($session)
			{
				if (!isset($_SESSION['modules']))
				{
					$_SESSION['modules'] = array('pajas' => array());
				}
				$_SESSION['modules']['pajas'][$load_to_instance] = $user_id;
			}
			return $new_user_instance;
		}
		else return $user_id;
	}

	/**
	 * Encrypt a password
	 *
	 * @param str $password - plain text password
	 * @param str $username - username of the user that gets their password encrypted
	 * @return string - encrypted
	 */
	public static function password_encrypt($password, $username)
	{
		return hash_hmac('sha256', $username.$password.Kohana::$config->load('user.password_salt'), Kohana::$config->load('user.shared_key'));
	}

	/**
	 * Remove a field
	 *
	 * @param int $field_id
	 * @return boolean
	 */
	public static function rm_field($field_id)
	{
		return self::driver()->rm_field($field_id);
	}

	/**
	 * Remove this user
	 *
	 * @return boolean
	 */
	public function rm_user()
	{
		if ($this->logged_in())
		{
			return self::driver()->rm_user($this->get_user_id());
		}
		return FALSE;
	}

	/**
	 * Set user data
	 *
	 * @param arr $user_data - Field as key, data as value (multiple values as array).
	 *                         Username and password can also be set here
	 * @return boolean
	 */
	public function set_user_data($user_data)
	{
		if ($this->logged_in())
		{
			if (isset($user_data['username']))
			{
				if ($user_data['username'] != $this->get_username() && self::username_available($user_data['username']))
				{
					self::driver()->set_username($this->get_user_id(), $user_data['username']);
					$this->username = $user_data['username'];
				}
				unset($user_data['username']);
			}

			if (isset($user_data['password']))
			{
				self::driver()->set_password($this->get_user_id(), self::password_encrypt($user_data['password'], $this->get_username()));
				unset($user_data['password']);
			}

			self::driver()->set_data($this->get_user_id(), $user_data, TRUE);

			// Clear local cache
			$this->user_data = NULL;
			$this->load_user_data($this->get_user_id());

			return TRUE;
		}
		else return FALSE;
	}

	/**
	 * Checks if a username is available
	 *
	 * @param str $username
	 * @return boolean
	 */
	public static function username_available($username)
	{
		$user_id = self::driver()->get_user_id_by_username($username);

		if ( ! empty($user_id) || strtolower($username) == 'root')
		{
			return FALSE;
		}
		else return TRUE;
	}

	/**
	 * Update field
	 *
	 * @param int $field_id
	 * @param str $field_name
	 * @return boolean
	 */
	public static function update_field($field_id, $field_name)
	{
		return self::driver()->update_field($field_id, $field_name);
	}

}
