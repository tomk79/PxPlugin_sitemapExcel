<?php

/**
 * PX Plugin "sitemapExcel"
 */
class pxplugin_sitemapExcel_helper_PHPExcelHelper{

	private $px;

	/**
	 * コンストラクタ
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		set_include_path(get_include_path().PATH_SEPARATOR.$px->get_conf('paths.px_dir').'libs/PHPExcel/');
		include_once( 'PHPExcel.php' );
	}

	/**
	 * 新規ファイルを作成
	 */
	public function create(){
		$objPHPExcel = new PHPExcel();
		return $objPHPExcel;
	}

	/**
	 * 既存のファイルを開く
	 */
	public function load( $path ){
		if(!strlen($path)){ return false; }
		if(!$this->px->dbh()->is_file($path)){ return false; }
		if(!$this->px->dbh()->is_readable($path)){ return false; }

		$objPHPExcel = PHPExcel_IOFactory::load($path);
		return $objPHPExcel;
	}

	/**
	 * 保存する
	 */
	public function save( $objPHPExcel, $path, $type = 'Excel2007' ){
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $type);
		return $objWriter->save($path);
	}

}

?>
