<?php
########################################################
#           CODIGO ESCRITO POR BRAYAN IRIBE            #
#PULL REQUESTS, SUGERENCIAS Y ISSUES SON BIENVENIDOS EN#
#https://github.com/BrayanIribe/CISCO_DPC3825_MACFILTER#
########################################################

require_once("http.php"); //incluir clase para hacer HTTP_POST
#DEFINIR ACCIONES
define("BLOCK",0);
define("UNLOCK",1);
#VARIABLES POR DEFECTO
$error = true;
$user = 'admin'; //usuario por defecto
$pswd = 'admin'; //pswd por defecto
$gateway = "192.168.0.1"; //IP del gateway por defecto
$devices = array("00:E0:4C:3F:C5:58","00:E0:4C:D2:11:9B"); //dispositivos por defecto por bloquear
$action = NULL; //sin accion
$verbose = false; //mostrar resultados
#----------[OBTENER PARAMETROS]----------#
$user_param = array_search('-u',$argv);
$pswd_param = array_search('-p',$argv);
$block_param = array_search('-b',$argv);
$unlock_param = array_search('-d',$argv);
$mac_filter_param = array_search('-m',$argv);
$gateway_param = array_search('-g',$argv);
$verbose_param = array_search('-v',$argv);
if ($user_param !== FALSE)
$user = $argv[$user_param + 1];

if ($pswd_param !== FALSE)
$pswd = $argv[$pswd_param + 1];

if ($block_param !== FALSE)
$action = BLOCK;

if ($unlock_param !== FALSE)
$action = UNLOCK;

if ($mac_filter_param !== FALSE)
$devices = explode(',',$argv[$mac_filter_param + 1]);

if ($verbose_param !== FALSE)
$verbose = true;

if (count($devices) == 0 && $action == BLOCK){
  echo("\nNo ha seleccionado que dispositivos bloquear.");
  $action = NULL;
}

if (count($devices) > 31)
die('Solo puede bloquear como maximo 32 dispositivos.');

if ($block_param !== FALSE && $unlock_param !== FALSE)
die('Solo puede especificar si bloqueara o desbloqueara los dispostivos.');

if ($action !== NULL)
$error = false;

