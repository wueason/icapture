<?php
namespace Icapture;

use Icapture\Image\Types;
use Icapture\Exceptions\InvalidUrlException;
use Icapture\Exceptions\ErrorSwooleInstanceException;

use \Resque;
use \swoole_server;

/**
* Class CaptureService
*
* @package Icapture
* @author  Eason Wu <eason991@gmail.com>
*/
class CaptureService
{

    /**
    * Resque instance
    *
    * @var \Resque
    */
    protected $queue;

    /**
    * Resque Service queue router
    *
    * @var \Resque
    */
    protected $queueRouter = 'icapture';

    /**
    * Redis Service dsn
    *
    * @var string
    */
    protected $redisDsn = '127.0.0.1:6379';

    /**
    * Swoole server config
    *
    * @var array
    */
    protected $config = [
        'daemonize' => 1,
        'worker_num' => 1,
        'worker_num' => 1,
        'task_worker_num' => 2,
        'task_tmpdir' => '/tmp/task/'
    ];

    /**
    * Service host
    *
    * @var string
    */
    protected $host = '127.0.0.1';

    /**
    * Service port
    *
    * @var number
    */
    protected $port = 3018;

    /**
    * URL to capture the screen of
    *
    * @var string
    */
    protected $url;

    /**
    * dom element top position
    * @var string
    */
    protected $top = 0;

    /**
    * dom element left position
    * @var string
    */
    protected $left = 0;

    /**
    * Width of the page to render
    *
    * @var int
    */
    protected $width = 1200;

    /**
    * Height of the page to render
    *
    * @var int
    */
    protected $height = 800;

    /**
    * Width of the page to clip
    *
    * @var int
    */
    protected $clipWidth = 1200;

    /**
    * Height of the page to clip
    *
    * @var int
    */
    protected $clipHeight = 800;

    /**
    * Image Type, default is png
    *
    * @var Type
    */
    protected $imageType = 'png';

    /**
    * User Agent String used on the page request
    *
    * @var string
    */
    protected $userAgentString = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36';

    /**
    * File name pattern how the file was written
    *
    * @var string
    */
    protected $imageFilePattern = 'images/pic_{picmd}';

    /**
    * File name pattern how the file was written
    *
    * @var string
    */
    protected $binPath = './bin/';

    /**
    * Swoole server instance
    *
    * @var \swoole_server
    */
    protected $serv;

    /**
    * CaptureService constructor.
    */
    public function __construct($options = [])
    {
        if(isset($options['host'])){
            $this->host = $options['host'];
        }
        if(isset($options['port']) && is_numeric($options['port'])){
            $this->port = $options['port'];
        }
        if(isset($options['queueRouter'])){
            $this->queueRouter = $options['queueRouter'];
        }
        if(isset($options['worker_num']) && is_numeric($options['worker_num'])){
            $this->config['worker_num'] = $options['worker_num'];
        }
        if(isset($options['task_worker_num']) && is_numeric($options['task_worker_num'])){
            $this->config['task_worker_num'] = $options['task_worker_num'];
        }
        if(isset($options['redisDsn'])){
            $this->redisDsn = $options['redisDsn'];
        }
        $this->initialize();
    }

    protected function initialize()
    {
        $this->initQueue();
        $this->initSwoole();
    }

    protected function initQueue()
    {
        $this->queue = Resque::setBackend($this->redisDsn);
    }

    protected function initSwoole()
    {
        $this->serv = new swoole_server($this->host, $this->port);
        $this->serv->set($this->config);
        $this->onReceive();
        $this->onTask();
        $this->onFinish();
        $this->onWorkerStart();
    }

    protected function onReceive()
    {
        $this->serv->on('Receive', function(swoole_server $serv, $fd, $from_id, $data) {
            $data = json_decode($data, true);
            if(isset($data['url']) && $data['url']) {
                try {
                    ksort($data);
                    $requestMD5 = md5(serialize($data));
                    $params = [];
                    $params['url'] = $data['url'];
                    $params['width'] = isset($data['width']) && is_numeric($data['width']) ? $data['width'] : $this->width;
                    $params['height'] = isset($data['height']) && is_numeric($data['height']) ? $data['height'] : $this->height;
                    $params['top'] = isset($data['top']) && is_numeric($data['top']) ? $data['top'] : $this->top;
                    $params['left'] = isset($data['left']) && is_numeric($data['left']) ? $data['left'] : $this->left;
                    $params['clipWidth'] = isset($data['clipWidth']) && is_numeric($data['clipWidth']) ? $data['clipWidth'] : $this->clipWidth;
                    $params['clipHeight'] = isset($data['clipHeight']) && is_numeric($data['clipHeight']) ? $data['clipHeight'] : $this->clipHeight;
                    $params['imageType'] = isset($data['imageType']) && $data['imageType'] ? Types::getClass($data['imageType']) : $this->imageType;
                    $params['userAgentString'] = isset($data['userAgentString']) && $data['userAgentString'] ? $data['userAgentString'] : $this->userAgentString;
                    $params['fileLocation'] = isset($data['fileLocation']) && $data['fileLocation'] ? $data['fileLocation'] : str_replace('{picmd}', $requestMD5, $this->imageLocationPattern);
                    $params['phantomjsBinPath'] = isset($data['phantomjsBinPath']) && $data['phantomjsBinPath'] ? $data['phantomjsBinPath'] : '';
                    $this->serv->task($params);
                } catch (Exception $e) {
                    // skip
                }
            }
        });
    }

    protected function onTask()
    {
        $this->serv->on('Task', function (swoole_server $serv, $task_id, $from_id, $data) {
            Resque::enqueue($this->queueRouter, '\Icapture\Jobs\CaptureJob', $data);
            echo 'Task finished. data: ' . json_encode($data) . PHP_EOL;
        });
    }

    protected function onFinish()
    {
        $this->serv->on('Finish', function (swoole_server $serv, $task_id, $data) {
            // skip
        });
    }

    protected function onWorkerStart()
    {
        $this->serv->on('workerStart', function($serv, $worker_id) {
            global $argv;
            if($worker_id >= $this->serv->setting['worker_num']) {
                swoole_set_process_name("php {$argv[0]}: task_worker");
            } else {
                swoole_set_process_name("php {$argv[0]}: worker");
            }
        });
    }

    public function run()
    {
        if(!($this->serv instanceof swoole_server)){
            throw new ErrorSwooleInstanceException();
        }
        $this->serv->start();
    }
}
