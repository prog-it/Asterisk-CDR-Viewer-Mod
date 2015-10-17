<?php

/**
* Class sendFile
* Send local file to download
* Support "206 Partial Content"
*/

namespace SendFile;

class sendFile {
	# Локаль
	public $Locale = 'ru_RU.utf-8';
	# Скорость в Кб/сек
	public $Speed = 2048;
	# Content Type
	public $ContentType = null;	
	# Content Type автоопределение
	public $ContentTypeAuto = true;
	# Content Disposition
	public $ContentInline = false;
	# Имя файла для сохранения
	public $FileName = null;
	# Путь к файлу
	public $Path = null;
	
	/**
	* Parse HTTP Range header
	* http://tools.ietf.org/html/rfc2616#section-14.35
	* return array of Range on success
	*        false on syntactically invalid byte-range-spec
	*        empty array on unsatisfiable bytes-range-set
	* @param int $entity_body_length
	* @param string range_header
	* @return array|bool
	*/
	
	private function parseRangeRequest($entity_body_length, $range_header) {
		$range_list = array();

		if ($entity_body_length == 0) {
			return $range_list; // mark unsatisfiable
		}

		// The only range unit defined by HTTP/1.1 is "bytes". HTTP/1.1
		// implementations MAY ignore ranges specified using other units.
		// Range unit "bytes" is case-insensitive
		if (preg_match('#bytes=([^;]+)#i', $range_header, $match)) {
			$range_set = $match[1];
		} else {
			return false;
		}

		// Wherever this construct is used, null elements are allowed, but do
		// not contribute to the count of elements present. That is,
		// "(element), , (element) " is permitted, but counts as only two elements.
		$range_spec_list = preg_split('#,#', $range_set, null, PREG_SPLIT_NO_EMPTY);
		foreach ($range_spec_list as $i => $range_spec) {
			$range_spec = trim($range_spec);

			if (preg_match('#^(\d+)\-$#', $range_spec, $match)) {
				$first_byte_pos = $match[1];

				if ($first_byte_pos > $entity_body_length) {
					continue;
				}

				$first_pos = $first_byte_pos;
				$last_pos = $entity_body_length - 1;
			} else if (preg_match('#^(\d+)\-(\d+)$#', $range_spec, $match)) {
				$first_byte_pos = $match[1];
				$last_byte_pos = $match[2];

				// If the last-byte-pos value is present, it MUST be greater than or
				// equal to the first-byte-pos in that byte-range-spec
				if ($last_byte_pos < $first_byte_pos) {
					return false;
				}
				$first_pos = $first_byte_pos;
				$last_pos = min($entity_body_length - 1, $last_byte_pos);
			} else if (preg_match('#^\-(\d+)$#', $range_spec, $match)) {
				$suffix_length = $match[1];
				if ($suffix_length == 0) {
					continue;
				}
				$first_pos = $entity_body_length - min($entity_body_length, $suffix_length);
				$last_pos = $entity_body_length - 1;
			} else {
				return false;
			}
			$range_list[$i]['firstPos'] = $first_pos;
			$range_list[$i]['lastPos'] = $last_pos;
			$range_list[$i]['chunkSize'] = $last_pos-$first_pos;
		}
		return $range_list;
	}
	
	# Получить имя файла
	private function getFileName($path) {
		$pi = pathinfo($path);
		return $pi['basename'];
	}
	
	# Задать локаль
	private function setLocale() {
		setlocale(LC_ALL, $this->Locale);
		putenv('LC_ALL=' . $this->Locale);		
	}
	
	# Получить mime
	private function getMime($path) {
		$res = 'application/octet-stream';
		# Включено автоопределение
		if ($this->ContentTypeAuto === true && class_exists('\finfo')) {
			$finfo = new \finfo(FILEINFO_MIME_TYPE);
			$res = $finfo->file($path);
		}
		return $res;
	}

	# Отправка файла
	public function send() {
		$this->setLocale();
		
		if (isset($this->Path) && file_exists($this->Path) && is_file($this->Path)) {
			$this->FileName = ($this->FileName) ? $this->FileName : $this->getFileName($this->Path);
			$contentDisp = ($this->ContentInline === true) ? 'inline' : 'attachment';
			$this->ContentType = ($this->ContentType) ? $this->ContentType : $this->getMime($this->Path);
			
			$this->Speed = $this->Speed * 1024;
			$fileSize = filesize($this->Path);
			# Чистка буфера
			ob_end_clean();
			
			# Нет HTTP_RANGE
			if (@getenv('HTTP_RANGE') == '') {
				$f = fopen($this->Path, 'rb');
				header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
				header('Pragma: no-cache');
				header('HTTP/1.1 200 OK');
				header('Connection: close');
				header('Content-Type: ' . $this->ContentType);
				header('Accept-Ranges: bytes');
				header('Content-Disposition: '.$contentDisp.'; filename="' . $this->FileName . '"');
				header('Content-Length: ' . $fileSize); 

				while (!feof($f)) {
					set_time_limit(0);
					if (connection_aborted()) {
						break;
					}
					echo fread($f, $this->Speed);
					ob_flush();
					flush();
					sleep(1);
				}
				fclose($f);			
			} else {
				$range = $this->parseRangeRequest($fileSize, strip_tags(getenv('HTTP_RANGE')));
				if ($range && $range !== false) {
					$f = fopen($this->Path, 'rb');
					# Установить позицию чтения в файле
					fseek($f, $range[0]['firstPos']);
					
					header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
					header('Pragma: no-cache');					
					header('HTTP/1.1 206 Partial Content');
					header('Connection: close');
					header('Content-Type: ' . $this->ContentType);
					header('Accept-Ranges: bytes');
					header('Content-Disposition: '.$contentDisp.'; filename="' . $this->FileName . '"');
					header('Content-Range: bytes ' . $range[0]['firstPos'] . '-'. $range[0]['lastPos'] . '/'. $fileSize);
					header('Content-Length: ' . $range[0]['chunkSize']); 

					while (!feof($f)) {
						set_time_limit(0);
						if (connection_aborted()) {
							break;
						}
						echo fread($f, $this->Speed);
						ob_flush();
						flush();
						sleep(1);
					}
					fclose($f);					
				}
			}
		} else {
			header('HTTP/1.1 404 Not Found');
		}
	}	
	
}


