<?php

namespace MicroweberPackages\Backup\Http\Controllers\Admin;

use Illuminate\Http\Request;
use MicroweberPackages\Backup\BackupManager;

class BackupController
{

    public $manager;

    public function __construct()
    {
        $this->manager = new \MicroweberPackages\Backup\BackupManager();
    }


    public function get()
    {
        $backupLocation = $this->manager->getBackupLocation();

        $backupFiles = [];


        $files = preg_grep('~\.(sql|zip|json|xml|xlsx|csv|xls)$~', scandir($backupLocation));
        if ($files) {
            foreach ($files as $file) {
                $backupFiles[] = normalize_path($backupLocation. $file,false);
            }
        }

        if (! empty($backupFiles)) {
            usort($backupFiles, function ($a, $b) {
                return filemtime($a) < filemtime($b);
            });
        }
        $backups = array();
        if (! empty($backupFiles)) {
            foreach ($backupFiles as $file) {

                if (is_file($file)) {
                    $mtime = filemtime($file);

                    $backup = array();
                    $backup['filename'] = basename($file);
                    $backup['date'] = date('F d Y', $mtime);
                    $backup['time'] = str_replace('_', ':', date('H:i:s', $mtime));
                    $backup['size'] = filesize($file);

                    $backups[] = $backup;
                }
            }
        }

        return $backups;
    }

    public function import(Request $request)
    {

    }

    public function download(Request $request)
    {
        $fileId = $request->get('file');

        $fileId = str_replace('..', '', $fileId);

        // Check if the file has needed args
        if (! $fileId) {
            return array(
                'error' => 'You have not provided filename to download.'
            );
        }

        $backupLocation = $this->manager->getBackupLocation();

        // Generate filename and set error variables
        $filename = $backupLocation . $fileId;
        $filename = str_replace('..', '', $filename);
        if (! is_file($filename)) {
            return array(
                'error' => 'You have not provided a existing filename to download.'
            );
        }

        // Check if the file exist.
        if (file_exists($filename)) {

            // Add headers
            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . basename($filename));
            header('Content-Length: ' . filesize($filename));

            // Read file
            $this->_readfileChunked($filename);
        } else {
            return array(
                'error' => 'File does not exist.'
            );
        }
    }

    public function upload(Request $request)
    {

        $src = $request->post('src', false);

        if (!$src) {
            return array(
                'error' => 'You have not provided src to the file.'
            );
        }

        $checkFile = url2dir(trim($src));

        $backupLocation = $this->manager->getBackupLocation();

        if (is_file($checkFile)) {
            $file = basename($checkFile);

            if (copy($checkFile, $backupLocation . $file)) {
                @unlink($checkFile);

                return array(
                    'success' => "$file was moved!"
                );
            } else {
                return array(
                    'error' => 'Error moving uploaded file!'
                );
            }
        } else {
//			return array(
//				'error' => 'Uploaded file is not found!'
//			);
        }
    }

    public function export(Request $request)
    {
        $tables = array();
        $categoriesIds = array();
        $contentIds = array();

        $manager = new BackupManager();

        $items = $request->get('items', false);
        if ($items) {
            foreach (explode(',', $items) as $item) {
                if (!empty($item)) {
                    $tables[] = trim($item);
                }
            }
        }

        if ($items == 'template') {
            $manager->setExportMedia(true);
            $manager->setExportOnlyTemplate(template_name());
        }

        if ($items == 'template_default_content') {
            $manager->setExportMedia(true);
            $manager->setExportAllData(true);
            $manager->addSkipTable('login_attempts');
            $manager->addSkipTable('multilanguage_translations');
            $manager->addSkipTable('multilanguage_supported_locales');
            $manager->addSkipTable('translation_keys');
            $manager->addSkipTable('translation_texts');
        }

        $manager->setExportData('tables', $tables);

        $format = $request->get('format',false);
        if ($format) {
            $manager->setExportType($format);
        }

        if ($request->get('all',false)) {
            if ($request->get('include_media') == 'true') {
                $manager->setExportMedia(true);
            }
            $manager->setExportAllData(true);
        }

        if ($request->get('debug', false)) {
            $manager->setLogger(new BackupV2Logger());
        }

        if ($request->get('content_ids', false)) {
            $manager->setExportData('contentIds', $request->get('content_ids'));
        }

        if ($request->get('categories_ids', false)) {
            $manager->setExportData('categoryIds', $request->get('categories_ids'));
        }

        $includeModules = array();
        if ($request->get('include_modules', false)) {
            $includeModules = explode(',' , $request->get('include_modules'));
        }
        if (!empty($includeModules) && is_array($includeModules)) {
            $manager->setExportModules($includeModules);
        }

        $includeTemplates = array();
        if ($request->get('include_templates', false)) {
            $includeTemplates = explode(',' , $request->get('include_templates'));
        }
        if (!empty($includeTemplates) && is_array($includeTemplates)) {
            $manager->setExportTemplates($includeTemplates);
        }

        if (is_ajax()) {
            header('Content-Type: application/json');
        }

        return $manager->startExport();
    }

    public function delete(Request $request)
    {
        $fileId = $request->get('id');

        // Check if the file has needed args
        if (! $fileId) {
            return array(
                'error' => 'You have not provided filename to be deleted.'
            );
        }

        $backupLocation = $this->manager->getBackupLocation();
        $filename = $backupLocation . $fileId;

        $fileId = str_replace('..', '', $fileId);
        $filename = str_replace('..', '', $filename);

        if (is_file($filename)) {
            unlink($filename);
            return array(
                'success' => "$fileId was deleted!"
            );
        } else {
            $filename = $backupLocation . $fileId . '.sql';
            if (is_file($filename)) {
                unlink($filename);

                return array(
                    'success' => "$fileId was deleted!"
                );
            }
        }
    }

    private function _readfileChunked($filename, $retbytes = true)
    {
        $filename = str_replace('..', '', $filename);

        $chunkSize = 1024 * 1024;
        $buffer = '';
        $cnt = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }

        while (! feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            if (ob_get_level() > 0) {
                ob_flush();
                flush();
            }
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }

        return $status;
    }

}



class BackupV2Logger {

    public function log($log) {
        echo $log . '<br />';
    }

}
