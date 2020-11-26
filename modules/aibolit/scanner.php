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

class AibolitScanner
{
    /**
     * Log path
     * @var string
     */
    private static $logDir = '/etc/brainy/data/aibolit/scanner_log/';
    
    /**
     * Scanner path
     * @var string
     */
    private static $scannerDir = '/etc/brainy/lib/aibolit/';
    
    /**
     * Report path
     * @var string
     */
    private static $reportDir = '/etc/brainy/data/aibolit/report/';
    
    /**
     * Progress scan path
     * @var string
     */
    private static $progressDir = '/etc/brainy/data/aibolit/progress/';
    
    /**
     * Queue file path
     * @var string
     */
    private static $queueFilePath = '/etc/brainy/data/aibolit/queue';
    
    /**
     * Module parametres
     * @var boolean|array
     */
    private static $version = false;
    
    /**
     * Allowed scanner use memory
     * @var array
     */
    
    private static $allowed_use_memory = array(
        '256'       => array(
            'id'        => '256',
            'name'      => '256 MB'
        ),
        '384'       => array(
            'id'        => '384',
            'name'      => '384 MB'
        ),
        '512'       => array(
            'id'        => '512',
            'name'      => '512 MB'
        ),
        '1024'       => array(
            'id'        => '1024',
            'name'      => '1024 MB'
        ),
    );
    
    /**
     * Get versions changed history
     * @return string
     */
    
    public static function getVersionsHistory()
    {
        if (!file_exists(self::getPath() . 'changelog.txt')) {
            return false;
        }
        
        return file_get_contents(self::getPath() . 'changelog.txt');
    }
    
    /**
     * Get scanner version
     * @return string
     */
    
    public static function getVersion($rescan = false)
    {
        if (self::$version === false || $rescan === true) {
            if (!file_exists(self::getPath() . 'ai-bolit-hoster.php')) {
                return false;
            }
            $scannerContent = file_get_contents(self::getPath() . 'ai-bolit-hoster.php');
            preg_match('/Version:\s([^\/]*)/is', $scannerContent, $version);
            self::$version = trim($version[1]);
        }
        
        return self::$version;
    }
    
    /**
     * Get allowed scanner use memory
     * @return string
     */
    
    public static function getAllowedUseMemory()
    {
        return self::$allowed_use_memory;
    }
    
    /**
     * Get logs path
     * @return string
     */
    
    public static function getLogPath()
    {
        return self::$logDir;
    }
    
    /**
     * Get logs scanning site
     * @return string
     */
    
    public static function getLogs($site)
    {
        if (!file_exists(AibolitScanner::getLogPath() . $site)) {
            return '';
        }
        
        $logs = file_get_contents(AibolitScanner::getLogPath() . $site);
        $logs = preg_replace('/([^(0-9A-Za-z)]*)/is', "\n", $logs);
        return $logs;
    }
    
    /**
     * Remove logs scanning site
     * @return boolean
     */
    
    public static function removeLogs($site)
    {
        if (!file_exists(AibolitScanner::getLogPath() . $site)) {
            return true;
        }
        
        return unlink(AibolitScanner::getLogPath() . $site);
    }
    
    /**
     * Get logs site end scan
     * @return string
     */
    
    public static function getLogsEndScan($site)
    {
        if (!file_exists(AibolitScanner::getLogPath() . 'end_' . $site)) {
            return '';
        }
        
        return file_get_contents(AibolitScanner::getLogPath() . 'end_' . $site);
    }
    
    /**
     * Remove logs site end scan
     * @return boolean
     */
    
    public static function removeLogsEndScan($site)
    {
        if (!file_exists(AibolitScanner::getLogPath() . 'end_' . $site)) {
            return true;
        }
        
        return unlink(AibolitScanner::getLogPath() . 'end_' . $site);
    }
    
    /**
     * Get scanner path
     * @return string
     */
    
    public static function getPath()
    {
        return self::$scannerDir;
    }
    
    /**
     * Get site info
     * @return string
     */
    
    public static function getSiteInfo($site)
    {
        $info = array(
            'queue'             => false,
            'progress'          => 0,
            'virus_detected'    => 0,
            'time'              => 0
        );
        
        $info['queue'] = self::getQueueSite($site);
        
        if ($info['queue'] == AibolitModel::STATUS_SCANNED) {
            $progress = self::getProgressSite($site);
            $info['progress'] = $progress['progress'];
            $info['time'] = $progress['updated'];
        }
        
        if ($info['queue'] == AibolitModel::STATUS_COMPLETE) {
            $report = self::getReportSite($site);
            foreach ($report['summary']['counters'] as $count_detected) {
                $info['virus_detected'] = $info['virus_detected'] + $count_detected;
            }
            
            $info['time'] = $report['summary']['report_time'];
        }
        
        return $info;
    }
    
