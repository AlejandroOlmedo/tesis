<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Abastecimiento extends CI_Controller {

	function __construct(){
		parent:: __construct();
		$this->load->database();
		$this->load->library('grocery_CRUD');
		$this->load->library('masterpage');
		$this->load->model('regional_model');
		$this->load->model('sistema/sistema_model');
		$this->load->model('inventario/productos_model','pro_model');
		$this->load->library('session');
	}
	// Al hacer una peticion a esta pagina, es porque se quiere acceder al menu de sistema.
	// Por eso no es necesario jalar el id de sistema.

	public function index()
	{	
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
			$data['vista_name'] = "sistema/index";
			$data['logo'] = $this->regional_model->get_parametro("logo");
			$data['titulo']="Menu de Sistema";
			$data['menu_sistema']=true;

		 	// Obtener los link del panel Izquierdo.
			$info['info_padre'] = $this->sistema_model->get_registro('sio_sistema_opcion',array('sio_estado'=>1,'sio_menu'=>1));
			$info['menu_principal'] = $this->sistema_model->get_menu('sic_sistema_catalogo',6);
		 	$data['menus'] = $this->load->view('menu/opciones_menu',$info, true);

			$this->__cargarVista($data);
		}
	}

	function salida_de_articulos()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		
		} else {
			
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
			$data['vista_name'] = "abastecimiento/salida_de_articulos";
			$data['logo'] = $this->regional_model->get_parametro("logo");
			$data['titulo']="Salida de articulos";

			// Obtenemos los valores de los Select
			$data['articulos'] = $this->regional_model->get_dropdown('ali_almacen_inv','ali_nombre','',array('ali_estado'=>1),null,'','ali_id',true);
			$data['procesos'] = $this->regional_model->get_dropdown('pro_proceso','pro_nombre','',array('pro_salida'=>1,'pro_estado'=>1),null,'','pro_id',true);
			$data['productos'] = $this->regional_model->get_dropdown('pro_producto','{pro_codigo}::{pro_nombre}','',array('pro_estado'=>1),null,'','pro_id',true);

			// Obtener los link del panel Izquierdo.
			$info['info_padre'] = $this->sistema_model->get_registro('sio_sistema_opcion',array('sio_estado'=>1,'sio_menu'=>1));
			$info['menu_principal'] = $this->sistema_model->get_menu('sic_sistema_catalogo',6);
		 	$data['menus'] = $this->load->view('menu/opciones_menu',$info, true);
		 	
			$this->__cargarVista($data);
		}	
	}

	function entrada_de_articulos()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		
		} else {

			if($_POST){
				
				// Insertarmos en sar_saldo_articulo 
				$articulos = array(
						'sar_pro_id' 	=> ($_POST['articulo']!=0)? $_POST['articulo']: NULL,
						'sar_ali_id' 	=> ($_POST['bodega']!=0)? $_POST['bodega'] : NULL,
						'sar_cantidad' 	=> ($_POST['cantidad']!=0) ? $_POST['cantidad']: NULL,
						'sar_precio' 	=> ($_POST['precio']!=0) ? $_POST['precio'] : NULL,
						'sar_fecha' 	=>  date("Y-m-d H:i:s", strtotime($_POST['fecha_registro'])),
						'sar_estado' 	=> 1,
						'sar_usu_mod' 	=> $this->tank_auth->get_user_id(),
						'sar_fecha_mod' => date("Y-m-d H:i:s")
					);

				$sar_id = $this->regional_model->insertar_registro('sar_saldo_articulo', $articulos);

				// Registramos el movimiento en moi_movimiento_inv

				$movimiento = array(
						'moi_ali_id' => ($_POST['bodega']!=0)? $_POST['bodega'] : NULL,
						'moi_prv_id' => ($_POST['proveedor']!=0)? $_POST['proveedor']: NULL,
						'moi_pro_id' => ($_POST['entrada']!=0)? $_POST['entrada']: NULL,
						'moi_fecha' => date("Y-m-d", strtotime($_POST['fecha_registro'])),
						'moi_estado' => 1,
						'moi_fecha_mod' => date("Y-m-d H:i:s"),
						'moi_usu_mod' => $this->tank_auth->get_user_id()
					);

				$moi_id = $this->regional_model->insertar_registro('moi_movimiento_inv', $movimiento);

				// Registrar el detalle, Actualmente es por registro, pero lo correcto seria
				// que registrara muchos productos a la misma vez.

				$detalle = array(
						'dee_sar_id' => $sar_id,
						'dee_moi_id' => $moi_id,
						'dee_cantidad' => $_POST['cantidad'],
						'dee_estado' => 1,
						'dee_fecha_mod' => date("Y-m-d H:i:s"),
						'dee_usu_mod' => $this->tank_auth->get_user_id()
					);

				$detalle_id = $this->regional_model->insertar_registro('dee_detalle_mov', $detalle);

				if($sar_id>0 && $moi_id>0 && $detalle_id>0)
				{
					$alerta=array('registro'=>$registro,'tipo_alerta'=> 'success','titulo_alerta'=>"Registro ingresado",'texto_alerta'=>"El registro se ha ingresado correctamente.");
				} else
				{
					$alerta=array('tipo_alerta'=> 'error','titulo_alerta'=>"Registro no ingresado",'texto_alerta'=>"El registro no pudo ser ingresado.");
				}
				
				$this->session->set_flashdata($alerta);       
				redirect('home/abastecimiento/entrada_de_articulos');				

			} else {

			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
			$data['vista_name'] = "abastecimiento/entrada_de_articulos";
			$data['logo'] = $this->regional_model->get_parametro("logo");
			$data['titulo']="Entrada de articulos";

			// Obtenemos los valores de los Select
			$data['articulos'] = $this->regional_model->get_dropdown('ali_almacen_inv','ali_nombre','',array('ali_estado'=>1),null,'','ali_id',true);
			$data['proveedores'] = $this->regional_model->get_dropdown('prv_proveedor','prv_nombre','',array('prv_estado'=>1),null,'','prv_id',true);
			$data['procesos'] = $this->regional_model->get_dropdown('pro_proceso','pro_nombre','',array('pro_entrada'=>1,'pro_estado'=>1),null,'','pro_id',true);
			$data['productos'] = $this->regional_model->get_dropdown('pro_producto','{pro_codigo}::{pro_nombre}','',array('pro_estado'=>1),null,'','pro_id',true);

			// Obtener los link del panel Izquierdo.
			$info['info_padre'] = $this->sistema_model->get_registro('sio_sistema_opcion',array('sio_estado'=>1,'sio_menu'=>1));
			$info['menu_principal'] = $this->sistema_model->get_menu('sic_sistema_catalogo',6);
		 	$data['menus'] = $this->load->view('menu/opciones_menu',$info, true);
		 	
			$this->__cargarVista($data);
			}
		}
	}

	function cargar_articulos()
	{
		$id_bodega = $this->input->post('id_bod');
		$articulos = $this->pro_model->get_articulos($id_bodega);
		$html="";
		foreach ($articulos as $key => $value) {
			$html .= '<option value="'.$value['pro_id'].'">'.$value['pro_nombre'].'::'.$value['sar_cantidad'].'</option>';
		}

		$arreglo = array(
			'articulos' => $html
			);

		echo json_encode($arreglo);
	}


	function __cargarVista($data=0)
	{	
		$vista=$data['vista_name'];
		$this->masterpage->setMasterpage('/pages/masterpage');
		$this->masterpage->addContentPage($vista,'content',$data);
		$this->masterpage->show();
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */