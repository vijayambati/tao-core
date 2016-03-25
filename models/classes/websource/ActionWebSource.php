<?php
/**
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\tao\model\websource;

use Slim\Http\Stream;
use League\Flysystem\FileNotFoundException;
use \core_kernel_fileSystem_FileSystem;

/**
 * Grants Access to compiled data via the MVC
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao
 
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class ActionWebSource extends BaseWebsource
{	
    public static function spawnWebsource(core_kernel_fileSystem_FileSystem $fileSystem) {
        return self::spawn($fileSystem);
    }
    
	public function getAccessUrl($relativePath) {
	    return _url('accessFile/'.base64_encode($this->getId().' '.trim($relativePath, DIRECTORY_SEPARATOR)).'/','File', 'tao');
	}

    /**
     * @param $filePath
     * @throws \tao_models_classes_FileNotFoundException
     * @return Stream
     */
    public function getFileStream($filePath)
    {
        if ($filePath === '') {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        $fs = $this->getFileSystem();
        try {
            $resource = $fs->readStream($filePath);
        } catch(FileNotFoundException $e) {
            throw new \tao_models_classes_FileNotFoundException("File not found");
        }
        return new Stream($resource);
    }
}