<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Productos extends REST_Controller
{
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();
	}

	public function todos_get($pagina = 0)
	{
		$pagina = $pagina * 10;
		$query = $this->db->query("select * from productos limit {$pagina}, 10");
		$respuesta = array('error'	=>	false, 'productos'	=>	$query->result_array());
		$this->response($respuesta);
	}

	public function porTipo_get($tipo = 0, $pagina = 0)
	{
		if ($tipo == 0)
		{
			$respuesta = array('error'	=>	true, 'mensaje'	=>	'No existen productos de este tipo');
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
		} else {
			$pagina = $pagina * 10;
			$query = $this->db->query("select * from productos where linea_id={$tipo} limit {$pagina}, 10");
			$respuesta = array('error'	=>	false, 'productos'	=>	$query->result_array());
			$this->response($respuesta);
		}
	}

	public function buscar_get($termino)
	{
		$query = $this->db->query("select * from productos where producto like '%{$termino}%'");
		$respuesta = array('error'	=>	false, 'productos'	=>	$query->result_array());
		$this->response($respuesta);
	}
}
