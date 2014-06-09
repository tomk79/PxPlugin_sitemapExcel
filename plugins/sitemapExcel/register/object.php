<?php

/**
 * PX Plugin "sitemapExcel"
 * @author Tomoya Koyanagi.
 */
class pxplugin_sitemapExcel_register_object{
	private $px;

	/**
	 * コンストラクタ
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
	}

	/**
	 * PHPExcelHelper を生成する
	 */
	public function factory_PHPExcelHelper(){
		$tmp_class_name = $this->px->load_px_plugin_class('/sitemapExcel/helper/PHPExcelHelper.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "PHPExcelHelper.php".', __FILE__, __LINE__);
			return false;
		}
		$phpExcelHelper = new $tmp_class_name($this->px);
		return $phpExcelHelper;
	}// factory_PHPExcelHelper()

	/**
	 * $obj_import を生成する
	 */
	public function factory_import(){
		$tmp_class_name = $this->px->load_px_plugin_class('/sitemapExcel/daos/import.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "daos/import.php".', __FILE__, __LINE__);
			print '[ERROR] FAILED to load "daos/import.php".';
			exit;
		}
		$obj_import = new $tmp_class_name( $this->px );
		return $obj_import;
	}// factory_import()

	/**
	 * データディレクトリのパスを取得
	 */
	public function get_ramdata_dir(){
		// $path_data_dir = $this->px->get_conf('paths.px_dir').'_sys/ramdata/plugins/sitemapExcel/';
		$path_data_dir = $this->px->realpath_plugin_ramdata_dir('sitemapExcel');
		return $path_data_dir;
	}

	/**
	 * インポートデータの格納ディレクトリを取得
	 */
	public function get_path_import_data_dir(){
		$path = $this->get_ramdata_dir();
		$rtn = $path.'import_data/';
		$this->px->dbh()->mkdir($rtn);
		return $rtn;
	}

	/**
	 * サイトマップディレクトリのパスを取得
	 */
	public function get_sitemap_dir(){
		$path_sitemap_dir = $this->px->get_conf('paths.px_dir').'sitemaps/';
		return $path_sitemap_dir;
	}

	/**
	 * インポートデータディレクトリを空にする
	 */
	public function empty_import_data_dir(){
		$path = $this->get_path_import_data_dir();
		if( !$path ){return false;}
		if( !is_dir($path) ){return false;}
		$result = $this->px->dbh()->rm( $path );
		clearstatcache();
		$path = $this->get_path_import_data_dir();
		return $result;
	}
}

?>