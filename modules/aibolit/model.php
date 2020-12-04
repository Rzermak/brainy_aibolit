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

class AibolitModel
{
    /**
     * Site statuses
     */
    
    const STATUS_EMPTY = '0';
    const STATUS_QUEUE = 'waiting';
    const STATUS_STOP = 'stopped';
    const STATUS_SCANNED = 'scanning';
    const STATUS_COMPLETE = 'complete';
    const STATUS_ERROR = '-1';
    
    /**
     * Users list path
     * @var string
     */
    private static $usersDir = '/etc/brainy/data/users/';
    
    /**
     * Users list
     * @var array|boolean
     */
    
    private static $users = false;
    
    /**
     * Sites list
     * @var array|boolean
     */
    
    private static $sites = false;
    
    /**
     * Get all users
     * @return array|boolean
     */
    
    public static function getAllUsers()
    {
        if (self::$users === false) {
            if ($handle = opendir(self::$usersDir)) {
                self::$users = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir(self::$usersDir . $file) || in_array($file, array('.', '..'))) {
                        continue;
                    }
                    
                    self::$users[$file] = AiBolitHelper::getServer()->request_vars_m('users', $file);
                }
                closedir($handle);
            }
        }
        
        return self::$users;
    }
    
    /**
     * Get user data
     * @param string $user
     * @return array|boolean
     */
    
    public static function getUser($user)
    {
        $users = self::getAllUsers();
        return isset($users[$user]) ? $users[$user] : false;
    }
    
    /**
     * Get all sites
     * @return array|boolean
     */
    
    public static function getAllSites()
    {
        if (self::$sites === false) {
            $users = self::getAllUsers();
            if (!is_array($users) || $users === false) {
                return false;
            }
            
            $autoScanSites = self::getAutoScanSites();
            foreach ($users as $user => $userData) {
                $sites = AiBolitHelper::getServer()->get_virt_hosts($user);
                foreach ($sites as $site) {
                    $site['user'] = $user;
                    $site['auto_scan'] = in_array($site['domain'], $autoScanSites) ? true : false;
                    $site['scanner'] = AibolitScanner::getSiteInfo($site['domain']);
                    self::$sites[$site['domain']] = $site;
                }
            }
        }
        
        return self::$sites;
    }
    
    /**
     * Get auto scanning sites
     * @return array
     */
    
    public static function getAutoScanSites()
    {
        $autoScanSites = array();
        $config = AiBolitHelper::getConfig();
        foreach ($config as $param => $value) {
            if (preg_match('/as\_(.*)/is', $param) && $value == 1) {
                $autoScanSites[] = preg_replace('/as\_(.*)/is', '\\1', $param);
            }
        }
        
        return $autoScanSites;
    }
    
    /**
     * Set auto scanning site
     * @param string $site
     * @param boolean $type
     */
    
    public static function setAutoScanSite($site, $type = false)
    {
        $data = array();
        $data['as_' . $site] = $type;
        AibolitHelper::setConfig($data);
    }
    
    /**
     * Update cache site info
     * @param string $site
     * @return boolean
     */
    
    public static function updateCacheSiteInfo($site)
    {
        $siteInfo = self::getSiteInfo($site);
        if ($site !== false) {
            $siteInfo['scanner'] = AibolitScanner::getSiteInfo($site);
            self::$sites[$site] = $siteInfo;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get site info
     * @return array|boolean
     */
    
    public static function getSiteInfo($site)
    {
        $sites = self::getAllSites();
        return isset($sites[$site]) ? $sites[$site] : false;
    }
    
    /**
     * Set another status site
     * @param string $status
     * @param string $site
     * @return boolean
     */
    
    public static function setStatus($status, $site)
    {
        AiBolitHelper::toLog('Set status ' . $status . ' for ' . $site);
        AibolitScanner::setQueueData(array(
            $site   => $status
        ));
        AibolitModel::updateCacheSiteInfo($site);
        return true;
    }
    
    /**
     * Set status 'scanning'
     * @param string $site
     * @return boolean
     */
    
    public static function setScannedStatus($site)
    {
        return self::setStatus(self::STATUS_SCANNED, $site);
    }
    
    /**
     * Set status 'complete'
     * @param string $site
     * @return boolean
     */
    
    public static function setCompleteStatus($site)
    {
        return self::setStatus(self::STATUS_COMPLETE, $site);
    }
    
    /**
     * Set error status '-1'
     * @param string $site
     * @return boolean
     */
    
    public static function setErrorStatus($site)
    {
        return self::setStatus(self::STATUS_ERROR, $site);
    }
    
    /**
     * Set status 'complete' if exists report, otherwise '0'
     * @param string $site
     * @return boolean
     */
    
    public static function setAutoStatus($site)
    {
        return self::setStatus((AibolitScanner::getReportSite($site) !== false ? self::STATUS_COMPLETE : self::STATUS_EMPTY), $site);
    }
}
