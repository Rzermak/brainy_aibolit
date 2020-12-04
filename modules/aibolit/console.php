<?php
/**
 * AiBolit - module for brainycp.
 * Using the aibolit scanner code https://revisium.com/ai/
 *
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author      rzermak <rzermak@yandex.ru>
 * @link        https://github.com/Rzermak/brainy_aibolit
 * @version     1.0
 */

if (PHP_SAPI != "cli") {
    echo 'This file can only be run from the console';
    die();
}

class AibolitConsole
{
    /**
     * Stdout
     * @var object
     */
    
    private $stdout = false;
    
    /**
     * Stdin
     * @var object
     */
    
    private $stdin = false;
    
    /**
     * Last message len
     * @var integer
     */
    
    private $lastStrLen = 0;
    
    /**
     * All messages len
     * @var array
     */
    
    private $allLastStrLen = array();
    
    /**
     * Logs
     * @var array
     */
    
    private $logs = array();
    
    /**
     * File scanner
     * @var string
     */
    
    private $scannerFile = '/etc/brainy/lib/aibolit/ai-bolit-hoster.php';
    
    /**
     * Console command for module
     * @var string
     */
    
    private $consoleCommand = '/etc/brainy/src/compiled/php5/bin/php /etc/brainy/modules/aibolit/console.php';
    
    /**
     * Arguments when open script in console
     * @var array
     */
    private $arguments = array();
    
    /**
     * Instance class
     * @var null|object
     */
    
    private static $instance;
    
    /**
     * Get instance
     * @return type
     */
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Init
     * @param array $argv - global variable php
     */
    
    public function init($argv = array())
    {
        $this->initConsole($argv);
        if ($this->issetArg("-h") || $this->issetArg("--help")) {
            return $this->showHelp();
        }
        
        // TODO: Failed in php7
        //if (AiBolitHelper::checkInstall() !== true) {
        //    $this->addMessage('Please install this module for use');
        //    return false;
        //}
        
        if ($this->issetArg("--scanstart")) {
            return $this->startScan();
        }
        
        $this->loadComponent();
        
        if ($this->issetArg("--scanend")) {
            return $this->endScan();
        }
        
        AibolitCron::checkQueue(true);
    }
    
    /**
     * Load brainy
     */
    
    public function loadComponent()
    {
        // Load module
        require_once('/etc/brainy/modules/aibolit/load.module.php');
        // Load system
        AiBolitHelper::loadPanelComponent();
    }
    
    /**
     * Initialize console
     * @param array $argv - global variable php
     */
    
    public function initConsole($argv = array())
    {
        $this->parseArguments($argv);
        $this->stdout = fopen('php://stdout', 'r');
        $this->stdin = fopen('php://stdin', 'r');
    }
    
    /**
     * Parse arguments console
     * @param array $argv - global variable php
     */
    
    public function parseArguments($argv)
    {
        if (count($argv) <= 1) {
            return null;
        }
        
        foreach ($argv as $key => $value) {
            if ($key == 0) {
                continue;
            }
            $value = explode('=', $value);
            $this->arguments[$value[0]] = isset($value[1]) ? $value[1] : '';
        }
    }
    
    /**
     * Check to exists argument
     * @param string $arg
     * @return boolean
     */
    
    public function issetArg($arg)
    {
        return array_key_exists($arg, $this->arguments) ? true : false;
    }
    
    /**
     * Check to exists one argument from list
     * @param array $args
     * @return boolean
     */
    
