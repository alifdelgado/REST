<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Pedidos extends REST_Controller
{
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");
		
		parent::__construct();
		$this->load->database();
	}

	public function realizarOrden_post($token = "0", $idUsuario = "0")
	{
		$data = $this->post();

		if($token == "0" || $idUsuario == "0")
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o usuario invalido.');
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		if(!isset($data['items']) || strlen($data['items'])==0)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Faltan los productos del usuario.');
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		$condiciones = array('id'	=>	$idUsuario, 'token'	=>	$token);
		$this->db->where($condiciones);
		$query = $this->db->get('login');
		$existe = $query->row();

		if(!$existe)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o Usuario incorrectos.');
			$this->response($respuesta);
			return;
		}

		$this->db->reset_query();
		$insertar = array('usuario_id'	=>	$idUsuario);

		$this->db->insert('ordenes', $insertar);
		$orden_id = $this->db->insert_id();

		$this->db->reset_query();
		$items = explode(',', $data['items']);

		foreach($items as &$item)
		{
			$dataInsertar = array('producto_id'	=>	trim($item), 'orden_id'	=>	$orden_id);
			$this->db->insert('ordenes_detalle', $dataInsertar);
		}

		$respuesta = array('error'	=>	FALSE, 'orden_id'	=>	$orden_id);
		$this->response($respuesta);
	}

	public function obtenerPedidos_get($token = "0", $idUsuario = "0")
	{
		if($token == "0" || $idUsuario == "0")
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o usuario invalido.');
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		$condiciones = array('id'	=>	$idUsuario, 'token'	=>	$token);
		$this->db->where($condiciones);
		$query = $this->db->get('login');
		$existe = $query->row();

		if(!$existe)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o Usuario incorrectos.');
			$this->response($respuesta);
			return;
		}

		$query = $this->db->query("select * from ordenes where usuario_id={$idUsuario}");
		$ordenes = array();
		foreach($query->result()  as $row)
		{
			$queryDetalle = $this->db->query("select a.orden_id, b.* from ordenes_detalle a inner join productos b on a.producto_id = b.codigo where orden_id={$row->id}");
			$orden = array('id'	=>	$row->id,
							'creado_en'	=>	$row->creado_en,
							'detalle'	=>	$queryDetalle->result());
			array_push($ordenes, $orden);
		}

		$respuesta = array('error'	=>	FALSE, 'ordenes'	=>	$ordenes);
		$this->response($respuesta);
	}

	public function borrarPedido_delete($token = "0", $idUsuario = "0", $idOrden = "0")
	{
		if($token == "0" || $idUsuario == "0" || $idOrden == "0")
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o usuario invalido.');
			$this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		$condiciones = array('id'	=>	$idUsuario, 'token'	=>	$token);
		$this->db->where($condiciones);
		$query = $this->db->get('login');
		$existe = $query->row();

		if(!$existe)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Token y/o Usuario incorrectos.');
			$this->response($respuesta);
			return;
		}

		$this->db->reset_query();
		$condiciones = array('id'	=>	$idOrden, 'usuario_id'	=>	$idUsuario);
		$this->db->where($condiciones);
		$query = $this->db->get('ordenes');
		$existe = $query->row();

		if(!$existe)
		{
			$respuesta = array('error'	=>	TRUE, 'mensaje'	=>	'Esa orden no puede ser borrada.');
			$this->response($respuesta);
			return;
		}
		
		$condiciones = array('orden_id', $idOrden);
		$this->db->delete('ordenes_detalle', $condiciones);

		$condiciones = array('id'	=>	$idOrden);
		$this->db->delete('ordenes', $condiciones);

		$respuesta = array('error'	=>	FALSE, 'mensaje'	=>	'Orden eliminada');
		$this->response($respuesta);
	}
}
