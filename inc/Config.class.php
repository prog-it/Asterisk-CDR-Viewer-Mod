<?php

/**
* Статичный класс использования файла конфигурации
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
	/** @var array */
    protected static $cache = array();
	/** @var */
    protected static $default = null;
	
	/**
	* Инициализация конфиг-файла
	*
	* @return array Массив параметров
	*/
	private static function init() {
		if ( !isset(self::$data) ) {
			if ( is_file(self::$path) ) {
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
		if ( isset($path) && is_file($path) ) {
			self::$path = $path;
			return true;
		}
		return false;
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
		if (self::exists($key)) {
			return self::$cache[$key];
		}
    }
	
	/**
	* Установить значение параметра
	*
	* @param string $key Параметр, значение которого необходимо установить
	* @param $value Значение, которое нужно установить для параметра
	*
	* @return void
	*/	
    public static function set($key, $value) {
        $segs = explode('.', $key);
        $data = &self::$data;
        $cacheKey = '';
        while ($part = array_shift($segs)) {
            if ($cacheKey != '') {
                $cacheKey .= '.';
            }
            $cacheKey .= $part;
            if (!isset($data[$part]) && count($segs)) {
                $data[$part] = array();
            }
            $data = &$data[$part];
            // Удалить старый кэш
            if (isset(self::$cache[$cacheKey])) {
                unset(self::$cache[$cacheKey]);
            }
            // Удалить старый кэш в массиве
            if (count($segs) == 0) {
                foreach (self::$cache as $cacheLocalKey => $cacheValue) {
                    if (substr($cacheLocalKey, 0, strlen($cacheKey)) === $cacheKey) {
                        unset(self::$cache[$cacheLocalKey]);
                    }
                }
            }
        }
        self::$cache[$key] = $data = $value;
    }	
	
	/**
	* Существует ли такой параметр
	*
	* @param string $key Параметр, значение которого необходимо проверить
	* @param $default Возвращаемое значение, если такой параметр отсутствует
	*
	* @return boolean TRUE Если такой параметр существует
	*/
    public static function exists($key, $default = null) {
		self::$default = $default;
        if (isset(self::$cache[$key])) {
            return true;
        }
        $segments = explode('.', $key);
        $data = self::init();
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $data)) {
                $data = $data[$segment];
                continue;
            } else {
                return self::$default;
            }
        }
        self::$cache[$key] = $data;
        return true;		
    }
	
	/**
	* Получить все параметры
	*
	* @return array Все параметры
	*/	
    public static function all() {
        return self::init();
    }
	
}