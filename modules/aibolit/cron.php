<?php
/**
 * AiBolit - module for brainycp.
 * Using the aibolit scanner code https://revisium.com/ai/
 *
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author      rzermak <rzermak@yandex.ru>
 * @link		https://github.com/Rzermak/brainy_aibolit
 * @version		1.0
 */

class AibolitCron
{
    /**
     * Pid path
     * @var string
     */
    
    private static $pidDir = '/etc/brainy/data/aibolit/pid/';
    
    /**
     * Allowed cron types
     * @var array
     */
    
    private static $cron_types = array(
        'off'       => array(
            'id'        => 'off',
            'name'      => 'Отключено'
        ),
        'day'       => array(
            'id'        => 'day',
            'name'      => 'Ежедневно',
            'time'      => '86400'
        ),
        'week'      => array(
            'id'        => 'week',
            'name'      => 'Раз в неделю',
            'time'      => '604800'
        ),
        'month'     => array(
            'id'        => 'month',
            'name'      => 'Раз в месяц',
            'time'      => '2592000'
        ),
    );
    
    /**
     * Console command for module
     * @var string
     */
    
    private static $consoleCommand = '/etc/brainy/src/compiled/php5/bin/php /etc/brainy/modules/aibolit/console.php';
    
    /**
     * Php version for scanner
     * @var string
     */
    
    private static $scannerPhpVersion = 'php70';
    
    /**
     * Get allowed cron types
     * @return array
     */
    
    public static function getAllowedTypes()
    {
        return self::$cron_types;
    }
    
    /**
     * Get allowed start times
     * @return array
     */
    
    public static function getAllowedTimes()
    {
        $allowed_times = array();
        for ($i = 0; $i < 24; $i++) {
            $allowed_times[$i] = array(
                'id'    => $i,
                'name'  => $i . ':00'
            );
        }
        
        return $allowed_times;
    }
    
    /**
     * Get pid path
     * @return string
     */
    
    public static function getPidPath()
    {
        return self::$pidDir;
    }
    
    /**
     * Set scanner site pid
     * @return string
     */
    
    public static function addPid($site, $pid)
    {
        $fp = fopen(self::getPidPath() . $site, 'w+');
        fwrite($fp, $pid);
        fclose($fp);
        
        return true;
    }
    
    /**
    * Get scanner site pid
    * @return string
    */
    
    public static function getPid($site)
    {
        if (!file_exists(self::getPidPath() . $site)) {
            return false;
        }
        
        return file_get_contents(self::getPidPath() . $site);
    }
    
    /**
     * Get scanner site pid
     * @return string
     */
    
    public static function removePid($site)
    {
        if (!file_exists(self::getPidPath() . $site)) {
            return true;
        }
        
        unlink(self::getPidPath() . $site);
    }
    
    /**
     * Get command to start scanner
     * @return string
     */
    
    public static function getConsoleCommand()
    {
        return self::$consoleCommand;
    }
    
    /**
     * Get command for crontab
     * @return array
     */
    
    public static function getCronCommandArray()
    {
        $data = array(
            'minutes'   => '*/1',
            'hours'     => '*',
            'days'      => '*',
            'months'    => '*',
            'weekdays'  => '*',
            'command'   => self::getConsoleCommand(),
            'output'    => '/dev/null 2 > /dev/null'
        );
        
        return $data;
    }
    
    /**
     * Checking the command in crontab
     * @return boolean
     */
    