if ($error){
  $str = "\n";
  $str .= 'router.php [opciones] -m [macs]';
  $str .= "\n\n";
  $str .= " Utilidad para bloquear / desbloquear por Mac en el router Cisco DPC3825\n";
  $str .= " -u [usuario]      Especifica el usuario con el cual iniciar sesion\n";
  $str .= " -p [pswd]         Especifica el password con el cual iniciar sesion\n";
  $str .= " -b                Bloquear las direcciones establecidas\n";
  $str .= " -d                Desbloquear todos los dispositivos.\n";
  $str .= " -m [mac1,mac32]   Definir direcciones delimitadas por comas.\n";
  $str .= " -g                Definir direccion IP del gateway.\n";
  $str .= " -v                Verboso.\n";
  fwrite(STDERR,$str);
}else{
  //No hay errores
  print_msg("[0] Inicializando Script");
  echo "OK\n";
  print_msg("[1] Iniciando sesion en el gateway");
  //preparar HTTP POST REQUEST
  $request = new HTTP_POST_REQUEST('http://' . $gateway . '/goform/Docsis_system');
  $request->params = array(
    "username_login" => $user,
    "password_login" => $pswd,
    "LanguageSelect" => "en",
    "Language_Submit" => 0,
    "login" => "Log+In"
  );
  $request->execute();
  $location = $request->getHeader('Location',$request->result['header']);
  $lang = $request->result['cookies']['Lang'];
  $session_id = $request->result['cookies']['SessionID'];
  $result = strpos($location,'Docsis_system.asp');
  #[!] Si el result del request no es igual a falso, quiere decir que es pswd incorrecto
  if ($result > 0)
  die("ERROR\n\n[ERROR] Es posible que las credenciales sean incorrectas.\nEl encabezado LOCATION no es igual a Quick_Setup.asp.");
  else
  echo "OK\n";
  echo "[HEADER] Location = {$location}\n";
  echo "[COOKIE] LANG = {$lang}\n";
  echo "[COOKIE] SESSIONID = {$session_id}\n";

  if ($verbose)
  echo "\n\n" . guiones("HTTP_POST_REQUEST BEGIN") . $request->result['header'] . guiones("HTTP_POST_REQUEST END") . "\n\n";
  //DESPUES de haber hecho el inicio de sesion y tener los cookies
  if ($action == BLOCK){
    print_msg("[2] Preparando request de bloqueo");
    $request = new HTTP_POST_REQUEST('http://' . $gateway . '/goform/WMACFilter');
    $request->cookies = "Lang={$lang}; SessionID={$session_id}";
    $request->params = array(
      "wl0_macfilter" => 'enable',
      "wl0_macmode" => 'deny',
      "save" => "Save+Settings",
      "h_wl0_macfilter" => 'enable',
      "h_wl0_macmode" => 'deny'
    );
    //agregar dispositivos
    $null_device = "00:00:00:00:00:00";
    for($i = 0; $i < 31; $i++){
      //[!] Los Mac Address deben de estar codificados segun el RFC 3986.
      $mac = (isset($devices[$i]) ? $devices[$i] : $null_device);
      $request->params["wl_mac{$i}"] = urlencode($mac);
    }
    echo "OK\n";
    if ($verbose){
      echo "\n\n" . guiones("REQUEST BEGIN");
      var_dump($request->params);
      echo guiones("REQUEST END") . "\n\n";
    }
    print_msg('[3] Ejecutando request de bloqueo');
    $request->execute();
    $login_result = strpos($request->result['header'],"200 OK");
    #[!] Si el result del request es igual a falso, quiere decir que es pswd incorrecto
    if ($login_result > 0)
    echo "OK\n";
    else
    die("ERROR\n\n[ERROR] Es posible que las credenciales sean incorrectas.\nEl parametro HTTP_RESPONSE_CODE no es igual a 200 OK.");

    if ($verbose)
    echo "\n\n" . guiones("HTTP_POST_REQUEST BEGIN") . $request->result['header'] . guiones("HTTP_POST_REQUEST END") . "\n\n";
  }else{
    print_msg("[2] Preparando request de bloqueo");
    $request = new HTTP_POST_REQUEST('http://' . $gateway . '/goform/WMACFilter');
    $request->cookies = "Lang={$lang}; SessionID={$session_id}";
    $request->params = array(
      "wl0_macfilter" => 'disable',
      "save" => "Save+Settings",
      "h_wl0_macfilter" => 'disable',
      "h_wl0_macmode" => 'deny'
    );
    echo "OK\n";
    if ($verbose){
      echo "\n\n" . guiones("REQUEST BEGIN");
      var_dump($request->params);
      echo guiones("REQUEST END") . "\n\n";
    }
    print_msg('[3] Ejecutando request de desbloqueo');
    $request->execute();
    $login_result = strpos($request->result['header'],"302 Redirect");
    #[!] Si el result del request es igual a falso, quiere decir que es pswd incorrecto
    if ($login_result > 0)
    echo "OK\n";
    else
    die("ERROR\n\n[ERROR] Es posible que las credenciales sean incorrectas.\nEl parametro HTTP_RESPONSE_CODE no es igual a 302 Redirect.");

    if ($verbose)
    echo "\n\n" . guiones("HTTP_POST_REQUEST BEGIN") . $request->result['header'] . guiones("HTTP_POST_REQUEST END") . "\n\n";
  }
}

function print_msg($str){
  echo str_pad($str,56,' ',STR_PAD_RIGHT);
}

function guiones($str){
  return "---------------[{$str}]---------------\n";
}