    /**
     * Get progress scan site
     * @return string
     */
    
    public static function getProgressSite($site)
    {
        if (!file_exists(self::getProgressPath() . $site)) {
            return false;
        }
        
        return json_decode(file_get_contents(self::getProgressPath() . $site), true);
    }
    
    /**
     * Get progress scan path
     * @return string
     */
    
    public static function getProgressPath()
    {
        return self::$progressDir;
    }
    
    /**
     * Get report site
     * @return string
     */
    
    public static function getReportSite($site)
    {
        if (!file_exists(self::getReportPath() . $site)) {
            return false;
        }
        
        return json_decode(file_get_contents(self::getReportPath() . $site), true);
    }
    
    /**
     * Get report site
     * @return string
     */
    
    public static function saveReportSite($site, $report)
    {
        if (!is_array($report)) {
            return false;
        }
        
        $fp = fopen(self::getReportPath() . $site, 'w+');
        fwrite($fp, json_encode($report));
        fclose($fp);
        
        return true;
    }
    
    /**
     * Get report path
     * @return string
     */
    
    public static function getReportPath()
    {
        return self::$reportDir;
    }
    
    /**
     * Change file data in report
     * @param string $file
     * @param string $site
     * @param array $data
     * @return boolean
     */
    
    public static function changeFileDataReport($file, $site, $data)
    {
        $report = self::getReportSite($site);
        foreach ($report as $virus_type => $report_type) {
            if ($virus_type == 'summary') {
                continue;
            }

            foreach ($report_type as $key => $item) {
                if ($item['fn'] == $file) {
                    $finded_file = true;
                    foreach ($data as $updateKey => $updateValue) {
                        $report[$virus_type][$key][$updateKey] = $updateValue;
                    }
                    
                    break;
                }
            }
        }

        return self::saveReportSite($site, $report);
    }
    
    /**
     * Get queue file path
     * @return string
     */
    
    public static function getQueueFilePath()
    {
        return self::$queueFilePath;
    }
    
    /**
     * Get queue data 
     * @return array
     */
    
    public static function getQueueData()
    {
        return AiBolitHelper::getServerData()->config_read(self::$queueFilePath);
    }
    
    /**
     * Set queue data 
     * @return array
     */
    
    public static function setQueueData($data)
    {
        AiBolitHelper::getServerData()->config_save(self::$queueFilePath, $data);
    }
    
    /**
     * Get queue site
     * @return string
     */
    
    public static function getQueueSite($site)
    {
        $data = self::getQueueData();
        return isset($data[$site]) ? $data[$site] : false;
    }
    
    /**
     * Add scan site to queue
     * @param string $site
     * @return boolean|string
     */
    
    public static function stopScan($site)
    {
        $sites = AibolitModel::getAllSites();
        
        if ($site == 'all') {
            foreach ($sites as $site) {
                self::stopScan($site['domain']);
            }
            
            return true;
        }
        
        if (!isset($sites[$site])) {
            return 'Please select site';
        }
        
        $queue = self::getQueueSite($site);
        if (in_array($queue, array(AibolitModel::STATUS_COMPLETE, AibolitModel::STATUS_EMPTY))) {
            return true;
        }
        
        if ($queue == AibolitModel::STATUS_SCANNED) {
            AibolitModel::setStatus(AibolitModel::STATUS_STOP, $site);
            AibolitCron::checkQueue();
        } else {
            AibolitModel::setAutoStatus($site);
        }
        
        return true;
    }
    
    /**
     * Add stop scan site to queue
     * @param string $site
     * @return boolean|string
     */
    
    public static function addScan($site)
    {
        $sites = AibolitModel::getAllSites();
        
        if ($site == 'all') {
            foreach ($sites as $site) {
                self::addScan($site['domain']);
            }
            
            return true;
        }
        
        if (!isset($sites[$site])) {
            return 'Please select site';
        }
        
        $queue = self::getQueueSite($site);
        if (in_array($queue, array(AibolitModel::STATUS_QUEUE, AibolitModel::STATUS_SCANNED))) {
            return true;
        }
        
        AibolitModel::setStatus(AibolitModel::STATUS_QUEUE, $site);
        AibolitCron::checkQueue();
        
        return true;
    }
    
    /**
     * Remove scanner site progress
     * @return boolean
     */
    
    public static function removeProgressScan($site) {
        if (!file_exists(self::getProgressPath() . $site)) {
            return true;
        }
        
        unlink(self::getProgressPath() . $site);
        
        return true;
    }
}