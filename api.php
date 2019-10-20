<?php
	/*
		Copyright (c) 2017 Arturo Vásquez Soluciones de Sistemas 2716
		Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
		The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	*/
	/* RELEASE NOTES
	 * CHANGELOG
	*/
	// include('conf/config.conf.php');
	// include('conf/dev/xsqlart.class.php');
	// include('conf/dev/xmlmagic/xmlmagic.class.php');
	// include('conf/dev/xmlmagic/artui.class.php');
	// include('lib/imageresize/lib/ImageResize.php');
	// include('lib/dompdf/src/Autoloader.php');
	// include('lib/HtmlPhpExcel/lib/HtmlPhpExcel/HtmlPhpExcel.php');
	define("MOD_PHP_EXTENSION", ".php");
	define("MOD_PY_EXTENSION", ".py");
	define("MOD_HTML_EXTENSION", ".html");
	define("ROOT_MOD_DIR","mods/");
	define("IMG_EXTENSION", ".png");
	define("ROOT_IMG_DIR","img/");
	class api{
		///Condition 1 – Presence of a static member variable
		private static $_instance = null;
		
		///Condition 2 – Locked down the constructor
		private function __construct() { } //Prevent any oustide instantiation of this class
		
		///Condition 3 – Prevent any object or instance of that class to be cloned
		private function __clone() { } //Prevent any copy of this object
		
		///Condition 4 – Have a single globally accessible static method
		public static function getInstance(){
			if( !is_object(self::$_instance) ) //or if( is_null(self::$_instance) ) or if( self::$_instance == null )
			self::$_instance = new artui();
			return self::$_instance;
		}
		public function getDoctype(){
			echo("<!DOCTYPE html>");
		}
		public function conexion(){
			$db=xsqlart::getInstance();
			return $db->run();
		}
		public function hasRows($qrysentence){
			//Conectar a la base de datos modo ADMIN
			$dbn=$this->conexion();
			if($dbn->Execute($qrysentence)){
				$rowsrslt=intval($dbn->getRows());
				if(is_int($rowsrslt)){
					if($rowsrslt>0){
						return $rowsrslt;
					}
					else {
						return 0;
					}
				}
				else{
					return "No es dato numérico.";
				}
			}
			else{
				echo($dbn->getError());
				return "Error en la consulta.";
			}
		}
		public function getArrayQuery($qrysentence){
			//Conectar a la base de datos modo ADMIN
			$dbn=$this->conexion();
			if($dbn->Execute($qrysentence)){
				if($dbn->getRows()>0){
					$data=$dbn->getAllData();
					// devolver array de datos
					return $data;
				}
				else{
					return "No hay datos registrados.";
				}
			}
			else{
				echo($dbn->getError());
				return "Error en la consulta.";
			}
		}
		public function conexionClient(){
			//Conectar a la base de datos CLIENTE (PUBLICO)
			$dbn=$this->conexion();
			if($dbn->Execute("SELECT * FROM basededatos")){
				if($dbn->getRows()>0){
					$data=$dbn->getData();
					$dbnidconn=$dbn->getIDConn();
					return $dbnidconn;
				}
				else{
					echo("No hay datos registrados.");
				}
			}
			else{
				echo("Error en la consulta.");
				echo($dbn->getError());
			}
			return -1;
		}
		public function getAnterior(){
			$anteweb="";
			if($_SERVER['HTTP_REFERER']==''){
				$anteweb=$_SERVER['HTTP_REFERER'];
			}
			elseif($_SERVER['HTTP_REFERER']!=$_SERVER['PHP_SELF']){
				$anteweb=$_SERVER['HTTP_REFERER'];
			}
			return $anteweb;
		}
		public function setConstants(){
			//Setear constantes PHP a utilizar
			$dbn=$this->conexion();
			if($dbn->Execute("SELECT * FROM varsystem")){
				if($dbn->getRows()>0){
					while($data=$dbn->getData()){
						define($data['nombrevar'], $data['content']);
					}
				}
				else{
					echo("No hay datos registrados.");
				}
			}
			else{
				echo("Error en la consulta.");
				echo($dbn->getError());
			}
		}
		public function setMantenimiento(){
			//Validar que el website este en mantenimiento
			$dbn=$this->conexion();
			if($dbn->Execute("UPDATE modo_mantenimiento SET status=1")){
				return;
			}
			else{
				echo($dbn->getError());
				return -2;
			}
		}
		public function mantenimiento(){
			//Validar que el website este en mantenimiento
			$dbn=$this->conexion();
			if($dbn->Execute("SELECT * FROM modo_mantenimiento WHERE status=1 ORDER BY ID DESC")){
				if($dbn->getRows()>0){
					return;
				}
				else{
					return -1;
				}
			}
			else{
				echo($dbn->getError());
				return -2;
			}
		}
		public function cargarMod($mdl){
			//ELIMINA BOM y HACE EL INCLUDE
			$mod=ROOT_MOD_DIR.$mdl.MOD_PHP_EXTENSION;
			if(file_exists($mod)){
				// -------- read the file-content ----
				$str = file_get_contents($mod);
				//remove BOM
				// -------- remove the utf-8 BOM ----
				$str=str_replace("\xEF\xBB\xBF",'',$str);
				include($str);
			}
			else{
				echo("No se encontro el modulo indicado");
			}
		}
		public function cargarImagen($img){
			$imgtotal=ROOT_IMG_DIR.$img.IMG_EXTENSION;
			if(file_exists($imgtotal)){
				echo("<section><img src='".$imgtotal."'></section>");
			}
			else{
				echo("No se encontro el modulo indicado");
			}
		}
		//Optimizar imagen al vuelo
		public function optmImg($img,$width){
			$imgtotal=ROOT_IMG_DIR.$img.IMG_EXTENSION;
			if(file_exists($imgtotal)){
				return $this->optimizarImg($imgtotal,$width);
			}
			else{
				echo("No se encontra el archivo indicado");
			}
		}
		//
		public function enMantenimiento(){
			$dbn=$this->conexion();
			if($dbn->Execute("SELECT * FROM modo_mantenimiento WHERE status=1 ORDER BY ID DESC")){
				if($dbn->getRows()>0){
					$data=$dbn->getData();
					if(!empty($data['plantilla'])){
						$this->cargarMod($data['plantilla']);
					}
					else{
						$this->cargarImagen($data['imagen']);
					}
				}
				else{
					return -1;
				}
			}
			else{
				echo($dbn->getError());
				return -2;
			}
		}
		function url_get_contents($url,$useragent='cURL',$headers=false, $follow_redirects=false,$debug=false) {
			$db=xsqlart::getInstance();
			$result=$db->url_get_contents($url,$useragent,$headers, $follow_redirects,$debug);
			return $result;
		}
		public function getBannerGen($tipo,$id){
			if(is_numeric($id)){
				if(is_string($tipo)){
					$tipov=strtolower($tipo);
					switch($tipov){
						case 'banner':
							getBanner($id);
							break;
						case 'slideshow':
							getSlideshow($id);
							break;
						default:
							break;
					}
				}
			}
		}
		public function getBanner($id){
			$dbn=$this->conexion();
			$dbc=$this->conexionClient();
			if($dbc->Execute("SELECT * FROM banners WHERE tipo='BANNER' WHERE ID='$id'")){
				if($dbc->getRows()>0){
					while($data=$dbc->getData()){
						$tipov=strtolower($data['tipo']);
						if($tipov=="banner"){
							//code it below...
						}
						if($tipov=="imagen"){
							//code it below...
						}
						if($tipov=="youtube"){
							//code it below...
						}
						if($tipov=="iframe"){
							//code it below...
						}
						if($tipov=="otro"){
							//code it below...
						}
					}
				}
				else{
					return -1;
				}
			}
			else{
				echo($dbc->getError());
				return -2;
			}
		}
		public function getSlideshow($id){
			$dbn=$this->conexion();
			$dbc=$this->conexionClient();
			if($dbc->Execute("SELECT * FROM banners WHERE tipo='SLIDESHOW' ORDER BY ID DESC")){
				if($dbc->getRows()>0){
					$data=$dbc->getData();
					if(!empty($data['plantilla'])){
						$this->cargarMod($data['plantilla']);
					}
					else{
						$this->cargarImagen($data['imagen']);
					}
				}
				else{
					return -1;
				}
			}
			else{
				echo($dbc->getError());
				return -2;
			}
		}
		public function cargarModClient($mdl){
			$mod=ROOT_MOD_DIR.$mdl.MOD_PHP_EXTENSION;
			if(file_exists($mod)){
				if(!mantenimiento()){
					$this->cargarMod($mdl);
				}
				else {
					$this->enMantenimiento();
				}
			}
			else{
				echo("No se encontro el modulo indicado");
			}
		}
		function optimizarImg($img,$width){
			$image = new \Eventviva\ImageResize($img);
			$image->resizeToWidth($width);
			return $image->output($img);
		}

		public function getPDF($content){
			$dompdf = new Dompdf();
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->loadHtml($content);
			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'portrait');
			// Render the HTML as PDF
			$dompdf->render();
			// Output the generated PDF to Browser
			$dompdf->stream();
			return 0;
		}
		public function getExcel($content,$nameFile){
			$nameFileStr=base64_encode($nameFile.'.xls').'.xls';
			$htmlPhpExcel = new \Ticketpark\HtmlPhpExcel\HtmlPhpExcel($content);
			// Create and output the excel file to the browser
			$htmlPhpExcel->process()->output();
			// Alternatively create the excel and save to a file
			$htmlPhpExcel->process()->save($nameFileStr);
			// or get the PHPExcel object to do further work with it
			$phpExcelObject = $htmlPhpExcel->process()->getExcelObject();
			return 0;
		}
		public function getHeader(){
			$this->cargarModClient("header");
		}
		public function getFooter(){
			$this->cargarModClient("footer");
		}
	}
?>
