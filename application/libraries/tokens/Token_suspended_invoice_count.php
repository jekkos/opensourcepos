<?php

class Token_suspended_invoice_count extends Token
{
	private static $token_code = 'SCO';
	private $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		$this->CI->load->model('Sale_suspended');
	}

	public static function get_token_code()
	{
		return Token_suspended_invoice_count::$token_code;
	}

	public function get_value()
	{
		return $this->Sale_suspended->get_invoice_count();
	}
}