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
		$path_toppage = '/';
		if( strlen($this->px->get_conf('project.path_top')) ){
			$path_toppage = $this->px->get_conf('project.path_top');
		}

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
		$last_breadcrumb = array();
		$last_page_id = null;
		$logical_path_last_depth = 0;
		$xlsx_row = $table_definition['row_data_start'];
		while(1){
			$page_info = array();
			$tmp_page_info = array();
			foreach($sitemap_definition as $key=>$row){
				$tmp_col_name = $table_definition['col_define'][$row['key']]['col'];
				if(strlen($tmp_col_name)){
					$tmp_page_info[$row['key']] = $objSheet->getCell($tmp_col_name.$xlsx_row)->getValue();
				}else{
					$tmp_page_info[$row['key']] = '';
				}
			}

			// 省略されたIDを自動的に付与
			if(!strlen($tmp_page_info['id'])){
				// トップページは空白でなければならない。
				if( $path_toppage != $tmp_page_info['path'] ){
					$tmp_page_info['id'] = 'sitemapExcel_auto_id_'.($auto_id_num ++);
				}
			}

			// タイトルだけ特別
			$col_title_col = $col_title['start'];
			$tmp_page_info['title'] = '';
			$logical_path_depth = 0;
			while($col_title_col < $col_title['end']){
				$tmp_page_info['title'] .= trim( $objSheet->getCell($col_title_col.$xlsx_row)->getValue() );
				if(strlen($tmp_page_info['title'])){
					break;
				}
				$col_title_col ++;
				$logical_path_depth ++;
			}

			// パンくずも特別
			$tmp_breadcrumb = $last_breadcrumb;
			if( $logical_path_last_depth === $logical_path_depth ){
				// 前回と深さが変わっていなかったら
			}elseif( $logical_path_last_depth < $logical_path_depth ){
				// 前回の深さより深くなっていたら
				$tmp_breadcrumb = $last_breadcrumb;
				array_push($tmp_breadcrumb, $last_page_id );
			}elseif( $logical_path_last_depth > $logical_path_depth ){
				// 前回の深さより浅くなっていたら
				$tmp_breadcrumb = array();
				for($i = 0; $i < $logical_path_depth; $i ++){
					if(is_null($last_breadcrumb[$i])){break;}
					$tmp_breadcrumb[$i] = $last_breadcrumb[$i];
				}
			}
			$tmp_page_info['logical_path'] = implode('>', $tmp_breadcrumb);
			$tmp_page_info['logical_path'] = preg_replace('/^\>/s', '', $tmp_page_info['logical_path']);


			// 今回のパンくずとパンくずの深さを記録
			$logical_path_last_depth = $logical_path_depth;
			$last_breadcrumb = $tmp_breadcrumb;
			$last_page_id = $tmp_page_info['id'];

			if(!strlen( $tmp_page_info['path'] )){
				// pathが空白なら終わったものと思う。
				break;
			}

			$page_info = array();
			foreach($sitemap_definition as $row){
				$page_info[$row['key']] = $tmp_page_info[$row['key']];
			}

			array_push( $sitemap, $page_info );

			$xlsx_row ++;
			continue;
		}

		$this->px->dbh()->mkdir(dirname($path_csv));
		$this->px->dbh()->file_overwrite($path_csv, $this->px->dbh()->mk_csv_utf8($sitemap) );

		clearstatcache();
		return true;
	}

}

?>