<?php
namespace Icapture;

use Icapture\Image\Types;

/**
* Class CaptureClient
*
* @package Icapture
* @author  Eason Wu <eason991@gmail.com>
*/
class CaptureClient
{
	private $params;

	private $imageType = 'png';

	CONST HELP_INFO = <<<EOF

***********************************************************

Class should be initialize with follow array:

[
	'url'=>'url address',
	'width'=>1200,
	'height'=>800,
	'top'=>0,
	'left'=>0,
	'clipWidth'=>1200,
	'clipHeight'=>800,
	'imageType'=>'png',
	'userAgentString'=>'Some user agent string',
	'phantomjsBinPath'=>'/path/to/phantomjs/dir/',
]

Url and imageType are necessary, and the rest are optional.

ImageType of png and jpg are supportted.

***********************************************************


EOF;

	public function __construct($params = [])
	{
		if (!($params && isset($params['url']) && $params['url'])) {
			exit(self::HELP_INFO);
		}
		if(isset($params['imageType']) && Types::getClass($params['imageType'])) {
			$this->imageType = $params['imageType'];
		}
		$this->params = $params;
	}

	public function request($serverAddress = 'tcp://127.0.0.1:3018')
	{
		$socket = stream_socket_client($serverAddress);
		fwrite($socket, json_encode($this->params));
		fclose($socket);
	}

	public function getCaptureFile()
	{
		$params = $this->params;
		ksort($params);
		return md5(serialize($params)) . '.' . $this->imageType;
	}
}

?>
