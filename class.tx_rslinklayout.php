<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008-2013 Rene Staeker <typo3@rs-softweb.de>
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Script 'class.tx_rslinklayout.php'
 *
 * $Id$
 *
 * @author Rene Staeker <typo3@rs-softweb.de>
 * @package TYPO3
 * @subpackage tx_rslinklayout
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_rslinklayout
 *   62:     function main($content, $conf)
 *  111:     function prepare_fileicons($extensions,$filepaths)
 *  158:     function extend_link_params($original, $extension, $delimiter)
 *  203:     function replace_link_params($original, $extension)
 *  271:     function recreate_link($params)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_rslinklayout {
	// reference to the calling object.
	var $cObj;
	// the array with the filetype-image-mapping
	var $fileicons;

	/**
	 * Main function
	 *
	 * @param	string		Input content
	 * @param	array		TypoScript configuration of the plugin
	 * @return	string		HTML output
	 */
	function main($content, $conf) {

		global $TSFE;

		// break if no class is set by TYPO3
		if (strpos($content['TAG'], 'class=') === FALSE) {
			return $content['TAG'];
		}

		if ($conf['linkTargetEnabled'] == True) {
			if ($content['TYPE'] == 'url') {
				$linkTargetImg = $this->cObj->IMAGE($conf['linkTargetExt.']);
				$content['TAG'] = $this->replace_link_params($content['TAG'], $conf['linkTargetExtParams']);
			} elseif ($content['TYPE'] == 'mailto') {
				$linkTargetImg = $this->cObj->IMAGE($conf['linkTargetMailto.']);
				$content['TAG'] = $this->replace_link_params($content['TAG'], $conf['linkTargetMailtoParams']);
			} else {
				$linkTargetImg = $this->cObj->IMAGE($conf['linkTargetInt.']);
				$content['TAG'] = $this->replace_link_params($content['TAG'], $conf['linkTargetIntParams']);
			}
		}

		if ($conf['linkFiletypeEnabled'] == True) {
			$this->prepare_fileicons($conf['linkFiletypeList'],$conf['linkFiletypePaths']);

			$url = $content['url'];

			$filetype=strtolower(substr($url, strrpos($url, '.')+1));
			if ($this->fileicons[$filetype] <> '') {
				$linkFiletypeArray = $this->conf['ImageCObject.'];
				$linkFiletypeArray['file'] = $this->fileicons[$filetype];
				$linkFiletypeArray['file.']['maxH'] = $conf['linkFiletypeHeight'];
				$linkFiletypeArray['wrap'] = $conf['linkFiletypeWrap'];
				$linkFiletypeArray['stdWrap.']['addParams.']['alt'] = strtoupper(substr($url, strrpos($url, '.')+1));
				$linkFiletypeArray['stdWrap.']['addParams.']['title'] = strtoupper(substr($url, strrpos($url, '.')+1));
				$linkFiletypeImg = $this->cObj->IMAGE($linkFiletypeArray);
			}
		}

		return $content['TAG'].$linkTargetImg.$linkFiletypeImg;
	}

	/**
	 * Prepare the array with the filetype-image-mapping
	 *
	 * @param	string		The comma separated list of enabled extension (from TS)
	 * @param	string		The comma separated list of search paths for the filetype icons (from TS)
	 * @return	void
	 */
	function prepare_fileicons($extensions,$filepaths) {
		if ($filepaths)	{
			$pathArr = t3lib_div::trimExplode(',',$filepaths,1);
			while(list(,$p)=each($pathArr))	{
				if ($p == 'EXT') {
					$dirArr[] = t3lib_extMgm::siteRelPath('rs_linklayout').'res/';
				} else {
					$dirArr[] = $p;
				}
			}
		} else {
			$dirArr[] = t3lib_extMgm::siteRelPath('rs_linklayout').'res/';
		}

		$this->fileicons = array();
		$extArr = t3lib_div::trimExplode(',',$extensions,1);

		foreach($extArr as $valueExt) {
			reset($dirArr);
			$found=False;
			while(list(,$valueDir)=each($dirArr))	{
				if (is_file($valueDir.$valueExt.'.gif')){
					$this->fileicons[$valueExt] = $valueDir.$valueExt.'.gif';
					$found = True;
					break;
				}
			}
			if ($found==False) {
				reset($dirArr);
				while(list(,$valueDir)=each($dirArr))	{
					if (is_file($valueDir.'default'.'.gif')){
						$this->fileicons[$valueExt] = $valueDir.'default'.'.gif';
						break;
					}
				}
			}
		}
	}

	/**
	 * Extends the link params and gives it back (NOT used yet)
	 *
	 * @param	string		The original link params
	 * @param	string		The extension link params
	 * @param	string		The delimiter
	 * @return	string		The link as HTML code
	 */
	function extend_link_params($original, $extension, $delimiter) {
		$originals_temp = array();
		$originals = array();
		$extensions_temp = array();
		$extensions = array();
		$extended_temp = array();
		$extended = array();

		$original = substr($original, strpos($original, '<a ')+3, -1);
		$original = trim($original, ' "');
		$originals_temp = explode('" ', $original);
		for ($i = 0; $i < count($originals_temp); $i++) {
			$originals[substr($originals_temp[$i], 0, strpos($originals_temp[$i], '="'))] = substr($originals_temp[$i], strpos($originals_temp[$i], '="')+2);
		}

		$extension = trim($extension);
		$extensions_temp = explode(' ', $extension);
		for ($i = 0; $i < count($extensions_temp); $i++) {
			$extensions[substr($extensions_temp[$i], 0, strpos($extensions_temp[$i], '=')).'_ex'] = substr($extensions_temp[$i], strpos($extensions_temp[$i], '=')+1);
		}

		$extended_temp = array_merge($originals, $extensions);
		ksort($extended_temp);
		for ($i = 0; $i < count($extended_temp); $i++) {
			$key = key($extended_temp);
			if ($extended_temp[$key.'_ex'] <> '') {
				$extended[$key] = $extended_temp[$key].$delimiter.$extended_temp[$key.'_ex'];
				next($extended_temp);
				$i++;
			} else {
				$extended[$key] = $extended_temp[$key];
			}
			next($extended_temp);
		}

		return $this->recreate_link($extended);
	}

	/**
	 * Replaces the link params and gives it back
	 *
	 * @param	string		The original link params
	 * @param	string		The extension link params
	 * @return	string		The link as HTML code
	 */
	function replace_link_params($original, $extension) {
		$originals_temp = array();
		$originals = array();
		$extensions_temp = array();
		$extensions = array();
		$extended_temp = array();
		$extended = array();

		$original = substr($original, strpos($original, '<a ')+3, -1);
		$original = trim($original, ' "');
		$originals_temp = explode('" ', $original);
		for ($i = 0; $i < count($originals_temp); $i++) {
			$originals[substr($originals_temp[$i], 0, strpos($originals_temp[$i], '="'))] = substr($originals_temp[$i], strpos($originals_temp[$i], '="')+2);
		}

		// remove useless whitespaces (thanks "Daniel K.")
		$extension = trim($extension);
		$extension = str_replace('  ', ' ', $extension);
		$extension = str_replace('  ', ' ', $extension);

		if (strlen($extension)>0) {
			$extensions_temp = explode(' ', $extension);
		}

		// clean the array values (remove " or ') (thanks "Daniel K.")
		for ($i = 0; $i < count($extensions_temp); $i++) {
			$extensions_temp[$i] = str_replace('"', '', $extensions_temp[$i]);
			$extensions_temp[$i] = str_replace('\'', '', $extensions_temp[$i]);
		} //end

		for ($i = 0; $i < count($extensions_temp); $i++) {
			$extensions[substr($extensions_temp[$i], 0, strpos($extensions_temp[$i], '=')).'_ex'] = substr($extensions_temp[$i], strpos($extensions_temp[$i], '=')+1);
		}

		$extended_temp = array_merge($originals, $extensions);
		ksort($extended_temp);
		for ($i = 0; $i < count($extended_temp); $i++) {
			$key = key($extended_temp);
			if ($extended_temp[$key.'_ex'] <> '') {
				$extended[$key] = $extended_temp[$key.'_ex'];
				next($extended_temp);
				$i++;
			} else {
				// this is the new case (thanks "Daniel K.")
				// if key is from extension list and has no occurrance on original list
				// put it to extended array without suffix "_ex"
				if (strpos($key, '_ex') > 0 ) {
					$extended[substr($key, 0, strpos($key, '_ex'))] = $extended_temp[$key];
				}
				// key from original list that has no occurrance on extension list
				// put it to extended array as-is
				else
				{
					$extended[$key] = $extended_temp[$key];
				}
			}
			next($extended_temp);
		}

		return $this->recreate_link($extended);
	}

	/**
	 * Rebuilds the link with all given parameters
	 *
	 * @param	array		Array with all link params
	 * @return	string		The link as HTML code
	 */
	function recreate_link($params) {
		$link = '<a ';
		for ($i = 0; $i < count($params); $i++) {
			$link .= key($params).'="'.$params[key($params)].'" ';
			next($params);
		}
		$link .= '>';

		return $link;
	}
}
?>
