<?php 
namespace CustomAjaxFilters\Majax\MajaxWP;

Class MimgTools {
	
	public static function handleRequest() {
		$url=$_SERVER['REQUEST_URI'];
		$p=strpos($url,"mimgtools/");
		
		if ($p!==false) {
			$url=substr($url,$p+strlen("mimgtools/"),-1);			
			MimgTools::prepImage($url);	
		} 
	}
	static function streamImage($fileName) {
		$type = 'image/jpeg';
		//header('Content-Type: application/force-download');
		header('Content-Type:'.$type);				
		header('Content-Length: ' . filesize($fileName));
		readfile($fileName);		
	}
	static function getInfFn($postId) {
		return "mimgnfo-$postId";
	}
	static function getImgFn($postId) {
		return "mimg2-$postId.jpg";
	}
	static function checkExist($uploadsPath,$postId) {
	 $filenameNfo = "$uploadsPath/".MimgTools::getInfFn($postId);
	 $filenameImg = "$uploadsPath/".MimgTools::getImgFn($postId);	
	 $ex=0;
	 if (file_exists($filenameNfo))	$ex=1;
	 if (file_exists($filenameImg))	$ex=2;
	 return $ex;
	}
	static function prepImage($postId="") {
		//no htaccess or mimgmain in root dir
		//$uploadsPath="./wp-content/uploads";

		//mimgmain in uploads dir
		$uploadsPath="../../../../../uploads/";		

		//mimgmain in plugin dir
		//$uploadsPath="./mimg";	

		if (isset($_REQUEST["debug"])) {
			if ($handle = opendir($uploadsPath)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						echo "$entry\n";
					}
				}
				closedir($handle);
			}
			exit;
		}		
		if (!$postId) return "";
		$filenameNfo = "$uploadsPath/".MimgTools::getInfFn($postId);
		$filenameImg = "$uploadsPath/".MimgTools::getImgFn($postId);
		$ex=MimgTools::checkExist($uploadsPath,$postId);
		if ($ex==2) {			
			//already have image			
			MimgTools::streamImage($filenameImg);
			die();
		}
		
		if ($ex==1) {			
			//echo "ex:$filenameNfo";
			$url=file_get_contents($filenameNfo);		
			$image = ImageCreateFromString(file_get_contents($url));  
			if ($image) {
				$height=true;
				$width=600;
				$height = $height === true ? (ImageSY($image) * $width / ImageSX($image)) : $height;
				
				// create image 
				$output = ImageCreateTrueColor($width, $height);
				ImageCopyResampled($output, $image, 0, 0, 0, 0, $width, $height, ImageSX($image), ImageSY($image));
				// save image
				
				ImageJPEG($output, $filenameImg, 95); 
				// return resized image	  
				unlink($filenameNfo);
				MimgTools::streamImage($filenameImg);
				die();
			}
		}
		
				
		die();		
	}

}
?>