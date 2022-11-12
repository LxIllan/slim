<?php

declare(strict_types=1);

namespace App\Application\Helpers;

use Slim\Psr7\UploadedFile;
use App\Application\Helpers\EmailTemplate;

class Util
{
	public const COMBOS_CATEGORY = 1;

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
	 * @param string $table
	 * @return string
	 */
	public static function prepareInsertQuery(array $data, string $table): string
	{
		$query = "INSERT INTO $table(";

		foreach ($data as $key => $value) {
			$query .= "`$key`,";
		}

		$query = rtrim($query, ",");
		$query .= ") VALUES(";

		foreach ($data as $key => $value) {
			$query .= "'$value',";
		}

		$query = rtrim($query, ",");
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
			$query .= " `$key` = '$value',";
		}

		$query = rtrim($query, ",");
		$query .= " WHERE id = $id";

		return $query;
	}

	/**
	 * @param string $folder
	 * @param UploadedFile $uploadedFile
	 * @return string
	 */
	public static function moveUploadedFile(string $folder, UploadedFile $uploadedFile)
	{
		$directory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' .
			DIRECTORY_SEPARATOR .  'images' . DIRECTORY_SEPARATOR . $folder;

		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		}

		$basename = bin2hex(random_bytes(8));
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$filename = sprintf('%s.%s', $basename, $extension);
		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

		$photoPath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$photoPath .= "/public/images/$folder/$filename";
		return $photoPath;
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
				$message = str_replace("{quantity}", strval($data['quantity']), $message);
				$message = str_replace("{foodName}", $data['food_name'], $message);
				break;
			case EmailTemplate::PASSWORD_TO_NEW_USER:
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
	 * @param string $str
	 * @return bool
	 */
	public static function strToBool(string $str): bool
	{
		$str = strtolower($str);
		return ($str == 'true') ? true : false;
	}

	/**
	 * @param array $jwt
	 * @return bool
	 */
	public static function isAdmin(array $jwt): bool
	{
		return boolval($jwt['root']);
	}

	// /**
	//  * @param int $id
	//  * @param array $data
	//  * @param string $table
	//  * @return stdClass
	//  */
	// public static function getHistory(string $query): StdClass
	// {
	// 	$connection = new \App\Application\Helpers\Connection();
	// 	$std = new StdClass();

	// 	$result = $connection->select($query);
	// 	$std->length = $result->num_rows;
	// 	$std->items = $result->fetch_all(MYSQLI_ASSOC);
	// 	$result->free();
	// 	return $std;
	// }

	/**
	 * @param string $column
	 * @param string $table
	 * @param int $branchId
	 * @param string $from
	 * @param string $to
	 * @param string $condition
	 * @return float
	 */
	public static function getSumFromTable(string $table, string $column, int $branchId, string $from, string $to, string $condition = ''): float
	{
		if (strlen($condition) > 0) {
			$condition = "AND $condition";
		}
		$connection = new \App\Application\Helpers\Connection();
		$query = <<<SQL
			SELECT SUM($column)
			FROM $table
			WHERE DATE($table.date) BETWEEN '$from' AND '$to'
				AND branch_id = $branchId
				$condition
		SQL;
		$row = $connection->select($query)->fetch_array();
		return floatval($row[0]);
	}

	/**
	 * @param string $table
	 * @param string $column
	 * @return float
	 */
	public static function existColumn(string $table, string $column): bool
	{
		$connection = new \App\Application\Helpers\Connection();
		$query = <<<SQL
			SHOW COLUMNS FROM $table LIKE '$column'
		SQL;
		$result = $connection->select($query);
		return $result->num_rows > 0;
	}

	/**
	 * @param string $message
	 * @param mixed $data
	 * @return void
	 */
	public static function log(string $message, mixed $data = null): void
	{
		$file = __DIR__ . "/../../../logs/system.log";
		file_put_contents(
			$file,
			date("[D M d H:i:s]") . " " .
				"$message -> " . json_encode($data) . "\r\n",
			FILE_APPEND | LOCK_EX
		);
	}
}
