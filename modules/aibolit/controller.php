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

class AiBolitController
{
    /**
     * Now action
     * @var string
     */
    private $action;
    
    /**
     * Smarty
     * @var object 
     */
    public $smarty;
    
    /**
     * Smarty template
     * @var object 
     */
    
    public $tpl;
    
    /**
     * @var AiBolitController
     */
    
    private static $instance;
        
    /**
     * Instantiate and return a factory.
     * @return AiBolitController
     */
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Start init page
     * @param object $smarty
     * @param object $tpl
     */
    
    public function init($smarty, $tpl)
    {
        $this->smarty = $smarty;
        $this->tpl = $tpl;
        
        $this->setAction();
        
        if (AiBolitHelper::getServerData()->user['group_properties']['root'] != 'y') {
            $this->accessDenied();
            return false;
        }
        
        if (AiBolitHelper::checkInstall() !== true) {
            $this->installPage();
            return false;
        }
        
        if (!method_exists($this, $this->action . 'Action')) {
            $this->action = 'default';
        }
        
        $methodName = $this->action . 'Action';
        $this->$methodName();
    }
    
    /**
     * Auto set action from REQUEST
     */
    
    public function setAction()
    {
        if (isset($_REQUEST['subdo'])) {
            $this->action = $_REQUEST['subdo'];
        }
    }
    
    /**
     * Install page
     */
    
    private function installPage()
    {
        if ($this->action == 'install') {
            $result = AibolitCron::addCommandCrontab();
            if ($result !== true) {
                $command = AibolitCron::getCronCommandArray();
                $this->smarty->assign('crontab_command', $command);
                $this->smarty->assign('error_crontab', $result);
            } else {
                header("Location: /index.php?do=aibolit");
                AiBolitHelper::_exit();
            }
        }
        $this->smarty->assign('components', AiBolitHelper::checkInstall());
        $this->tpl->out = $this->smarty->fetch('aibolit/install.tpl');
    }
    
    /**
     * Default index page
     */
    
    private function defaultAction()
    {
        $this->smarty->assign('aibolitconf', AiBolitHelper::getConfig());
        $this->smarty->assign('cron_types', AibolitCron::getAllowedTypes());
        $this->smarty->assign('cron_times', AibolitCron::getAllowedTimes());
        $this->smarty->assign('scan_memorys', AibolitScanner::getAllowedUseMemory());
        $this->smarty->assign('scanner_versions_history', htmlspecialchars(AibolitScanner::getVersionsHistory()));
        $this->smarty->assign('scanner_version', htmlspecialchars(AibolitScanner::getVersion()));
        $this->smarty->assign('sites', AibolitModel::getAllSites());
        $this->smarty->assign('module_version', AiBolitHelper::getModuleVersion());
        $this->tpl->out = $this->smarty->fetch('aibolit/index.tpl');
    }
    
    private function show_table_sitesAction()
    {
        $this->smarty->assign('sites', AibolitModel::getAllSites());
        $this->tpl->out = $this->smarty->fetch('aibolit/show_table_sites.tpl');
        AiBolitHelper::ajaxSuccess($this->tpl->out);
    }
    
    private function show_table_reportAction()
    {
        $site = $_REQUEST['site'];
        $sites = AibolitModel::getAllSites();
        if (!isset($sites[$site])) {
            AiBolitHelper::ajaxError('Please select a site');
        }
        
        $this->smarty->assign('report', AibolitScanner::getReportSite($site));
        $this->smarty->assign('site', $site);
        $this->tpl->out = $this->smarty->fetch('aibolit/show_table_report.tpl');
        AiBolitHelper::ajaxSuccess($this->tpl->out);
    }
    
    private function show_logsAction()
    {
        $site = $_REQUEST['site'];
        if ($site != 'aibolit') {
            $sites = AibolitModel::getAllSites();
            if (!isset($sites[$site])) {
                AiBolitHelper::ajaxError('Please select a site');
            }
            
            $this->smarty->assign('scan_log', AibolitScanner::getLogs($site));
            $this->smarty->assign('endscan_log', AibolitScanner::getLogsEndScan($site));
            $this->smarty->assign('site', $site);
        } else {
            $this->smarty->assign('module_log', AiBolitHelper::getLogs());
        }
        
        // There are performance drawdowns when processing the response json format by the browser when the logs are very large
        //$this->tpl->out = $this->smarty->fetch('aibolit/show_logs.tpl');
        //AiBolitHelper::ajaxSuccess($this->tpl->out);
        
        $this->smarty->display('aibolit/show_logs.tpl');
        AiBolitHelper::showAndExit();
    }
    