    public static function checkExistsInCron()
    {
        $crontab = new crontab();
        $cron_list = $crontab->crontab_commands('root');
        foreach ($cron_list as $command) {
            if (stripos($command, self::getConsoleCommand()) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add command to crontab
     * @return boolean|string
     */
    
    public static function addCommandCrontab()
    {
        if (self::checkExistsInCron() === true) {
            return true;
        }
        
        $crontab = new crontab();
        $command = self::getCronCommandArray();
        $result = $crontab->crontab_cronjob_add(
            $command['minutes'],
            $command['hours'],
            $command['days'],
            $command['months'],
            $command['weekdays'],
            $command['command'] . ' > ' . $command['output'],
            'root'
        );
        return $result['code'] == 0 ? true : $result['message'];
    }
    
    /**
     * Get php version for scanner
     * @return string
     */
    
    public static function getScannerPhpVersion()
    {
        return self::$scannerPhpVersion;
    }
    
    /**
     * Checking for the correct version php
     * @return boolean
     */
    
    public static function checkExistsPhp()
    {
       $server_info = AiBolitHelper::getServer()->get_info();
       $php = isset($server_info['phparr'][self::getScannerPhpVersion()]) ? $server_info['phparr'][self::getScannerPhpVersion()] : 0;
       return $php ? true : false;
    }
    
    /**
     * Check plans tasks
     */
    
    public static function checkCron()
    {
        $startTasks = false;
        $config = AiBolitHelper::getConfig();
        if ($config['cron_time'] == date('H', time())) {
            $last_time = isset($config['cron_last']) ? $config['cron_last'] : 0;
            if (isset(self::$cron_types[$config['cron_type']]) && $config['cron_type'] != 'off') {
                if (time() - $last_time > self::$cron_types[$config['cron_type']]['time']) {
                    $startTasks = true;
                }
            }
        }
        
        if ($startTasks === true) {
            AibolitHelper::setConfig(array(
                'cron_last'   => time(),
            ));
            
            $mail_config = AiBolitHelper::getMailConfig();
            
            if ($config['auto_update_base']) {
                if (AiBolitUpdateBase::updateAuto() !== true) {
                    if ($config['send_email_detected']) {
                        AiBolitHelper::sendEmail($mail_config['fromWhoName'] . ' - Ai-bolit', 'Antivirus base automatic update failed', $config['email']);
                    }
                }
            }
            
            $status = AibolitScanner::addScan('all');
            if ($status !== true) {
                if ($config['send_email_detected']) {
                    AiBolitHelper::sendEmail($mail_config['fromWhoName'] . ' - Ai-bolit', 'Error while scanning all sites', $config['email']);
                }
            }
        }
    }
    
    /**
     * Checking the queue
     * @param boolean $is_console
     */
    
    public static function checkQueue($is_console = false)
    {
        if ($is_console === true) {
            self::checkCron();
        }
        
        $countScanned = 0;
        $scanCandidates = array();
        $config = AiBolitHelper::getConfig();
        $sites = AibolitModel::getAllSites();
        foreach ($sites as $site) {
            if ($site['scanner']['queue'] == AibolitModel::STATUS_SCANNED) {
                $pid = self::getPid($site['domain']);
                
                if (!$pid || (time() - $site['scanner']['time'] > 60)) {
                    AiBolitHelper::toLog('Process scanning ' . $site['domain'] . ' is hangs');
                    self::stopScan($site['domain']);
                    AibolitModel::setErrorStatus($site['domain']);
                    continue;
                }
                
                if (intval($site['scanner']['progress']) == 100) {
                    self::stopScan($site['domain']);
                    AibolitModel::setCompleteStatus($site['domain']);
                    continue;
                }
                
                $countScanned++;
            }
            
            if ($site['scanner']['queue'] == AibolitModel::STATUS_QUEUE) {
                $scanCandidates[] = $site['domain'];
            }
            
            if ($site['scanner']['queue'] == AibolitModel::STATUS_STOP) {
                AiBolitHelper::toLog('Find ' . $site['domain'] . ' queue to stopped');
                self::stopScan($site['domain']);
                AibolitModel::setAutoStatus($site['domain']);
            }
        }
        
        if (count($scanCandidates) > 0) {
            foreach ($scanCandidates as $site) {
                if ($config['max_thread'] <= $countScanned) {
                    break;
                }
                
                self::startScan($site);
                $countScanned++;
            }
        }
    }
    
    /**
     * Start scan site
     * @param string $site
     * @return boolean
     */
    
    public static function startScan($site)
    {
        $config = AiBolitHelper::getConfig();
        $siteData = AibolitModel::getSiteInfo($site);
        
        if (!isset($siteData['dir'])) {
            AiBolitHelper::toLog('Error dir site ' . $site);
            return false;
        }
        
        AiBolitHelper::toLog('Start scan ' . $site);
        AibolitScanner::removeLogs($site);
        AibolitScanner::removeLogsEndScan($site);
        AibolitScanner::createLogStartScanned($site);
        AibolitModel::setScannedStatus($site);
        $command =  '/usr/bin/php70/bin/php /etc/brainy/modules/aibolit/console.php'
                  . ' --scanstart'
                  . ' --site=' . $site . ''
                  . ' --mode=' . ($config['scan_fast_mode'] == 1 ? 1 : 2)
                  . ' --path=' . $siteData['dir']
                  . ' --json_report=' . AibolitScanner::getReportPath() . $site
                  . ' --no-html'
                  . ' --memory=' . $config['scan_memory'] . 'M'
                  . ' --progress=' . AibolitScanner::getProgressPath() . $site
                  . ' --logfile=' . AibolitScanner::getLogPath() . 'end_' . $site
                  . ' > ' . AibolitScanner::getLogPath() . $site
                  . ' >&1 & echo $!;';
        
        $pid = exec($command, $output);
        self::addPid($site, $pid);
        return true;
    }
    
    /**
     * Stop scan site
     * @param string $site
     * @return boolean
     */
    
    public static function stopScan($site)
    {
        AiBolitHelper::toLog('Stop scan ' . $site);
        $pid = intval(self::getPid($site));
        if ($pid) {
            $command = 'kill -9 ' . $pid;
            AiBolitHelper::toLog('Exec command: ' . $command);
            exec($command, $output);
            self::removePid($site);
        }
        AibolitScanner::removeProgressScan($site);
        return true;
    }
}
