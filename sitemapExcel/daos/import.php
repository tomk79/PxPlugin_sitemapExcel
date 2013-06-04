<?php

/**
 * PX Plugin "sitemapExcel"
 */
class pxplugin_sitemapExcel_daos_import{

	private $command;
	private $px;


	/**
	 * コンストラクタ
	 * @param $command = PXコマンド配列
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct( $command, $px ){
		$this->command = $command;
		$this->px = $px;
	}


	/**
	 * PHPExcelHelper を生成する
	 */
	private function factory_PHPExcelHelper(){
		$tmp_class_name = $this->px->load_px_plugin_class('/'.$this->command[1].'/helper/PHPExcelHelper.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "PHPExcelHelper.php".', __FILE__, __LINE__);
			return false;
		}
		$phpExcelHelper = new $tmp_class_name($this->px);
		return $phpExcelHelper;
	}

	/**
	 * xlsxからサイトマップCSVを出力する。
	 */
	public function import_xlsx2sitemap(){

		$phpExcelHelper = $this->factory_PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		$objPHPExcel = $phpExcelHelper->create();

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		/*
		[UTODO] ここに、xlsxを取り込む機能を実装する。
		*/

		clearstatcache();
		return true;
	}

}

?>