    private function get_edit_fileAction()
    {            
        $site = $_REQUEST['site'];
        $file = $_REQUEST['file'];
        
        if (AiBolitHelper::accessToFile($file, $site) !== true) {
            AiBolitHelper::ajaxError('You don\'t have access to selected file');
        }
        
        if (!file_exists($file)) {
            AiBolitHelper::ajaxError('File not found');
        }
        
        $this->smarty->assign('file_content', htmlspecialchars(file_get_contents($file)));
        $this->smarty->assign('site', $site);
        $this->smarty->assign('file', $file);
        $this->tpl->out = $this->smarty->fetch('aibolit/edit_file.tpl');
        
        AiBolitHelper::ajaxSuccess($this->tpl->out);
    }
    
    private function save_fileAction()
    {            
        $site = $_REQUEST['site'];
        $file = $_REQUEST['file'];
        
        if (AiBolitHelper::accessToFile($file, $site) !== true) {
            AiBolitHelper::ajaxError('You don\'t have access to selected file');
        }
        
        if (!file_exists($file)) {
            AiBolitHelper::ajaxError('File not found');
        }
                
        if (AiBolitHelper::saveFile($file, $_REQUEST['file_content']) !== true) {
            AiBolitHelper::ajaxError('Error saving file');
        }
        
        AibolitScanner::changeFileDataReport($file, $site, array(
            'manual_editing'    => true
        ));
        
        AiBolitHelper::ajaxSuccess();
    }
    
    private function remove_fileAction()
    {            
        $site = $_REQUEST['site'];
        $file = $_REQUEST['file'];
        
        if (AiBolitHelper::accessToFile($file, $site) !== true) {
            AiBolitHelper::ajaxError('You don\'t have access to selected file');
        }
        
        if (AiBolitHelper::removeFile($file) !== true) {
            AiBolitHelper::ajaxError('Error deleting file');
        }
        
        AibolitScanner::changeFileDataReport($file, $site, array(
            'manual_deleting'    => true
        ));
        
        AiBolitHelper::ajaxSuccess();
    }
    
    private function update_autoAction()
    {
        $update = AiBolitUpdateBase::updateAuto();
        AiBolitHelper::showAndExit(array(
            'success'                   => $update === true ? true : false,
            'error'                     => $update !== true ? $update : false,
            'scanner_version'           => htmlspecialchars(AibolitScanner::getVersion(true)),
            'scanner_version_time'      => date('Y-m-d H:i:s', AiBolitHelper::getConfig('last_aibolit_update')),
            'scanner_versions_history'  => htmlspecialchars(AibolitScanner::getVersionsHistory())
        ));
    }
    
    private function update_from_addressAction()
    {
        $update = AiBolitUpdateBase::updateFromAddress();
        AiBolitHelper::showAndExit(array(
            'success'                   => $update === true ? true : false,
            'error'                     => $update !== true ? $update : false,
            'scanner_version'           => htmlspecialchars(AibolitScanner::getVersion(true)),
            'scanner_version_time'      => date('Y-m-d H:i:s', AiBolitHelper::getConfig('last_aibolit_update')),
            'scanner_versions_history'  => htmlspecialchars(AibolitScanner::getVersionsHistory())
        ));
    }
    
    private function update_from_fileAction()
    {
        $update = AiBolitUpdateBase::updateFromFile();
        AiBolitHelper::showAndExit(array(
            'success'                   => $update === true ? true : false,
            'error'                     => $update !== true ? $update : false,
            'scanner_version'           => htmlspecialchars(AibolitScanner::getVersion(true)),
            'scanner_version_time'      => date('Y-m-d H:i:s', AiBolitHelper::getConfig('last_aibolit_update')),
            'scanner_versions_history'  => htmlspecialchars(AibolitScanner::getVersionsHistory())
        ));
    }
    
    private function scanAction()
    {
        $site = $_REQUEST['site'];
        $command = $_REQUEST['command'];
        
        switch ($command) {
            case 'start':
                $status = AibolitScanner::addScan($site);
                AiBolitHelper::_show(array(
                    'success'                   => $status === true ? true : false,
                    'error'                     => $status !== true ? $status : false
                ));
                break;
            
            case 'stop':
                $status = AibolitScanner::stopScan($site);
                AiBolitHelper::_show(array(
                    'success'                   => $status === true ? true : false,
                    'error'                     => $status !== true ? $status : false
                ));
                break;
            
            default:
                AiBolitHelper::ajaxError('Please select command');
        }
        
        AiBolitHelper::_exit();
    }
    
    private function save_configAction()
    {
        AibolitHelper::setConfig($_POST['options']);
        AiBolitHelper::ajaxSuccess();
    }
    
    private function accessDenied()
    {
        $this->tpl->out = $this->smarty->fetch($GLOBALS['DOCUMENT_ROOT_PATH'] . $GLOBALS['template_path'] . '/common/access_denied.tpl');
    }
}