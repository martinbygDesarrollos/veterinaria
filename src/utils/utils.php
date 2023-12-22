<?php
/**
 *
 */
class Utils
{

	public function clearCalendarPdfDir(){


		$path = dirname(dirname(__DIR__)) . "/public/imprimibles/";
		$folders = glob($path . '/*');
		foreach($folders AS $file){
			if(is_dir($file)){
				rmdir($file);
			}else unlink($file);
		}
	}
}

?>