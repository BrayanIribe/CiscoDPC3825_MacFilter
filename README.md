# CISCO_DPC3825_MACFILTER
Una aplicación simple desarrollada en PHP para controlar el filtrado mac del router Cisco DPC3825 que proporciona comúnmente CableVision en México.

Para hacer funcionar la aplicación, se puede clonar el GIT en la raíz del servidor local y posteriormente ejecutar el script router.php en la consola como a continuación:

$ git clone https://github.com/BrayanIribe/CISCO_DPC3825_MACFILTER

$ php -e router.php

Si queremos siempre bloquear o desbloquear por defecto ciertos dispositivos y no tener que escribir el usuario o password, podemos abrir el archivo router.php y cambiar los parámetros.

router.php [opciones] -m [macs]

 Utilidad para bloquear / desbloquear por Mac en el router Cisco DPC3825

 -u [usuario]      Especifica el usuario con el cual iniciar sesion

 -p [pswd]         Especifica el password con el cual iniciar sesion

 -b                Bloquear las direcciones establecidas

 -d                Desbloquear todos los dispositivos.

 -m [mac1,mac32]   Definir direcciones delimitadas por comas.

 -g                Definir direccion IP del gateway.

 -v                Verboso.

No es necesario codificar los Mac Address según el RFC 3986. La aplicación lo hace solo. Para específicar dispositivos según su MAC hay que delimitarlos con comas por ejemplo:

 router.php -b -m 00:00:00:00:00:00,00:00:00:00:00:00
