<?php

/**
* Класс удобного использования файла конфигурации
*
* @param $path Путь к конфиг-файлу
* @param $default Возвращаемое значение по умолчанию, если параметра в конфиге нет
*
*/

class Config {
	/** @var string */
    protected static $path = './inc/config.php';
	/** @var array */
    protected static $data;
	/** @var string */
    protected static $last_key;	
	/** @var array */
    protected static $last_data;
	/** @var */
    protected static $default = null;
	
	/**
	* Инициализация конфиг-файла
	*
	* @return array Массив параметров
	*/
	private static function init() {
		if ( !isset(self::$data) ) {
			if ( file_exists(self::$path) ) {
				self::$data = require_once self::$path;
			} else {
				exit('File '.self::$path.' not exists'.PHP_EOL);
			}
		}		
		return self::$data;
	}
	
	/**
	* Задать путь к конфиг-файлу
	*
	* @param string $path Путь к конфиг-файлу
	*
	* @return boolean TRUE Если удалось установить новый путь к конфиг-файлу
	*/
	public static function setPath($path) {
		$res = false;
		if ( isset($path) && file_exists($path) ) {
			self::$path = $path;
			$res = true;
		}
		return $res;
	}

	/**
	* Получить значение последнего запрошенного параметра
	*
	* @param string $key Параметр, значение которого необходимо получить
	*
	* @return boolean FALSE Если не удалось получить значение параметра
	*/	
	private static function getLastData($key) {
		return isset(self::$last_key) && self::$last_key == $key ? self::$last_data : false;
	}

	/**
	* Получить значение параметра
	*
	* @param string $key Параметр, значение которого необходимо получить
	* @param $default Возвращаемое значение, если такой параметр отсутствует
	*
	* @return Значение конфига
	*/
    public static function get($key, $default = null) {
        self::$default = $default;
		$last_data = self::getLastData($key);

		if ( $last_data !== false ) {
			$data = $last_data;
		} else {
			$data = self::init();
			$segments = explode('.', $key);
			foreach ($segments as $segment) {
				if (isset($data[$segment])) {
					$data = $data[$segment];
				} else {
					$data = self::$default;
					break;
				}
			}
			self::$last_key = $key;
			self::$last_data = $data;
		}
        return $data;
    }
	
	/**
	* Существует ли такой параметр
	*
	* @param string $key Параметр, значение которого необходимо проверить
	*
	* @return boolean TRUE Если такой параметр существует
	*/
    public static function exists($key) {
        return self::get($key) !== self::$default;
    }
}