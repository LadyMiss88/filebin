<?php
/*
 * Copyright 2014 Florian "Bluewind" Pritz <bluewind@server-speed.net>
 *
 * Licensed under AGPLv3
 * (see COPYING for full license text)
 *
 */

class Api extends MY_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('mfile');
		$this->load->model('mmultipaste');
	}

	public function route() {
		$requested_version = $this->uri->segment(2);
		$controller = $this->uri->segment(3);
		$function = $this->uri->segment(4);
		$major = intval(explode(".", $requested_version)[0]);

		if (!preg_match("/^[a-zA-Z-_]+$/", $controller)) {
			return send_json_error_reply("api/invalid-controller-value", "Invalid controller requested");
		}

		if (!preg_match("/^[a-zA-Z-_]+$/", $function)) {
			return send_json_error_reply("api/invalid-function-value", "Invalid function requested");
		}

		$namespace = "controllers\\api\\v".$major;
		$class = $namespace."\\".$controller;
		$class_info = $namespace."\\api_info";

		if (!class_exists($class_info) || version_compare($class_info::get_version(), $requested_version, "<")) {
			return send_json_error_reply("api/version-not-supported", "Requested API version is not supported");
		}

		if (!class_exists($class)) {
			return send_json_error_reply("api/unknown-controller", "Unknown controller requested");
		}

		$c= new $class;
		if (!method_exists($c, $function)) {
			return send_json_error_reply("api/unknown-function", "Unknown function requested");
		}
		return $c->$function();
	}
}
