<?php

declare(strict_types=1);

namespace App\Application\Helpers;

use App\Application\Helpers\EmailTemplate;

class Util
{
    public const DISHES_ID = 1;
    public const COMBOS_ID = 2;
    public const DRINKS_ID = 3;
    public const DESSERTS_D = 4;
    public const EXTRAS_ID = 5;

    /**
     * @param array $data
     * @param string $table
     * @return string
     */
    public static function prepareInsertQuery(array $data, string $table): string
    {
        $query = "INSERT INTO {$table}(";

        foreach ($data as $key => $value) {
            $query .= "{$key}, ";
        }

        $query = rtrim($query, ", ");
        $query .= ") VALUES(";

        foreach ($data as $key => $value) {
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

        foreach ($data as $key => $value) {
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
     * @param string $template
     * @return bool
     */
    public static function sendMail(array $data, string $template): bool
    {
        $branchName = $data["branch_name"];
        $urlWebsite = $_ENV["URL_WEBSITE"];
        $emailWebsite = $_ENV["EMAIL_WEBSITE"];
        $to = $data["email"];

        $headers = "From: $emailWebsite\r\n" .
            "Reply-To: $emailWebsite\r\n" .
            'X-Mailer: PHP/' . phpversion() . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-type: text/html; charset=utf-8\r\n";

        $message = file_get_contents(__DIR__ . "/../EmailTemplates/$template.html");
        if (!$message) {
            return false;
        }

        $message = str_replace("{branchName}", $branchName, $message);
        $message = str_replace("{urlWebsite}", $urlWebsite, $message);

        switch ($template) {
            case EmailTemplate::NOTIFICATION_TO_ADMIN:
                $message = str_replace("{branchLocation}", $data['branch_location'], $message);
                $message = str_replace("{quantity}", strval($data['quantity']), $message);
                $message = str_replace("{foodName}", $data['food_name'], $message);
                break;
            case EmailTemplate::PASSWORD_TO_NEW_USER:
                $message = str_replace("{branchLocation}", $data['branch_location'], $message);
                $message = str_replace("{userName}", $data['username'], $message);
                $message = str_replace("{password}", $data['password'], $message);
                break;
            case EmailTemplate::RESET_PASSWORD:
                $message = str_replace("{userName}", $data['username'], $message);
                $message = str_replace("{password}", $data['password'], $message);
                break;
            default:
                return false;
        }

        return mail($to, $data['subject'], $message, $headers);
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function sendNotificationToAdmin(array $data): bool
    {
        $to = $data["email"];
        $subject = "Notification from: {$data["branchName"]}";
        $headers = "From: {$_ENV["EMAIL_WEBSITE"]}\r\n" .
            "Reply-To: {$_ENV["EMAIL_WEBSITE"]}" . "\r\n" .
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
            "<a href='{$_ENV["URL_WEBSITE"]}'>pollorey.syss.tech</a>" .
            "<br>" .
            "</body>" .
            "</html>";

        return mail($to, $subject, $message, $headers);
    }

    /**
     * @param array $data
     * @param string $name
     * @param int $statusCode
     * @return string
     */
    public static function encodeData(mixed $data, string $name, int $statusCode = 200): string
    {
        $std = new \stdClass();
        $std->statusCode = $statusCode;
        $std->data = [$name => $data];
        return json_encode($std);
    }

    public static function cargarImagen(?array $foto, int $idRegistro, int $tipoRegistro = 0): string
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
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghilkmnopqrstuvwxyz0123456789";
        $lenCharacters = strlen($characters) - 1;
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
        return is_string(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    /**
     * @param array $items
     * @return bool
     */
    public static function isArrayOfFloats(array $items): bool
    {
        foreach ($items as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }
        return true;
    }
}
