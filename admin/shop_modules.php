<?php

//Include diff class
require_once(getShopBasePath()."/modules/ag_modulemanager/diff.php");

class Shop_Modules extends oxAdminDetails
{
    protected $_sThisTemplate = 'shop_modules.tpl';

    public function render()
    {
        
		$this->_aViewData["modules"] = $this->_buildModuleArray();
		
        parent::render();
				
        return $this->_sThisTemplate;
    }
    
    public function save(){
    	$name  		= oxConfig::getParameter("modulename");
    	$value 		= oxConfig::getParameter("modulevalue");
    	$ordering 	= oxConfig::getParameter("modulepos");
    	
    	$this->_addModuleEntry($name,$value,$ordering);    	    	
    }
    
    protected function _addModuleEntry($name,$value,$ordering){
    	
    	//Remove entry if it already exists
    	$this->_removeModuleEntry($name,$value);
    	
    	$myConfig  = $this->getConfig();
    	$aModules  = $myConfig->getShopConfVar("aModules");
    	
    	$this->_log[] = "adding module entry $name => $value ($ordering)";
    	  	
    	if(array_key_exists($name,$aModules)){
    		$entry = $aModules[$name];
    		$entry = explode("&",$entry);
    		    		    		    		    		
    		if(isset($entry[$ordering])){
    			array_splice($entry,$ordering,0,$value);    			  			
    		}else{
    			$entry[] = $value;
    		}
    		    		
    		$aModules[$name] = implode("&",$entry);
    		
    	}else{
    		$aModules[$name] = $value;
    	}
    	
    	$myConfig->saveShopConfVar("arr","aModules",$aModules);
    }
    
    protected function _removeModuleEntry($name,$value){
    	
    	$myConfig  = $this->getConfig();
    	$aModules  = $myConfig->getShopConfVar("aModules");
    	
    	$entry = $aModules[$name];
    	$entry = explode("&",$entry);
    	
    	foreach($entry as $key => $v){
    		if($value == $v){
    			$this->_log[] = "removing module entry $name => $value";
    			unset($entry[$key]);
    			break;
    		}
    	}

    	if(count($entry) == 0){
    		unset($aModules[$name]);
    	}else{
    		$aModules[$name] = implode("&",$entry);	
    	}
    	
    	$myConfig->saveShopConfVar("arr","aModules",$aModules);
    	
    }
    
    public function delete(){
    	$name = oxConfig::getParameter("delmod");
    	$value = oxConfig::getParameter("delval");
    	
    	$this->_removeModuleEntry($name,$value);
    }
    
    protected function _buildModuleArray(){
    	$myConfig  = $this->getConfig();
    	$modules = $myConfig->getShopConfVar("aModules");
    	
    	$out = array();
    	
    	foreach($modules as $key => $value){
    		
    		$o = new stdClass();
    		$o->name = $key;
    		$o->entries = explode("&",$value);
    		$out[] = $o;
    		
    	}
    	
    	return $out;
    	
    }
    
	protected function _addItemAt($array, $index, $value)
	{
		return array_merge(array_slice($array, 0, $index), $value, array_slice($array, $index));
	}
    
    protected $_log = array();
    
    public function install(){
    	$myConfig  = $this->getConfig();
    	$file = $myConfig->getUploadedFile("file");
    	
    	//Move our archive to tmp
    	$tmp = getShopBasePath()."tmp";
    	mkdir($tmp."/installtmp");
    	$newname = rand().$file["name"];
    	$archfolder = $tmp."/installtmp/";
    	$archfile = $archfolder.$newname; 
    	
    	move_uploaded_file($file["tmp_name"],$archfile);
    	
    	//Now extract that
		$zip = new ZipArchive();				
		$zip->open($archfile);		
		$zip->extractTo($archfolder);		
		$zip->close();
    	
    	//First run sql commands, see if we can find a file
    	$this->_runInstallSQL($archfolder);
    	
    	//Now copy everything from copy_this
    	$this->_copyThisFiles($archfolder);
    	
    	$this->_aViewData["changes"] = $this->_getChangedFullFiles($archfolder);
    	
    	//Install modules
    	$this->_installModules($archfolder);
    	
    	
		$this->_aViewData["archfile"] = $archfile;
		$this->_aViewData["changedfull"] = oxConfig::getParameter("changedfull");
		$this->_aViewData["finishinstall"] = true;
		$this->_aViewData["log"] = $this->_log;		
    }
    
