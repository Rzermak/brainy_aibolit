<?php
/**
 * AiBolit - module for brainycp.
 * Using the aibolit scanner code https://revisium.com/ai/
 *
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author      rzermak <rzermak@yandex.ru>
 * @link        https://github.com/Rzermak/brainy_aibolit
 * @version     1.1
 */

class AiBolitHelper
{
    /**
     * Module version
     */
    
    const MODULE_VERSION = '1.1';
    
    /**
     * Module parametres file path
     * @var string
     */
    private static $configFile = '/etc/brainy/data/aibolit/config.cnf';
    
    /**
     * Mail configuration file
     * @var string
     */
    private static $mailConfigFile = '/etc/brainy/data/properties/mailconf';
    
    /**
     * Module log file path
     * @var string
     */
    private static $logFile = '/etc/brainy/data/aibolit/log';
    
    /**
     * Module history file path
     * @var string
     */
    private static $historyModuleFile = '/etc/brainy/data/aibolit/history';
        
    /**
     * Module parametres
     * @var boolean|array
     */
    private static $config = false;
    
    /**
     * Email parametres
     * @var boolean|array
     */
    private static $mailConfig = false;
    
    /**
     * Object webserver
     * @var boolean|object
     */
    private static $webserver = false;
    
    public static function getModuleVersion()
    {
        return self::MODULE_VERSION;
    }
    
    /**
     * Load panel component
     * @global $server - global variable of panel
     */
    
    public static function loadPanelComponent()
    {
        global $server;
        
        // Load system
        require_once('/etc/brainy/conf/globals.php');
        require_once('/etc/brainy/classes/server.php');
        require_once('/etc/brainy/classes/hostacc.php');
        require_once('/etc/brainy/lib/punycode/idna_convert.php');
        require_once('/etc/brainy/classes/webserver.php');
        require_once('/etc/brainy/classes/mail.php');
        $server = new Server();
    }
    
    /**
     * Add item to log file
     * @param string $message
     */
    
    public static function toLog($message)
    {
        $fp = fopen(self::$logFile, 'a');
        fwrite($fp, date('Y-m-d H:i:s', time()) . ' - ' . $message);
        fwrite($fp, "\n");
        fclose($fp);
    }
    
    /**
     * Get logs data
     */
    
    public static function getLogs()
    {
        if (!file_exists(self::$logFile)) {
            return '';
        }
        
        return file_get_contents(self::$logFile);
    }
    
    /**
     * Get module history
     */
    
    public static function getModuleHistory()
    {
        if (!file_exists(self::$historyModuleFile)) {
            return '';
        }
        
        return file_get_contents(self::$historyModuleFile);
    }
    
    /**
     * Get module parametres
     * @return object
     */
    
    public static function getConfig($param = false)
    {
        if (self::$config === false) {
            self::$config = self::getServerData()->config_read(self::$configFile);
        }
        
        return $param !== false ? self::$config[$param] : self::$config;
    }
    
    /**
     * Save module parametres
     * @param array $data
     */
    
    public static function setConfig($data)
    {
        self::getServerData()->config_save(self::$configFile, $data);
        self::$config = false;
    }
    
    /**
     * Get module parametres
     * @return object
     */
    
    public static function getMailConfig($param = false)
    {
        if (self::$mailConfig === false) {
            self::$mailConfig = self::getServerData()->config_read(self::$mailConfigFile);
        }
        
        return $param !== false ? self::$mailConfig[$param] : self::$mailConfig;
    }
    
    /**
     * Get object webserver
     * @return object
     */
    
    public static function getServer()
    {
        if (self::$webserver === false) {
            self::$webserver = new Webserver();
        }
        
        return self::$webserver;
    }
    
    /**
     * Get object server
     * @return object
     */
    
    public static function getServerData()
    {
        global $server;
        return $server;
    }
    
    /**
     * Get access right to operation on the file
     * @param string $file
     * @param string $site
     * @return boolean
     */
    
    public static function accessToFile($file, $site)
    {
        if (!$file || !$site) {
            return false;
        }
        
        $sites = AibolitModel::getAllSites();
        if (!isset($sites[$site])) {
            return false;
        }
        
        $finded_file = false;
        $report = AibolitScanner::getReportSite($site);
        foreach ($report as $virus_type => $report_type) {
            if ($virus_type == 'summary') {
                continue;
            }
            
            foreach ($report_type as $item) {
                if ($item['fn'] == $file) {
                    $finded_file = true;
                    break;
                }
            }
            
            if ($finded_file === true) {
                break;
            }
        }
        
        if ($finded_file !== true) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get access right to operation on the site
     * @param string $site
     * @return boolean
     */
    
    public static function accessToSite($site)
    {
        if (!$site) {
            return false;
        }
        
        $sites = AibolitModel::getAllSites();
        if (!isset($sites[$site])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Save file
     * @param string $file
     * @param string $content
     * @return boolean
     */
    
    public static function saveFile($file, $content)
    {
        $fp = fopen($file, 'w+');
        fwrite($fp, $content);
        fclose($fp);
        
        return true;
    }
    
    /**
     * Remove file
     * @param string $file
     * @return boolean
     */
    
    public static function removeFile($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Checking module installation
     * @return boolean
     */
    
    public static function checkInstall()
    {
        $components = array(
            'cron'      => AibolitCron::checkExistsInCron(),
            'php'       => AibolitCron::checkExistsPhp()
        );
        
        foreach ($components as $component) {
            if ($component !== true) {
                return $components;
            }
        }
        
        return true;
    }
    
    /**
     * Send email
     * @param string $subject
     * @param string $emailBody
     * @param string $email
     * @return boolean
     */
    
    public static function sendEmail($subject, $body, $email)
    {
        $mail = new mail();
        $mail_config = self::getMailConfig();
        $res = $mail->sendmail($email, $mail_config['fromWhoE'], $subject, $body);
        if ($res['code'] != 0) {
            return $res['message'];
        } else {
            return true;
        }
    }
    
    /**
     * Show content and exit
     * @param string|array $content
     */
    
    public static function showAndExit($content = '')
    {
        self::_show($content);
        self::_exit();
    }
    
    /**
     * Show success content and exit
     * @param string|boolean|array $content
     */
    
    public static function ajaxSuccess($content = '')
    {
        if (!is_array($content)) {
            $content = array(
                'success'   => true,
                'content'   => $content
            );
        }
        
        if (!isset($content['success'])) {
            $content['success'] = true;
        }
        
        self::_show($content);
        self::_exit();
    }
    
    /**
     * Show error content and exit
     * @param string|boolean|array $content
     */
    
    public static function ajaxError($content = '')
    {
        if (!is_array($content)) {
            $content = array(
                'error'   => true,
                'content'   => $content
            );
        }
        
        if (!isset($content['error'])) {
            $content['error'] = true;
        }
        
        self::_show($content);
        self::_exit();
    }
    
    /**
     * Show content
     * @param string|array $content
     */
    
    public static function _show($content)
    {
        echo is_array($content) ? json_encode($content) : $content;
    }
    
    /**
     * Exit
     */
    
    public static function _exit()
    {
        die();
    }
}
