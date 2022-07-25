<?php

declare(strict_types=1);

namespace App\Application\DAO;

<<<<<<< HEAD
use App\Application\Helper\Connection;
=======
use App\Application\Helper\Conexion;
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
use App\Application\Helper\Util;
use App\Application\Model\Usuario;

class UsuarioDAO {

    private $_conexion;

    function __construct() {
<<<<<<< HEAD
        $this->_conexion = new Connection();
=======
        $this->_conexion = new Conexion();
>>>>>>> f7d660f5f61ad7a92dcc705f5a1fbc2f8802ad4b
    }

    public function agregarUsuario(Usuario $usuario): bool {
        return $this->_conexion->insert("INSERT INTO usuario(nombre_pila, apellido1, "
            . "apellido2, correo_electronico, domicilio, telefono, clave, ruta_fotografia, "
            . "habilitado, root, idsucursal) "
            . "VALUES ('" . $usuario->getNombrePila() . "', '"
            . $usuario->getApellido1() . "', '"
            . $usuario->getApellido2() . "', '"
            . $usuario->getCorreo() . "', '"
            . $usuario->getDomicilio() . "', '"
            . $usuario->getTelefono() . "', '"
            . $usuario->getClave() . "', '"
            . $usuario->getRutaFotografia() . "', 1,"
            . $usuario->getRoot() . ","
            . $usuario->getIdSucursal() . ")");
    }


    public function getSiguienteId(): int {
        $tupla = $this->_conexion->select("SELECT AUTO_INCREMENT FROM "
            . "INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'usuario'")->fetch_array();
        return $tupla[0];
    }

    public function editarUsuario(Usuario $usuario): bool {
        return $this->_conexion->update("UPDATE usuario SET "
            . "nombre_pila = '" . $usuario->getNombrePila() . "', "
            . "apellido1 = '" . $usuario->getApellido1() . "', "
            . "apellido2 = '" . $usuario->getApellido2() . "', "
            . "correo_electronico = '" . $usuario->getCorreo() . "', "
            . "clave = '" . $usuario->getClave() . "', "
            . "telefono = '" . $usuario->getTelefono() . "', "
            . "domicilio = '" . $usuario->getDomicilio() . "', "
            . "ruta_fotografia = '" . $usuario->getRutaFotografia() . "' "
            . "WHERE idusuario = " . $usuario->getIdUsuario());
    }

    public function eliminarUsuario($idUsuario) {
        $ubicacioneDeLaFoto = "img/Usuarios/IMG_" . $idUsuario . ".jpg";
        if (file_exists($ubicacioneDeLaFoto)) {
            unlink($ubicacioneDeLaFoto);
        }
        return $this->_conexion->UPDATE("UPDATE usuario SET habilitado = 0 WHERE idusuario = " . $idUsuario);
    }    

    public function dameUsuario(int $idUsuario) {
        $tupla = $this->_conexion->select("SELECT idusuario, nombre_pila, apellido1, "
            . "apellido2, correo_electronico, telefono, domicilio, clave, ruta_fotografia, "
            ."root, idsucursal "
            . "FROM usuario WHERE idusuario = " . $idUsuario)->fetch_array();
        if (isset($tupla)) {
            return new Usuario($tupla[0], $tupla[1], $tupla[2], $tupla[3], $tupla[4],
                $tupla[5], $tupla[6], $tupla[7], $tupla[8], $tupla[9], $tupla[10]);
        } else {
            return null;
        }
    }

    public function dameCajeros(int $idSucursal, string $nombre = '') {
        $cajeros = [];
        $result = $this->_conexion->select("SELECT idusuario FROM usuario"
            . " WHERE nombre_pila LIKE '%$nombre%' AND idsucursal = " . $idSucursal
            . " AND root = 0 AND habilitado = 1 ORDER BY nombre_pila");
        while ($row = $result->fetch_array()) {
            $cajeros[] = self::dameUsuario($row['idusuario']);
        }
        $result->free();
        return $cajeros;
    }

    public function eliminarCajero(int $idCajero):  bool {
        return $this->_conexion->UPDATE("UPDATE usuario SET habilitado = 0 WHERE idusuario = " . $idCajero);
    }

    public function validarSesion(string $correoElectronico, string $clave) {
        $result = $this->_conexion->select("SELECT idusuario, nombre_pila, apellido1, "
            . "idsucursal, root FROM usuario "
            . "WHERE correo_electronico LIKE '$correoElectronico' AND correo_electronico = '$correoElectronico' "
            . "AND clave = '$clave' AND habilitado = 1");
        if ($result->num_rows == 1) {
            $row = $result->fetch_array();
            $datos = [
                'id' => $row['idusuario'],
                'nombre' => $row['nombre_pila'],
                'apellido' => $row['apellido1'],
                'sucursal' => $row['idsucursal'],
                'root' => $row['root']
            ];
        }
        return isset($datos) ? $datos : null;
    }

    public function dameCorreoAdministrador(int $idSucursal) {
        $tupla = $this->_conexion->select("SELECT correo_electronico FROM usuario "
            . "WHERE idsucursal = " . $idSucursal . " AND root = 1")->fetch_array();
        return (isset($tupla)) ? $tupla[0] : null;
    }

    public function comprobarCorreo(string $correoElectronico) {
        $tupla = $this->_conexion->select("SELECT correo_electronico "
            . "FROM usuario WHERE correo_electronico = '" . $correoElectronico . "'")->fetch_array();
        return (isset($tupla) && Util::validarEmail($tupla[0]));
    }
}
