<?php
/**
 * Created by PhpStorm.
 * User: ivariable
 * Date: 01.02.2011
 * Time: 19:03:56
 * To change this template use File | Settings | File Templates.
 */
class BitrixGem_UserTypeFields_IBlockPack extends BaseBitrixGem{

	protected $aGemInfo = array(
		'GEM'			=> 'UserTypeFields_IBlockPack',
		'AUTHOR'		=> 'Sergey Leshchenko',
		'AUTHOR_LINK'	=> 'http://dev.1c-bitrix.ru/community/webdev/group/78/blog/2193/',
		'DATE'			=> '15.02.2011',
		'VERSION'		=> '0.0.3',
		'NAME' 			=> 'UserTypeFields_IBlockPack',
		'DESCRIPTION' 	=> "Пользовательские свойства главного модуля \"Связь с элементом инфоблока\" и \"Связь с элементом инфоблока в виде списка\"",
		'CHANGELOG'		=> 'Первый релиз в виде гема.',
		'REQUIREMENTS'	=> '',
		'REQUIRED_MODULES' => array('iblock'),
	);

	public function initGem(){
		require_once( 'sources/include/uf_iblock_element.php' );
		require_once( 'sources/include/uf_iblock_element_list.php' );
	}

	public function installGem(){
		CopyDirFiles( $this->getGemFolder().'/sources/templates/.default/', $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/.default/", true, true);
		return true;
	}

	public function unInstallGem(){
		// Грусть-печаль. DeleteDirFiles- тока для файлов
		DeleteDirFilesEx(
			"/bitrix/templates/.default/components/bitrix/system.field.edit/iblock_element/"
		);
		DeleteDirFilesEx(
			"/bitrix/templates/.default/components/bitrix/system.field.edit/iblock_element_list/"
		);
		DeleteDirFilesEx(
			"/bitrix/templates/.default/components/bitrix/system.field.view/iblock_element/"
		);
		DeleteDirFilesEx(
			"/bitrix/templates/.default/components/bitrix/system.field.view/iblock_element_list/"
		);
		return true;
	}

}
?>
