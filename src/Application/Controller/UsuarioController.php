<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DAO\UsuarioDAO;
use App\Application\Model\Usuario;
use App\Application\Helper\Util;

class AdminUsuarios {

    private $_usuarioDAO;

    function __construct() {
        $this->_usuarioDAO = new UsuarioDAO();
    }

    public function agregarCajero($nombrePila, $apellido1, $apellido2, $correo, $telefono,
                                         $domicilio, $clave, $rutaFotografia, $idSucursal) {
        $idUsuario = 1;
        $root = 0;
        return $this->_usuarioDAO->agregarUsuario(new Usuario($idUsuario, $nombrePila, $apellido1,
            $apellido2, $correo, $telefono, $domicilio, $clave, $rutaFotografia, $root,
            $idSucursal));
    }

    public function agregarGerente($nombrePila, $apellido1, $apellido2, $correo, $telefono,
                                   $domicilio, $clave, $rutaFotografia, $idSucursal) {
        $idUsuario = 1;
        $root = 1;
        $habilitado = 1;
        return $this->_usuarioDAO->agregarUsuario(new Usuario($idUsuario, $nombrePila, $apellido1,
            $apellido2, $correo, $telefono, $domicilio, $clave, $rutaFotografia, $root,
            $habilitado, $idSucursal));
    }

    public function editarUsuario($usuario) {
        return $this->_usuarioDAO->editarUsuario($usuario);
    }

    public function eliminarUsuario($idUsuario) {
        return $this->_usuarioDAO->eliminarUsuario($idUsuario);
    }

    public function eliminarCajero($idCajero) {
        return $this->_usuarioDAO->eliminarCajero($idCajero);
    }

    public function consultarUsuario($idUsuario) {
        return $this->_usuarioDAO->dameUsuario($idUsuario);
    }

    public function listarCajeros($idSucursal, $nombre = '') {
        return $this->_usuarioDAO->dameCajeros($idSucursal, $nombre);
    }

    public function validarSesion(string $correoElectronico, string $clave) {
        return $this->_usuarioDAO->validarSesion($correoElectronico, $clave);
    }

    public function comprobarCorreo(string $correoElectronico) {
        return Util::validarEmail($correoElectronico) ? $this->_usuarioDAO->comprobarCorreo($correoElectronico) : false;
    }

    public function dameCorreoAdministrador(int $idSucursal) {
        return $this->_usuarioDAO->dameCorreoAdministrador($idSucursal);
    }

    public function getSiguienteId() {
        return $this->_usuarioDAO->getSiguienteId();
    }
}