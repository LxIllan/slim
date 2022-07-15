<?php

namespace App\Application\Helper;

class Util
{
    public const ID_FER = 3;

    public const FOTO_PRODUCTO = 0;
    public const FOTO_USUARIO = 1;
    public const STR_FOOTER = 'Copyright © Pollo Rey 2022';    
    public const CATEGORIAS = ['Platillos', 'Paquete', 'Bebidas', 'Cerveza', 'Extras'];        
            
    public const ID_PLATILLOS = 1;
    public const ID_PAQUETE = 2;
    public const ID_BEBIDAS = 3;
    public const ID_CERVEZA = 4;
    public const ID_EXTRAS = 5;
    public const ID_PRODUCTOS = 6;    

    public static function cargarImagen(?array $foto, int $idRegistro, int $tipoRegistro = 0) : string
    {
        $fotosDefault = ['img/Productos/default.jpg', 'img/Usuarios/default.jpg'];
        $carpeta = ($tipoRegistro == 0) ? 'Productos' : 'Usuarios';
        $nombreFoto = 'img/' . $carpeta . '/' . 'IMG_' . $idRegistro . '.jpeg';
        if ((isset($foto)) && (($foto['type'] == 'image/jpeg') || ($foto['type'] == 'image/jpg') || ($foto['type'] == 'image/png'))) {
            $origen = $foto['tmp_name'];
            $destino = 'img/' . $carpeta . '/' . $foto['name'];
            $nombreFoto = 'img/' . $carpeta . '/' . 'IMG_' . $idRegistro . '.' . end((explode('.', $foto['name'])));
            
            if (is_uploaded_file($origen)) {
                array_map('unlink', glob('img/' . $carpeta . '/' . 'IMG_' . $idRegistro . '.*'));
            } else {
                echo 'Error: El fichero encontrado no fue procesado por la subida correctamente';
                return $fotosDefault[$tipoRegistro];
            }
            if (@move_uploaded_file($origen, $destino)) {
                if (rename($destino, $nombreFoto)) {
                    return $nombreFoto;
                } else {
                    unlink($nombreFoto);
                    return $fotosDefault[$tipoRegistro];
                }
            } else {
                return $fotosDefault[$tipoRegistro];
            }
        }
        return (file_exists($nombreFoto)) ? $nombreFoto : $fotosDefault[$tipoRegistro];
    }

    public static function formatearFecha(string $fecha, bool $hora = false) : string
    {
        return ($hora) ? date('l, M d, H:i:s', strtotime($fecha)) : date('d-F-Y', strtotime($fecha));
    }

    public static function formatearDinero(float $dinero) : string
    {
        return '$' . number_format($dinero, 2, ".", ",") . ' M.X.N';
    }

    public static function generarClave(int $numChars = 8) : string
    {
        $str = "0123456789abcdefghijklmnopqrstuvwxyz0123456789"
        . "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $lenStr = strlen($str);
        $clave = '';
        $i = 0;
        while ($i++ < $numChars) {
            $clave .= $str[rand(0, $lenStr)];
        }
        return $clave;
    }
    
    /* Limpia una cadena para que sea reconocida en un post */
    public static function str_to_post(string $str) : string
    {
        $str = str_replace(' ', '_', $str);
        $str = str_replace('.', '_', $str);
        $str = str_replace(',', '_', $str);
        return $str;
    }

    public static function validarEmail($email) : bool
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public static function isArrayOfFloats(array $arr) : bool
    {
        foreach ($arr as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }
        return true;
    }
}
