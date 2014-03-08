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
	}// factory_PHPExcelHelper()

	/**
	 * xlsxからサイトマップCSVを出力する。
	 */
	public function import_xlsx2sitemap( $path_xlsx, $path_csv ){
		$path_toppage = '/';
		if( strlen($this->px->get_conf('project.path_top')) ){
			$path_toppage = $this->px->get_conf('project.path_top');
		}
		$path_toppage = $this->regulize_path( $path_toppage );

		$sitemap_definition = $this->px->site()->get_sitemap_definition();
		$phpExcelHelper = $this->factory_PHPExcelHelper();
		if( !$phpExcelHelper ){
			return false;
		}
		set_time_limit(0);
		$objPHPExcel = $phpExcelHelper->load($path_xlsx);

		$table_definition = $this->parse_definition($objPHPExcel, 0);//xlsxの構造定義を読み解く
		$col_title = array();
		foreach($table_definition['col_define'] as $tmp_col_define){
			if( isset( $col_title['start'] ) ){
				$col_title['end'] = $tmp_col_define['col'];
				break;
			}
			if( $tmp_col_define['key'] == 'title' ){
				$col_title['start'] = $tmp_col_define['col'];
			}
		}
		unset($tmp_col_define);

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();

		// xlsxにあってサイトマップ定義にないカスタムカラムを定義に反映
		$xls_custom_column_definition = $table_definition['col_define'];
		$tmp_last_elm_info = array();
		foreach( $sitemap_definition as $tmp_row ){
			unset($xls_custom_column_definition[$tmp_row['key']]);
			$tmp_last_elm_info = $tmp_row;
		}
		foreach( $xls_custom_column_definition as $tmp_key=>$tmp_row ){
			$tmp_last_elm_info['num']  ++;
			$tmp_last_elm_info['col']  ++;
			$tmp_last_elm_info['key']  = $tmp_row['key'];
			$tmp_last_elm_info['name'] = $tmp_row['key'];
			$sitemap_definition[$tmp_last_elm_info['key']] = $tmp_last_elm_info;
		}



		$sitemap = array();

		$page_info = array();
		foreach($sitemap_definition as $row){
			$page_info[$row['key']] = '* '.$row['key'];
		}
		array_push( $sitemap, $page_info );

		$last_breadcrumb = array();
		$last_page_id = null;
		$logical_path_last_depth = 0;
		$xlsx_row = $table_definition['row_data_start'];
		$xlsx_row --;
		while(1){
			set_time_limit(30);
			$xlsx_row ++;

			if( $objSheet->getCell('A'.$xlsx_row)->getCalculatedValue() == 'EndOfData' ){
				// A列が 'EndOfData' だったら、終了。
				break;
			}

			$page_info = array();
			$tmp_page_info = array();
			foreach($sitemap_definition as $key=>$row){
				$tmp_col_name = $table_definition['col_define'][$row['key']]['col'];
				if(strlen($tmp_col_name)){
					$tmp_page_info[$row['key']] = $objSheet->getCell($tmp_col_name.$xlsx_row)->getCalculatedValue();
				}else{
					$tmp_page_info[$row['key']] = '';
				}
			}
			if( $tmp_page_info['**delete_flg'] ){
				// 削除フラグ
				continue;
			}
			if(!strlen( $tmp_page_info['path'] )){
				// pathが空白なら終わったものと思う。
				break;
			}
			if( !array_key_exists('list_flg', $xls_custom_column_definition) ){
				// エクセルの定義にlist_flg列がなかったら、
				// 全ページにlist_flg=1をセット。
				$tmp_page_info['list_flg'] = 1;
			}

			// 読み込んだパスを正規化
			$tmp_page_info['path'] = $this->regulize_path( $tmp_page_info['path'] );

			// 省略されたIDを自動的に付与
			if(!strlen($tmp_page_info['id'])){
				// トップページは空白でなければならない。
				if( $path_toppage != $tmp_page_info['path'] ){
					$tmp_page_info['id'] = $this->generate_auto_page_id();
				}
			}

			// トップページは空白でなければならない。
			if( $path_toppage == $tmp_page_info['path'] ){
				$tmp_page_info['id'] = '';
			}

			// タイトルだけ特別
			$col_title_col = $col_title['start'];
			$tmp_page_info['title'] = '';
			$logical_path_depth = 0;
			$alias_title_list = array();
			while($col_title_col < $col_title['end']){
				$tmp_page_info['title'] .= trim( $objSheet->getCell($col_title_col.$xlsx_row)->getCalculatedValue() );
				if(strlen($tmp_page_info['title'])){
					$col_title_col ++;
					while( strlen( $tmp_alias_title = trim( $objSheet->getCell(($col_title_col).$xlsx_row)->getCalculatedValue() ) ) ){
						array_push( $alias_title_list, $tmp_alias_title );
						$col_title_col ++;
					}
					break;
				}
				$col_title_col ++;
				$logical_path_depth ++;
			}
			unset($col_title_col);
			unset($tmp_alias_title);

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

			$page_info = array();
			foreach($sitemap_definition as $row){
				$page_info[$row['key']] = $tmp_page_info[$row['key']];
			}

			// サイトマップにページを追加する
			if( count($alias_title_list) ){
				// エイリアスが省略されている場合
				$page_info_base = $page_info;
				$page_info['path'] = 'alias:'.$page_info['path'];
				array_push( $sitemap, $page_info );
				$tmp_last_page_id = $page_info['id'];
				foreach( $alias_title_list as $key=>$row ){
					$page_info = $page_info_base;
					if( count($alias_title_list) > $key+1 ){
						// 最後の1件以外なら
						$page_info['path'] = 'alias:'.$page_info['path'];
					}
					array_push($tmp_breadcrumb, $tmp_last_page_id);
					$page_info['logical_path'] = implode('>', $tmp_breadcrumb);
					$page_info['logical_path'] = preg_replace('/^\>/s', '', $page_info['logical_path']);
					$page_info['id'] = $this->generate_auto_page_id();
					$page_info['title'] = $row;

					array_push( $sitemap, $page_info );

					$tmp_last_page_id = $page_info['id'];
					$logical_path_last_depth ++;
					$last_breadcrumb = $tmp_breadcrumb;
					$last_page_id = $tmp_last_page_id;
				}

				unset($page_info_base);
				unset($tmp_last_page_id);
				continue;
			}else{
				// 通常のページの場合
				array_push( $sitemap, $page_info );
				continue;
			}
			continue;
		}

		$this->px->dbh()->mkdir(dirname($path_csv));
		$this->px->dbh()->file_overwrite($path_csv, $this->px->dbh()->mk_csv_utf8($sitemap) );

		set_time_limit(30);

		clearstatcache();
		return true;
	}// import_xlsx2sitemap()

	/**
	 * ページIDを自動生成する
	 */
	private function generate_auto_page_id(){
		static $auto_id_num = 0;
		$auto_id_num ++;
		$rtn = 'sitemapExcel_auto_id_'.intval($auto_id_num);
		return $rtn;
	}//generate_auto_page_id()

	/**
	 * パス文字列の正規化
	 */
	private function regulize_path($path){
		$parsed_url = parse_url($path);
		$path_path = preg_replace( '/(?:\?|\#).*$/', '', $path);
		$path_path = preg_replace( '/\/$/s', '/'.$this->px->get_directory_index_primary(), $path_path);
		$path = $path_path.(strlen($parsed_url['query'])?'?'.$parsed_url['query']:'').(strlen($parsed_url['fragment'])?'#'.$parsed_url['fragment']:'');
		return $path;
	}//regulize_path()

	/**
	 * xlsxの構造定義設定を解析する
	 */
	private function parse_definition( $objPHPExcel, $sheetIndex = 0 ){
		$rtn = array();
		$objPHPExcel->setActiveSheetIndex($sheetIndex);
		$objSheet = $objPHPExcel->getActiveSheet();

		parse_str( $objSheet->getCell('A1')->getCalculatedValue(), $rtn );
		$rtn['row_definition'] = intval($rtn['row_definition']);
		$rtn['row_data_start'] = intval($rtn['row_data_start']);
		$rtn['skip_empty_col'] = intval($rtn['skip_empty_col']);

		$rtn['col_define'] = array();

		$mergedCells = $objSheet->getMergeCells();
		$mergeInfo = array();
		foreach( $mergedCells as $mergeRow ){
			if( preg_match( '/^([a-zA-Z]+)'.$rtn['row_definition'].'\:([a-zA-Z]+)'.$rtn['row_definition'].'$/', $mergeRow, $matched ) ){
				$mergeInfo[$matched[1]] = $matched[2];
			}
		}

		$col = 'A';
		$skip_count = 0;
		while(1){
			$def_key = $objSheet->getCell($col.$rtn['row_definition'])->getCalculatedValue();
			if(!strlen($def_key)){
				$skip_count ++;
				$col ++;
				if( $skip_count > $rtn['skip_empty_col'] ){
					break;
				}
				continue;
			}
			$skip_count = 0;

			$rtn['col_define'][$def_key] = array(
				'key'=>trim($def_key),
				'col'=>$col,
				// 'name'=>$def_name,
			);

			if( strlen($mergeInfo[$col]) ){
				$mergeStartCol = $mergeInfo[$col];
				while( strcmp($mergeStartCol, $col) ){
					$col ++;
				}
			}else{
				$col ++;
			}
		}


		return $rtn;
	}// parse_definition()

}

?>