    public function installfinish(){
    	$tmp = getShopBasePath()."tmp";
    	$archfolder = $tmp."/installtmp/";
    	$archfile = oxConfig::getParameter("archfile");
    	$changedfullname = oxConfig::getParameter("changedfull");    	
    	$changedfullfolder = $archfolder.$changedfullname;
    	    	
    	//Copy changed_full files that were selected to the shop
    	$files = oxConfig::getParameter("changes");
    	if($files && count($files) > 0){
    		
	    	$today = date("d.m.y G:i:s");
	    	    	    	
	    	foreach($files as $change){
	    		
	    		//Get name for file inside of shop
	    		$dest = getShopBasePath() . "/" . str_replace($changedfullfolder,"",$change);
	    		
	    		//Backup the destination file    		
	    		copy($dest,$dest.".bck-".$today);
	    		
	    		unlink($dest);
	    		    		    		
	    		//Copy the change
	    		copy($change,$dest);
	    		
	    	}
    	}
    	
    	//Delete tmp folder
    	$this->_killDir($archfolder);
    	@unlink($archfile);
    	
    	//Clear tmp
    	foreach (glob($tmp."/*.*") as $filename) {
		    @unlink($filename);
		}
		
		oxUtilsView::getInstance()->addErrorToDisplay("Installation erfolgreich abgeschlossen");
    }
    
    public function compare(){
    	$tmp = getShopBasePath()."tmp";
    	$archfolder = $tmp."/installtmp/";
    	$changedfullname = oxConfig::getParameter("changedfull");    	
    	$changedfullfolder = $archfolder.$changedfullname;
    	
    	$file = oxConfig::getParameter("comp");
    	
    	//Get name for file inside of shop
    	$dest = getShopBasePath() . "/" . str_replace($changedfullfolder,"",$file);
    	
    	$diff = new diff;
		$this->_aViewData["diff"] = $diff->inline($dest,$file,2);
		$this->_aViewData["numchanges"] = count($diff->changes);
		$this->_aViewData["f1"] = $dest;
		$this->_aViewData["f2"] = $file;
		
		
    	
    }
    
    protected function _runInstallSQL($folder){
    	
    	//$this->_log[] = "_runInstallSQL";
    	
    	$sqlname = oxConfig::getParameter("installfile");
    	$sqlfile = $folder.$sqlname;
    	
    	if(file_exists($sqlfile)){
    		
    		$sqlCommands = file_get_contents($sqlfile);
    		$db = oxDb::getDb();
    		
    		$this->_log[] = "Running SQL Commands:";
    		
    		$sqlCommands = explode("\n",$sqlCommands);
    		foreach($sqlCommands as $command){
    			$this->_log[] = $command;
    			$db->Execute($command);
    		}    		
    	}
    	   	
    }
    
    protected function _copyThisFiles($folder){
    	
    	//$this->_log[] = "_copyThisFiles";
    	
    	$copythisname = oxConfig::getParameter("copythis");
    	$copythisfolder = $folder.$copythisname."/";
    	
    	if(file_exists($copythisfolder)){
    		
    		//Copy all files recursivly
    		$source = $copythisfolder;
    		$dest   = getShopBasePath()."/";
    		
    		$this->_log[] = "Copying files from $source:";
    		
    		$this->_smartCopy($source,$dest);
    		
    	}
    	
    }
    
    protected function _getChangedFullFiles($folder){
    	
    	$changedfullname = oxConfig::getParameter("changedfull");
    	$changedfullfolder = $folder.$changedfullname."/";
    	
    	$this->_log[] = "Checking for changed_full in $changedfullfolder";
    	
    	if(file_exists($changedfullfolder)){
    		
    		$filesList = $this->_findFilesInFolder($changedfullfolder);
    		    		    		
    	}
    	
    	return $filesList;
    	
    }
    
