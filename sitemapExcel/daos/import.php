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
	public function import_xlsx2sitemap( $path_xlsx ){

		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$phpExcelHelper = $this->factory_PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		$objPHPExcel = $phpExcelHelper->load($path_xlsx);

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		$sitemap = array();

		$i = 5;
		while(1){
			$page_info = array();
			$tmp_page_info = array();
			$tmp_page_info['id'] = $objSheet->getCell('A'.$i)->getValue();
			$tmp_page_info['title'] = $objSheet->getCell('B'.$i)->getValue().$objSheet->getCell('C'.$i)->getValue().$objSheet->getCell('D'.$i)->getValue().$objSheet->getCell('F'.$i)->getValue().$objSheet->getCell('G'.$i)->getValue();
			$tmp_page_info['title_h1'] = $objSheet->getCell('H'.$i)->getValue();
			$tmp_page_info['title_label'] = $objSheet->getCell('I'.$i)->getValue();
			$tmp_page_info['title_breadcrumb'] = $objSheet->getCell('J'.$i)->getValue();
			$tmp_page_info['path'] = $objSheet->getCell('K'.$i)->getValue();
			$tmp_page_info['content'] = $objSheet->getCell('L'.$i)->getValue();
			$tmp_page_info['list_flg'] = $objSheet->getCell('M'.$i)->getValue();
			$tmp_page_info['auth_level'] = $objSheet->getCell('N'.$i)->getValue();
			$tmp_page_info['layout'] = $objSheet->getCell('O'.$i)->getValue();
			$tmp_page_info['extension'] = $objSheet->getCell('P'.$i)->getValue();
			$tmp_page_info['orderby'] = $objSheet->getCell('Q'.$i)->getValue();
			$tmp_page_info['keywords'] = $objSheet->getCell('R'.$i)->getValue();
			$tmp_page_info['description'] = $objSheet->getCell('S'.$i)->getValue();
			$tmp_page_info['category_top_flg'] = $objSheet->getCell('T'.$i)->getValue();

			if(!strlen( $tmp_page_info['path'] )){
				// pathが空白なら終わったものと思う。
				break;
			}


			$page_info = array();
			foreach($sitemap_definition as $row){
				$page_info[$row['key']] = $tmp_page_info[$row['key']];
			}

			$sitemap[$page_info['path']] = $page_info;

			$i ++;
			continue;
		}

		$path_csv = $this->px->get_conf('paths.px_dir').'_sys/ramdata/plugins/sitemapExcel/sitemapExcel_sample.csv';//[UTODO]仮実装
		$this->px->dbh()->mkdir(dirname($path_csv));
		$this->px->dbh()->file_overwrite($path_csv, $this->px->dbh()->mk_csv_utf8($sitemap) );//[UTODO]仮実装

		clearstatcache();
		return true;
	}

}

?>
