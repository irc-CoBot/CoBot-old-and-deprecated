<?php
$conffile="<?php \n";
$modfile="<?php \n";
$dotsql="CoBOT.sql";
echo "Instalador de CoBot.\n";
echo "Seleccione una opción:\n\n";
echo "1 - Instalar (Crear tablas en la base de datos y archivos de configuración)\n";
echo "2 - Crear usuarios\nSu opción: ";
$o = trim(fgets(STDIN));
echo "\n\n";
if($o==1){
	echo "Conexión a la base de datos:\n";
	echo "Servidor [localhost]: "; $dbhost = trim(fgets(STDIN)); if(!$dbhost){$dbhost="localhost";}
	echo "Usuario: "; $dbuser = trim(fgets(STDIN));
	echo "Constraseña: "; $dbpass = trim(fgets(STDIN));
	echo "Base de datos (se creará si no existe) [CoBot]: "; $db = trim(fgets(STDIN)); if(!$db){$db="CoBot";}
	$conffile.="\$conf['db']['host'] = '{$dbhost}';\n\$conf['db']['user'] = '{$dbuser}';\n\$conf['db']['pass'] = '{$dbpass}';\n\$conf['db']['name'] = '{$db}';\n\n";
	echo "\n";
	$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass);
	if(!$mysqli){echo "No se pudo conectar al servidor mysql."; exit;}
	$mysdb=$mysqli->select_db($db);
	if(!$mysdb){
		echo "La base de datos no existe, creandola...\n";
		$mycdb=$mysqli->query("CREATE DATABASE `{$db}`");
		if(!$mycdb){echo "No se pudo crear la base de datos."; exit;}
		echo "Base de datos creada.\n\n";
	}
	echo "Creando tablas...\n";
	$ok = true;
	$sql=explode(";",file_get_contents($dotsql));
	foreach($sql as $query){$mysqli->query($query);}
	echo "Tablas creadas!";
	echo "\n\n\nConfiguración:\n";
	echo "Servidor: "; $ircserv=trim(fgets(STDIN));
	echo "Usar SSL? [N/y]"; $ssl=trim(fgets(STDIN)); if($ssl!="y"){$ssl="false";}else{$ssl="true";}
	echo "Puerto [6667]: "; $ircport=trim(fgets(STDIN)); if(!$ircport){$ircport="6667";}
	echo "Nick [CoBot]: "; $ircnick=trim(fgets(STDIN)); if(!$ircnick){$ircnick="CoBot";}
	$conffile.="\$conf['irc']['host'] = '{$ircserv}';\n\$conf['irc']['port'] = '{$ircport}';\n\$conf['irc']['nick'] = '{$ircnick}';\n\$conf['irc']['ssl'] = {$ssl};\n\n";
	echo "\n";
	echo "Canales a los que desea que el bot entre automaticamente (separados con comas): "; $autojoin=trim(fgets(STDIN));
	$aj=explode(",", $autojoin);
	$chans="";
	foreach($aj as $chan){
		$chan=trim($chan);
		$chans.="\"{$chan}\", ";
	}
	$chans=trim($chans,", ");
	echo "Prefijo de los comandos del bot [-]: ";$prefix=trim(fgets(STDIN)); if(!$prefix){$prefix="-";}
	$conffile.="\$conf['irc']['channels'] = array({$chans});\n\$conf['irc']['prefix']='{$prefix}';\n\n";
	
	echo "Desea que el bot se autentique con NickServ? [Y/n]: ";$nsmod=trim(fgets(STDIN)); if($nsmod=="n"){$nsmod=false;}else{$nsmod=true;}
	if($nsmod==true){
		$modfile.="\$ircbot->load(\"m_nickserv.php\");\n";
		echo "Usuario de NickServ [{$ircnick}]: "; $nsuser=trim(fgets(STDIN)); if(!$nsuser){$nsuser=$ircnick;}
		echo "Contraseña de NickServ: ";$nspass=trim(fgets(STDIN));
		$conffile.="\$conf['nickserv']['nsuser']='{$nsuser}';\n\$conf['nickserv']['nspass']='{$nspass}';\n\$conf['nickserv']['ghost']=true;\n\n";
	}
	$modfile.='$ircbot->load("m_joinpart.php");
	$ircbot->load("m_quit.php");
	$ircbot->load("m_say.php");
	$ircbot->load("m_authadd.php");
	$ircbot->load("m_ping.php");
	$ircbot->load("m_nick.php");
	$ircbot->load("m_modules.php");
	$ircbot->load("m_ignore.php");
	$ircbot->load("m_op.php");
	';
	$conffile.="\$conf['conn']['reconnect']=15;\n\$conf['conn']['charset']=\"ISO-8859-1\";\n\n";
	echo "\n\n -- Configuración básica terminada -- \n\n";
	echo "Desea activar m_games? (juegos) [Y/n] ";$g=trim(fgets(STDIN)); if($g=="n"){$g=false;}else{$g=true;}
	if($g==true){
		$modfile.='$ircbot->load("m_games.php");';
	}
	echo "Lea la documentación para activar otros módulos que agregan mejores funcionalidades!!";
$fp=fopen("config.php","w");
fwrite($fp,$conffile);
fclose($fp);
$fp=fopen("modules.conf.php", "w");
fwrite($fp,$modfile);
fclose($fp);

}elseif($o==2){
	if(file_exists("config.php")){
		echo "No se encuentra el archivo de configuraciones. ¿Ha instalado el bot?";
	}
	include("config.php");
	$mysqli = mysqli_connect($conf['db']['host'], $conf['db']['user'], $conf['db']['pass'], $conf['db']['name']);
	echo "Nombre del usuario: "; $uname=trim(fgets(STDIN));
	echo "Contraseña: "; $upass=trim(fgets(STDIN));
	echo "Privilegios (SE APLICARAN PRIVILEGIOS GLOBALES) del 1 al 10: "; $upriv=trim(fgets(STDIN));
	echo "Crear usuario? [Y/n] ";$g=trim(fgets(STDIN)); if($g!="y"){$g=false;}else{$g=true;}
	if($g==true){
		$mysqli->query("INSERT INTO `users` (`user` ,`pass` ,`rng`) VALUES ('{$uname}',  '".sha1($upass)."',  '{$upriv},*');");
	}
}