    protected function _findFilesInFolder($folder,$files=array()){
    	
    	//Using SPL here
    	$dir_iterator = new RecursiveDirectoryIterator($folder);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    	   	
    	foreach($iterator  as $file){
    		if($file->isFile()){
    			$files[] = $file->getPathname();
    		}
    	}
    	
    	return $files;
    }
    
    protected function _installModules($folder){
    	
    	//$this->_log[] = "_installModules";
    	
    	$moduleEntries = oxConfig::getParameter("modulesentries");
    	$moduleEntryFile = $folder . oxConfig::getParameter("modulesfile");
    	
    	if($moduleEntries == "" && file_exists($moduleEntryFile)){
    		$moduleEntries = file_get_contents($moduleEntryFile);
    	}
    	
    	$this->_log[] = "Installing Modules ($moduleEntryFile)";
    	$this->_log[] = $moduleEntries;
    	
    	if($moduleEntries != ""){    		
    		$moduleArray = explode("\n",$moduleEntries);
    		foreach($moduleArray as $module){    
    			$this->_log[] = $module;			
    			$splitted = explode("=>",$module);
    			
    			$splValues = explode("&",trim($splitted[1]));
    			foreach($splValues as $modVal){
    				$this->_addModuleEntry(trim($splitted[0]),trim($modVal),0);	
    			}
    			  			
       		}    		
    	}
    	
    }
    
    protected function _smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result=false;
       
        if (is_file($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if (!file_exists($dest)) {
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
                }
                $__dest=$dest."/".basename($source);
            } else {
                $__dest=$dest;
            }
            
            if(is_file($__dest) && is_writable($__dest) == FALSE){
            	$this->_log[] = "Can't copy file from $source to $__dest - permission denied";
            	$result = false;	
            }else{
            	$this->_log[] = "Copying file from $source to $__dest";            
		        $result=copy($source, $__dest);
		        chmod($__dest,$options['filePermission']);	
            }
            
            
           
        } elseif(is_dir($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if ($source[strlen($source)-1]=='/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest=$dest.basename($source);
                    @mkdir($dest);
                    chmod($dest,$options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                }
            }

            $dirHandle=opendir($source);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                     if(!is_dir($source."/".$file)) {
                        $__dest=$dest."/".$file;
                    } else {
                        $__dest=$dest."/".$file;
                    }
                    //echo "$source/$file ||| $__dest<br />";
                    $result=$this->_smartCopy($source."/".$file, $__dest, $options);
                }
            }
            closedir($dirHandle);
           
        } else {
            $result=false;
        }
        return $result;
    } 
    
    protected function _killDir($directory, $empty=FALSE)
	{
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
	
		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory))
		{
			// ... we return false and exit the function
			return FALSE;
	
		// ... if the path is not readable
		}elseif(!is_readable($directory))
		{
			// ... we return false and exit the function
			return FALSE;
	
		// ... else if the path is readable
		}else{
	
			// we open the directory
			$handle = opendir($directory);
	
			// and scan through the items inside
			while (FALSE !== ($item = readdir($handle)))
			{
				// if the filepointer is not the current directory
				// or the parent directory
				if($item != '.' && $item != '..')
				{
					// we build the new path to delete
					$path = $directory.'/'.$item;
	
					// if the new path is a directory
					if(is_dir($path)) 
					{
						// we call this function with the new path
						$this->_killDir($path);
	
					// if the new path is a file
					}else{
						// we remove the file
						unlink($path);
					}
				}
			}
			// close the directory
			closedir($handle);
	
			// if the option to empty is not set to true
			if($empty == FALSE)
			{
				// try to delete the now empty directory
				if(!rmdir($directory))
				{
					// return false if not possible
					return FALSE;
				}
			}
			// return success
			return TRUE;
		}
	}

}