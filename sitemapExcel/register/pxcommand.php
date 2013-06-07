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
		if( $this->command[2] == 'import' ){
			return $this->page_import();
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
		$src .= '			<p class="center"><input type="submit" value="インポート" /></p>'."\n";
		$src .= '			<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
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
	 * サイトマップxlsxをインポートする。
	 */
	private function page_import(){
		$error = $this->check_import_check();
		if( $this->px->req()->get_param('mode') == 'execute' && !count($error) ){
			return $this->execute_import_execute();
		}elseif( $this->px->req()->get_param('mode') == 'thanks' ){
			return $this->page_import_thanks();
		}elseif( !strlen($this->px->req()->get_param('mode')) ){
			$error = array();
		}
		return $this->page_import_input($error);
	}
	private function page_import_input($error){
		$src = '';
		$src .= '<p>インポート機能は開発準備中です。</p>'."\n";
		$src .= '<form action="?" method="get" class="inline">'."\n";
		$src .= '	<p class="center"><input type="submit" value="インポートを実行する" /></p>'."\n";
		$src .= '	<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
		$src .= '	<div><input type="hidden" name="mode" value="execute" /></div>'."\n";
		$src .= '</form>'."\n";
		print $this->html_template($src);
		exit;
	}
	private function check_import_check(){
		$rtn = array();
		return $rtn;
	}
	private function execute_import_execute(){

		$tmp_class_name = $this->px->load_px_plugin_class('/'.$this->command[1].'/daos/import.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "daos/import.php".', __FILE__, __LINE__);
			print '[ERROR] FAILED to load "daos/import.php".';
			exit;
		}
		$obj_import = new $tmp_class_name($this->command, $this->px);

		$path_xlsx = $this->px->get_conf('paths.px_dir').'data/sitemapExcel_sample.xlsx';//[UTODO]仮実装

		if( !$obj_import->import_xlsx2sitemap( $path_xlsx ) ){
			$this->px->error()->error_log('FAILED to import xlsx.', __FILE__, __LINE__);
			print '[ERROR] FAILED to import xlsx.';
			exit;
		}


		return $this->px->redirect( $this->href().'&mode=thanks' );
	}
	private function page_import_thanks($error){
		$src = '';
		$src .= '<p>インポートしました。</p>'."\n";
		$src .= '<form action="?" method="get" class="inline">'."\n";
		$src .= '	<p class="center"><input type="submit" value="もう一度、インポートを実行する" /></p>'."\n";
		$src .= '	<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
		$src .= '	<div><input type="hidden" name="mode" value="" /></div>'."\n";
		$src .= '</form>'."\n";
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

		$tmp_class_name = $this->px->load_px_plugin_class('/'.$this->command[1].'/daos/export.php');
		if(!$tmp_class_name){
			$this->px->error()->error_log('FAILED to load "daos/export.php".', __FILE__, __LINE__);
			print '[ERROR] FAILED to load "daos/export.php".';
			exit;
		}
		$obj_export = new $tmp_class_name($this->command, $this->px);

		if( !$obj_export->export_sitemap2xlsx( $path_work_dir.'tmp.xlsx' ) ){
			$this->px->error()->error_log('FAILED to export xlsx.', __FILE__, __LINE__);
			print '[ERROR] FAILED to export xlsx.';
			exit;
		}

		$this->px->flush_file($path_work_dir.'tmp.xlsx', array('filename'=>'PxFW_'.$this->px->get_conf('project.id').'sitemap_'.date('Ymd_Hi').'.xlsx', 'delete'=>true));
		exit;
	}


	/**
	 * コンテンツ内へのリンク先を調整する。
	 */
	private function href( $linkto = null ){
		if(is_null($linkto)){
			return '?PX='.implode('.',$this->command);
		}
		if($linkto == ':'){
			return '?PX=plugins.sitemapExcel';
		}
		$rtn = preg_replace('/^\:/','?PX=plugins.sitemapExcel.',$linkto);

		$rtn = $this->px->theme()->href( $rtn );
		return $rtn;
	}

	/**
	 * コンテンツ内へのリンクを生成する。
	 */
	private function mk_link( $linkto , $options = array() ){
		if( !strlen($options['label']) ){
			if( $this->local_sitemap[$linkto] ){
				$options['label'] = $this->local_sitemap[$linkto]['title'];
			}
		}
		$rtn = $this->href($linkto);

		$rtn = $this->px->theme()->mk_link( $rtn , $options );
		return $rtn;
	}

}

?>
