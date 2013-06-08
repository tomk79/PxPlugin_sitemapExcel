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
	 * xlsxの構造定義設定を解析する
	 */
	private function parse_definition( $objPHPExcel ){
		$rtn = array();
		$objPHPExcel->setActiveSheetIndex(1);
		$objSheet = $objPHPExcel->getActiveSheet();

		$i = 1;
		while(1){
			$key = $objSheet->getCell('A'.$i)->getValue();
			$val = $objSheet->getCell('B'.$i)->getValue();

			if( !strlen($key) ){
				break;
			}

			switch($key){
				case 'col_define':
					$rtn['col_define'] = array();
					while(1){
						$def_key = $objSheet->getCell('B'.$i)->getValue();
						$def_col = $objSheet->getCell('C'.$i)->getValue();
						$def_name = $objSheet->getCell('D'.$i)->getValue();
						if(!strlen($def_key) || !strlen($def_col) || !strlen($def_name)){
							break;
						}
						$rtn['col_define'][$def_key] = array(
							'key'=>$def_key,
							'col'=>$def_col,
							'name'=>$def_name,
						);
						$i ++;
					}
					break;
				default:
					$rtn[$key] = $val;
					$i ++;
					break;
			}
		}

		return $rtn;
	}

	/**
	 * xlsxからサイトマップCSVを出力する。
	 */
	public function import_xlsx2sitemap( $path_xlsx, $path_csv ){

		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$phpExcelHelper = $this->factory_PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		$objPHPExcel = $phpExcelHelper->load($path_xlsx);

		$table_definition = $this->parse_definition($objPHPExcel);//xlsxの構造定義を読み解く
		$col_title = array();
		foreach($table_definition['col_define'] as $col_define){
			if( isset( $col_title['start'] ) ){
				$col_title['end'] = $col_define['col'];
				break;
			}
			if( $col_define['key'] == 'title' ){
				$col_title['start'] = $col_define['col'];
			}
		}

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		$sitemap = array();

		$auto_id_num = 1;
		$breadcrumb = array();
		$i = $table_definition['row_data_start'];
		while(1){
			$page_info = array();
			$tmp_page_info = array();
			foreach($sitemap_definition as $key=>$row){
				$tmp_col_name = $table_definition['col_define'][$row['key']]['col'];
				if(strlen($tmp_col_name)){
					$tmp_page_info[$row['key']] = $objSheet->getCell($tmp_col_name.$i)->getValue();
				}else{
					$tmp_page_info[$row['key']] = '';
				}
			}

			// 省略されたIDを自動的に付与
			if(!strlen($tmp_page_info['id'])){
				// UTODO: トップページは空白でなければならない。
				$tmp_page_info['id'] = 'sitemapExcel_auto_id_'.($auto_id_num ++);
			}

			// タイトルだけ特別
			$col_title_col = $col_title['start'];
			$tmp_page_info['title'] = '';
			$logical_path_depth = 0;
			while($col_title_col < $col_title['end']){
				$tmp_page_info['title'] .= trim( $objSheet->getCell($col_title_col.$i)->getValue() );
				if(strlen($tmp_page_info['title'])){
					break;
				}
				$col_title_col ++;
				$logical_path_depth ++;
			}

			// パンくずも特別
			if(!strlen($tmp_page_info['id'])){
				$tmp_page_info['logical_path'] = '';
			}elseif($logical_path_depth <= 1){
				$tmp_page_info['logical_path'] = '';
			}else{
				$tmp_page_info['logical_path'] = $logical_path_depth;
			}

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
test::var_dump($sitemap);exit;

		$this->px->dbh()->mkdir(dirname($path_csv));
		$this->px->dbh()->file_overwrite($path_csv, $this->px->dbh()->mk_csv_utf8($sitemap) );//[UTODO]仮実装

		clearstatcache();
		return true;
	}

}

?>
