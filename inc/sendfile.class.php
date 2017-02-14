<?php

/**
 * Sends a file to a client, with support for (multiple) range requests. 
 * It is also able to throttle the download.",  
 * 
 * Основано на: https://github.com/diversen/http-send-file
 */
 
/**
 * Баги
 * 
 * Для PHP 32bit ниже версии 5.6 - ограничение на размер файла 2 ГБ. От этого скрипта это не зависит.
 * 
 */
 
class Sendfile
{
    //public 
    /**
     * if false we set content disposition from file that will be sent
     * @var mixed $disposition    
     */
    private $disposition = false;
    
    /**
     * throttle speed in seconds
     * @var float $sec
     */
    private $sec = 0.1;
    
    /**
     * bytes per $sec
     * @var int $bytes 
     */
    private $bytes = 81920;
    
    /**
     * if contentType is false we try to guess it
     * @var mixed $contentType 
     */
    private $type = false;
	
	/**
     * locale information
     * @var string $locale
     */
	private $locale = 'ru_RU.utf-8';	
    
    /**
     * set content disposition 
     * @param type $file_name
     */
    public function contentDisposition ($file_name = false) {
        $this->disposition = $file_name;
    }
    
    /**
     * set throttle speed
     * @param float $sec
     * @param int $bytes
     */
    public function throttle ($sec = 0.1, $bytes = 81920) {
        $this->sec = $sec;
        $this->bytes = $bytes;
    }
    
    /**
     * set content mime type if false we try to guess it
     * @param string $content_type
     */
    public function contentType ($content_type = null) {
        $this->type = $content_type;
    }

    /**
     * get name from path info
     * @param type $file
     * @return type
     */
    private function name ($file) {
        $info = pathinfo($file);
        return $info['basename'];  
    }
	
    /**
     * get locale information
     */
	private function getLocale () {
		setlocale(LC_ALL, $this->locale);
		putenv('LC_ALL=' . $this->locale);
	}	
	
    /**
     * set locale information
     * @param string $locale
     */
	public function setLocale ($locale = null) {
		$this->locale = $locale;
	}
	
    /**
     * Sets-up headers and starts transfering bytes
     * 
     * @param string  $file_path
     * @param boolean $withDisposition
     * @throws Exception
     */
    public function send($file_path, $withDisposition = TRUE) {
		if ($this->locale) {
			$this->getLocale();
		}

        if ( !is_readable($file_path) || !is_file($file_path) ) {
			header('HTTP/1.1 404 Not Found');
            //throw new \Exception('File not found or inaccessible!');
        }

        $size = filesize($file_path);
        if (!$this->disposition) {
            $this->disposition = $this->name($file_path);
        }

        if (!$this->type) {
            $this->type = $this->getContentType($file_path);
        }

        //turn off output buffering to decrease cpu usage
        $this->cleanAll();

        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header('Content-Type: ' . $this->type);
        if ($withDisposition) {
            header('Content-Disposition: attachment; filename="' . $this->disposition . '"');
        }
        header('Accept-Ranges: bytes');

        // The three lines below basically make the
        // download non-cacheable 
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // multipart-download and download resuming support
        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);
            $range = intval($range);
            if (!$range_end) {
                $range_end = $size - 1;
            } else {
                $range_end = intval($range_end);
            }

            $new_length = $range_end - $range + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
            header("Content-Range: bytes $range-$range_end/$size");
        } else {
            $new_length = $size;
            header("Content-Length: " . $size);
        }

        /* output the file itself */
        $chunksize = $this->bytes; //you may want to change this
        $bytes_send = 0;

        $file = @fopen($file_path, 'rb');
        if ($file) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                fseek($file, $range);
            }

            while (!feof($file) && (!connection_aborted()) && ($bytes_send < $new_length) ) {
                $buffer = fread($file, $chunksize);
                echo($buffer); //echo($buffer); // is also possible
                flush();
                usleep($this->sec * 1000000);
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        } else {
            header('HTTP/1.1 404 Not Found');
			//throw new \Exception('Error - can not open file.');
        }
        die();
    }
    
    /**
     * method for getting mime type of a file
     * @param string $path
     * @return string $mime_type 
     */
    private function getContentType($path) {
        $result = false;
        if (is_file($path) === true) {
            if (function_exists('finfo_open') === true) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (is_resource($finfo) === true) {
                    $result = finfo_file($finfo, $path);
                }
                finfo_close($finfo);
            } else if (function_exists('mime_content_type') === true) {
                $result = preg_replace('~^(.+);.*$~', '$1', mime_content_type($path));
            } else if (function_exists('exif_imagetype') === true) {
                $result = image_type_to_mime_type(exif_imagetype($path));
            }
        }
        return $result;
    }
    
    /**
     * clean all buffers
     */
    private function cleanAll() {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }
}
