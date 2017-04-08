<?php

namespace Icapture\Jobs;

use \Screen\Capture;

/**
* Class CaptureJob
*
* @package Icapture\Jobs
* @author  Eason Wu <eason991@gmail.com>
*/
class CaptureJob
{
    public function perform()
    {
        if(!($this->args['url'] && $this->args['imageType'] && $this->args['fileLocation'])){
            return;
        }
        $screenCapture = new Capture();
        $screenCapture->setUrl($this->args['url']);
        if($this->args['width']){
            $screenCapture->setWidth($this->args['width']);
        }
        if($this->args['height']){
            $screenCapture->setHeight($this->args['height']);
        }
        if($this->args['top']){
            $screenCapture->setTop($this->args['top']);
        }
        if($this->args['left']){
            $screenCapture->setLeft($this->args['left']);
        }
        if($this->args['clipWidth']){
            $screenCapture->setClipWidth($this->args['clipWidth']);
        }
        if($this->args['clipHeight']){
            $screenCapture->setClipHeight($this->args['clipHeight']);
        }
        if($this->args['userAgentString']){
            $screenCapture->setUserAgentString($this->args['userAgentString']);
        }
        if($this->args['imageType']){
            $screenCapture->setImageType($this->args['imageType']);
        }
        if($this->args['phantomjsBinPath']){
            $screenCapture->binPath = $this->args['phantomjsBinPath'];
        }
        $screenCapture->save($this->args['fileLocation']);
    }
}
