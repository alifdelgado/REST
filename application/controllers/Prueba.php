<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Prueba extends REST_Controller
{
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();
	}

	public function index()
	{
		echo "Hola Mundo";
	}

	public function obtenerArreglo_get($index)
	{
		if ($index>2)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'No existe elemento con la posición '.$index);
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
		} else {
			$arreglo = [
				"manzana",
				"pera",
				"piña"
			];
			$respuesta = array('error'	=> FALSE, 'fruta'	=>	$arreglo[$index]);
			$this->response($respuesta);
		}

	}

	public function obtenerProducto_get($codigo)
	{
		$query = $this->db->query("select * from productos where codigo='".$codigo."'");
		$this->response($query->result());
	}
}
