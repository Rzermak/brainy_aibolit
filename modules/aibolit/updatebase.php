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

class AiBolitUpdateBase
{
    /**
     * Scanner update path
     * @var string
     */
    
    private static $scannerUpdateTmpDir = '/etc/brainy/data/aibolit/tmp/';
    
    /**
     * List files to update
     * @var array
     */
    
    private static $updateFiles = array(
        'ai-bolit/ai-bolit-hoster.php'      => 'ai-bolit-hoster.php',
        'ai-bolit/AIBOLIT-BINMALWARE.db'    => 'AIBOLIT-BINMALWARE.db',
        'changelog.txt'                     => 'changelog.txt',
        'readme.txt'                        => 'readme.txt',
    );
    
    /**
     * Get update tmp dir
     * @return string
     */
    
    public static function getUpdateTmpDir()
    {
        return self::$scannerUpdateTmpDir;
    }
    
    /**
     * Auto update base
     * @return boolean|string
     */
    
    public static function updateAuto()
    {
        self::clearUploadDir();
        
        $content = file_get_contents('https://revisium.com/ai/');
        preg_match('/href\="([^\?q=]*)\?q\=([^\"]*)"/is', $content, $link);
        if (!$link[1] || !$link[2]) {
            return 'Not find link to arhiver';
        }
        
        $file_link = 'https:' . $link[1] . '?q=' . $link[2];
        $content = file_get_contents($file_link);
        
        if (!$content) {
            return 'Error download file ' . $file_link;
        }
        
        $fp = fopen(self::getUpdateTmpDir() . 'archive.zip', 'w+');
        fwrite($fp, $content);
        fclose($fp);
        
        $update = self::update();
        
        if ($update === true) {
            AibolitHelper::setConfig(array(
                'last_archive_link'     => $file_link,
                'last_aibolit_update'   => time(),
            ));
        }
        
        return $update;
    }
    
    /**
     * Update base from link to file
     * @return boolean|string
     */
    
    public static function updateFromAddress()
    {
        self::clearUploadDir();
        
        $file_link = $_POST['archive_address'];
        if (!$file_link) {
            return 'Address is wrong';
        }
        
        $content = file_get_contents($file_link);
        
        if (!$content) {
            return 'Error download file ' . $file_link;
        }
        
        $fp = fopen(self::getUpdateTmpDir() . 'archive.zip', 'w+');
        fwrite($fp, $content);
        fclose($fp);
        
        $update = self::update();
        
        if ($update === true) {
            AibolitHelper::setConfig(array(
                'last_archive_link'     => $file_link,
                'last_aibolit_update'   => time(),
            ));
        }
        
        return $update;
    }
    
    /**
     * Update base from loaded file
     * @return boolean|string
     */
    
    public static function updateFromFile()
    {
        self::clearUploadDir();
        
        if (!isset($_FILES['archive_scanner'])) {
            return 'Please select file';
        }
        
        if (!move_uploaded_file($_FILES['archive_scanner']['tmp_name'], self::getUpdateTmpDir() . 'archive.zip')) {
            return 'Error upload file';
        }
        
        $update = self::update();
        
        if ($update === true) {
            AibolitHelper::setConfig(array(
                'last_aibolit_update'   => time(),
            ));
        }
        
        return $update;
    }
    
    /**
     * Update base from update folder
     * @return boolean|string
     */
    
    private static function update()
    {
        if (self::unpackArchive() !== true) {
            self::clearUploadDir();
            return 'Error unpack archive';
        }
        
        if (self::checkNewFiles() !== true) {
            self::clearUploadDir();
            return 'New version files is wrong';
        }
        
        foreach (self::$updateFiles as $file => $save_file) {
            unlink(AibolitScanner::getPath() . $save_file);
            copy(self::getUpdateTmpDir() . $file, AibolitScanner::getPath() . $save_file);
        }
        
        self::clearUploadDir();
        return true;
    }
    
    /**
     * Check the presence of all necessary files in the update
     * @return boolean
     */
    
    private static function checkNewFiles()
    {
        foreach (self::$updateFiles as $file => $save_file) {
            if (!file_exists(self::getUpdateTmpDir() . $file)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Unpack the update archive
     * @return boolean
     */
    
    private static function unpackArchive()
    {
        if (!file_exists(self::getUpdateTmpDir() . 'archive.zip')) {
            return false;
        }
        
        $zip = new ZipArchive;
        if ($zip->open(self::getUpdateTmpDir() . 'archive.zip') === true) {
            $zip->extractTo(self::getUpdateTmpDir());
            $zip->close();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete files from update dir
     */
    
    public static function clearUploadDir()
    {
        $recursive = new RecursiveDirectoryIterator(self::getUpdateTmpDir(), RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($recursive, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }
}
