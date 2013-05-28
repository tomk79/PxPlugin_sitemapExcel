<?php
$this->load_px_class('/bases/pxcommand.php');

/**
 * PX Plugin "sitemapExcel"
 */
class pxplugin_sitemapExcel_register_pxcommand extends px_bases_pxcommand{

	private $command;

	/**
	 * コンストラクタ
	 * @param $command = PXコマンド配列
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct( $command , $px ){
		parent::__construct( $command , $px );
		$this->command = $this->get_command();
		$this->start();
	}

	/**
	 * 処理の開始
	 */
	private function start(){
		if( $this->command[2] == 'upload' ){
			return $this->page_upload();
		}elseif( $this->command[2] == 'download' ){
			return $this->page_download();
		}
		return $this->page_homepage();
	}

	/**
	 * ホームページを表示する。
	 */
	private function page_homepage(){

		$src = '';
		$src .= '<p>エクセル形式(*.xlsx)で作成したサイトマップをCSVに変換するプラグインです。</p>'."\n";
		$src .= '<div class="cols unit">'."\n";
		$src .= '	<div class="cols-col cols-1of2"><div class="cols-pad">'."\n";
		$src .= '		<form action="?" method="get" class="inline">'."\n";
		$src .= '			<p class="center"><input type="submit" value="アップロード" /></p>'."\n";
		$src .= '			<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'upload'))).'" /></div>'."\n";
		$src .= '		</form>'."\n";
		$src .= '	</div></div>'."\n";
		$src .= '	<div class="cols-col cols-1of2 cols-last"><div class="cols-pad">'."\n";
		$src .= '		<form action="?" method="get" class="inline">'."\n";
		$src .= '			<p class="center"><input type="submit" value="ダウンロード" /></p>'."\n";
		$src .= '			<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'download'))).'" /></div>'."\n";
		$src .= '		</form>'."\n";
		$src .= '	</div></div>'."\n";
		$src .= '</div><!-- / .cols -->'."\n";
		$src .= ''."\n";

		// $this->set_title( 'sitemapExcel' );//タイトルをセットする

		print $this->html_template($src);
		exit;
	}

	/**
	 * サイトマップxlsxをアップロードする。
	 */
	private function page_upload(){
		$src = '';
		$src .= '<p>アップロード機能は開発準備中です。</p>'."\n";
		print $this->html_template($src);
		exit;
	}



	/**
	 * 現在のサイトマップをダウンロードする。
	 */
	private function page_download(){

		$path_work_dir = $this->px->get_conf('paths.px_dir').'_sys/ramdata/plugins/sitemapExcel/';
		if( !$this->px->dbh()->mkdir_all($path_work_dir) ){
			$this->px->error()->error_log('FAILED to create working directory "'.$path_work_dir.'".', __FILE__, __LINE__);
			print '[ERROR] FAILED to create working directory "'.$path_work_dir.'".';
			exit;
		}

		$tmp_class_name = $this->px->load_px_plugin_class($this->command[1].'/helper/PHPExcelHelper.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "PHPExcelHelper.php".', __FILE__, __LINE__);
			print '[ERROR] FAILED to load "PHPExcelHelper.php".';
			exit;
		}

		$phpExcelHelper = new $tmp_class_name($this->px);
		$objPHPExcel = $phpExcelHelper->create();

		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();


		$objSheet->getCell('A1')->setValue('ただいま開発中です。');


		$phpExcelHelper->save($objPHPExcel, $path_work_dir.'tmp.xlsx', 'Excel2007');

		$this->px->flush_file($path_work_dir.'tmp.xlsx', array('filename'=>'PxFW_'.$this->px->get_conf('project.id').'sitemap_'.date('Ymd_Hi').'.xlsx', 'delete'=>true));
		print 'test';
		exit;
	}

}

?>