    public function issetOneArgs($args)
    {
        foreach ($args as $arg) {
            if ($this->issetArg($arg) === true) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get value of argument
     * @param string $arg
     * @return string|null
     */
    
    public function getArg($arg)
    {
        return $this->issetArg($arg) ? $this->arguments[$arg] : null;
    }
    
    /**
     * Get value of one argument from list
     * @param array $args
     * @return string|null
     */
    
    public function getOneArgs($args)
    {
        foreach ($args as $arg) {
            if ($this->issetArg($arg) === true) {
                return $this->getArg($arg);
            }
        }
        
        return false;
    }
    
    /**
     * Show message
     * @param string $text
     * @param boolean $inLog
     */
    
    public function addMessage($text, $inLog = true)
    {
        $text = $text . "\n";
        $this->lastStrLen = strlen($text) + 1;
        $this->allLastStrLen[] = $this->lastStrLen;
        if ($inLog === true) {
            $this->logs[] = $text;
        }
        @fwrite(STDOUT, $text);
    }
    
    /**
     * Remove old showed message and add new
     * @param string $text
     */
    
    public function replaceLastMessage($text)
    {
        $this->removeLastMessage();
        return $this->addMessage($text);
    }
    
    /**
     * Remove last message
     */
    
    public function removeLastMessage()
    {
        @fwrite(STDOUT, str_repeat(chr(8), $this->lastStrLen) . "\n");
        unset($this->allLastStrLen[(count($this->allLastStrLen) - 1)]);
        $this->lastStrLen = $this->allLastStrLen[(count($this->allLastStrLen) - 1)];
    }
    
    /**
     * Remove all messages
     */
    
    public function removeAllMessage()
    {
        $countMessage = count($this->allLastStrLen);
        for ($i = 0; $i < $countMessage; $i++) {
            $this->removeLastMessage();
        }
    }
    
    /**
     * Getting a response from the user
     * @param string $quest
     * @param integer $maxSeconds
     * @return string
     */
    
    public function questUser($quest, $maxSeconds = 10)
    {
        $this->addMessage(($maxSeconds !== false ? $quest . ' (' . $maxSeconds . 's)' : $quest), false);
        if ($maxSeconds !== false) {
            $answer = '';
            $read = array($this->stdin);
            $write = $except = array();
            if (stream_select($read, $write, $except, $maxSeconds)) {
                $answer = trim(fgets($this->stdin));
            }
        } else {
            $answer = trim(fgets($this->stdin));
        }
        
        return $answer;
    }
    
    /**
     * Show help
     */
    
    public function showHelp()
    {
        $command = $this->getConsoleCommand();
        $help = <<<HELP
AiBolit - module for brainycp.

Use: {$command} [OPTIONS]

You can use the following arguments:
  -c,  --check         Check queue
  -h,  --help          Show help

HELP;
        
        $this->addMessage($help);
    }
    
    /**
     * Get path to scanner file
     * @return string
     */
    
    public function getScannerFile()
    {
        return $this->scannerFile;
    }
    
    /**
     * Get command to start scanner
     * @return string
     */
    
    public function getConsoleCommand()
    {
        return $this->consoleCommand;
    }
    
    /**
     * Start scanning process
     */
    
    public function startScan()
    {
        require_once($this->getScannerFile());
    }
    
    /**
     * End the scanning process
     * @return boolean
     */
    
    public function endScan()
    {
        if ($this->issetOneArgs(array('--site', '-s')) === false) {
            return false;
        }
        
        $site = $this->getOneArgs(array('--site', '-s'));
        AibolitCron::stopScan($site);
        AibolitModel::setAutoStatus($site);
    }
    
    /**
     * When scanning process ends
     */
    
    public function completeScan($exit_code, $stat)
    {
        $site = $this->getOneArgs(array('--site', '-s'));
        $logfile = $this->getOneArgs(array('--logfile', '-l'));
        $command =  $this->getConsoleCommand()
                  . ' --scanend'
                  . ' --site=' . $site
                  . ' > ' . $logfile
                  . ' >&1 & echo $!;';
        exec($command, $output);
    }
}

// When scanning process ends
function aibolit_onComplete($exit_code, $stat)
{
    AibolitConsole::getInstance()->completeScan($exit_code, $stat);
}

AibolitConsole::getInstance()->init($argv);
