<?php

namespace App\Application\Helper;

class Util
{
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

    /**
     * @param array $data
     * @param string $table
     * @return string
     */
    public static function prepareInsertQuery(array $data, string $table): string
    {
        $query = "INSERT INTO {$table}(";

        foreach ($data as $key => $value)
        {
            $query .= "{$key}, ";
        }

        $query = rtrim($query, ", ");
        $query .= ") VALUES(";

        foreach ($data as $key => $value)
        {
            if (is_string($value)) {
                $query .= "'{$value}', ";
            } else {
                $query .= "{$value}, ";
            }

        }

        $query = rtrim($query, ", ");
        $query .= ")";
        return $query;
    }

    /**
     * @param int $id
     * @param array $data
     * @param string $table
     * @return string
     */
    public static function prepareUpdateQuery(int $id, array $data, string $table): string
    {
        $query = "UPDATE $table SET";

        foreach ($data as $key => $value)
        {
            if (is_string($value)) {
                $query .= " $key = '$value',";
            } else {
                $query .= " $key = $value,";
            }
        }

        $query = rtrim($query, ",");
        $query .= " WHERE id = {$id}";

        return $query;
    }

    /**
     * @param int $id
     * @param string $table
     * @return string
     */
    public static function prepareDeleteQuery(int $id, string $table): string
    {
        return "DELETE FROM $table WHERE id = $id";
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function sendNotificationToAdmin(array $data): bool
    {
        $to = $data["email"];
        $subject = "Notification from: {$data["branchName"]}";
        $headers = "From: pollorey@syss.tech\r\n" .
            'Reply-To: pollorey@syss.tech' . "\r\n" .
            'X-Mailer: PHP/' . phpversion() . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type: text/html; charset=utf-8' . "\r\n";

        $message =  "<html>" .
            "<head>" .
            "<title>{$data["branchName"]}</title>" .
            "</head>" .
            "<body>" .
            "<h3>{$data["branchLocation"]}</h3>" .
            "Quedan <b>{$data["quantity"]}</b> unidades de <b>{$data["foodName"]}</b>" .
            "<br>" .
            "<br>" .
            "<a href='http://test.syss.tech/pollorey/'>pollorey.syss.tech</a>" .
            "<br>" .
            "</body>" .
            "</html>";

        return mail($to, $subject, $message, $headers);
    }

    /**
     * @param array $data
     * @param int $statusCode
     * @return string
     */
    public static function orderReturnData(mixed $data, int $statusCode = 200): string
    {
        return json_encode(["statusCode" => $statusCode, "data" => $data]);
    }

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

    /**
     * @param int $lenPassword
     * @return string
     */
    public static function generatePassword(int $lenPassword = 8): string
    {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $lenCharacters = strlen($characters);
        $password = '';
        $i = 0;
        while ($i++ < $lenPassword) {
            $password .= $characters[rand(0, $lenCharacters)];
        }
        return $password;
    }

    /**
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param array $items
     * @return bool
     */
    public static function isArrayOfFloats(array $items) : bool
    {
        foreach ($items as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }
        return true;
    }
}
