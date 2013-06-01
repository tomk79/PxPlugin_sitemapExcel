<?php

/**
 * PX Plugin "sitemapExcel"
 */
class pxplugin_sitemapExcel_daos_export{

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
	 * 現在のサイトマップをxlsxに出力する。
	 */
	public function export_sitemap2xlsx( $path_output ){

		$phpExcelHelper = $this->factory_PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		$objPHPExcel = $phpExcelHelper->create();

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		// フォント
		$objSheet->getDefaultStyle()->getFont()->setName('メイリオ');

		// フォントサイズ
		$objSheet->getDefaultStyle()->getFont()->setSize(12);

		// 背景色指定(準備)
		$objSheet->getDefaultStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);

		// ウィンドウ枠を固定
		$objSheet->freezePane('B4');

		// 罫線の一括指定
		$cell_style_boarder = array(
		  'borders' => array(
		    'top'     => array('style' => PHPExcel_Style_Border::BORDER_THIN),
		    'bottom'  => array('style' => PHPExcel_Style_Border::BORDER_THIN),
		    'left'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
		    'right'   => array('style' => PHPExcel_Style_Border::BORDER_THIN)
		  )
		);

		// シートタイトルセル
		$sheetTitle = '「'.$this->px->get_conf('project.name').'」 サイトマップ';
		$objSheet->setTitle($sheetTitle);
		$objSheet->getCell('A1')->setValue($sheetTitle);
		$objSheet->getStyle('A1')->getFont()->setSize(24);

		$objSheet->getCell('A2')->setValue('Exported: '.date('Y-m-d H:i:s'));

		// 定義行
		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$col = 'A';
		foreach( $sitemap_definition as $def_row ){
			$cellName = ($col++).'3';
			$objSheet->getCell($cellName)->setValue($def_row['name']);
			$objSheet->getStyle($cellName)->getFill()->getStartColor()->setRGB('dddddd');

			// 罫線の一括指定
			$objSheet->getStyle($cellName)->applyFromArray( $cell_style_boarder );
		}

		// データ行
		$row = 4;
		foreach( $this->px->site()->get_sitemap() as $page_info ){
			$col = 'A';
			foreach( $sitemap_definition as $def_row ){
				$cellName = ($col++).$row;
				$objSheet->getCell($cellName)->setValue($page_info[$def_row['key']]);

				// 罫線の一括指定
				$objSheet->getStyle($cellName)->applyFromArray( $cell_style_boarder );
			}
			$row ++;
		}

		$phpExcelHelper->save($objPHPExcel, $path_output, 'Excel2007');

		clearstatcache();
		return is_file($path_output);
	}

}

?>
