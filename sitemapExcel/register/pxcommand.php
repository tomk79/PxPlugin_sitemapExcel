<?php
$this->load_px_class('/bases/pxcommand.php');

/**
 * PX Plugin "sitemapExcel"
 */
class pxplugin_sitemapExcel_register_pxcommand extends px_bases_pxcommand{

	private $command;

	private $path_data_dir;

	/**
	 * コンストラクタ
	 * @param $command = PXコマンド配列
	 * @param $px = PxFWコアオブジェクト
	 */
	public function __construct( $command , $px ){
		parent::__construct( $command , $px );
		$this->command = $this->get_command();
		$this->path_data_dir = $this->px->get_conf('paths.px_dir').'_sys/ramdata/plugins/sitemapExcel/';
		$this->start();
	}

	/**
	 * 処理の開始
	 */
	private function start(){
		if( $this->command[2] == 'import' ){
			return $this->page_import();
		}elseif( $this->command[2] == 'export' ){
			return $this->page_export();
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
		$src .= '	<div class="cols-col cols-2of3"><div class="cols-pad">'."\n";
		$src .= '		<h2>インポート</h2>'."\n";
		$src .= '		<p>所定の形式の *.xlsx ファイルから、プロジェクトのサイトマップを更新します。</p>'."\n";
		$src .= '		<p>読み込める xlsx ファイルの構造定義は、エクスポート機能から取得できるファイルを参考にしてください。</p>'."\n";
		$src .= '		<form action="?" method="get" class="inline">'."\n";
		$src .= '			<p class="center"><input type="submit" value="インポートする" /></p>'."\n";
		$src .= '			<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
		$src .= '		</form>'."\n";
		$src .= '	</div></div>'."\n";
		$src .= '	<div class="cols-col cols-1of3 cols-last"><div class="cols-pad">'."\n";
		$src .= '		<h2>エクスポート</h2>'."\n";
		$src .= '		<p>プロジェクト「'.t::h($this->px->get_conf('project.name')).'」に現在登録されているサイトマップを、*.xlsx 形式で出力できます。</p>'."\n";
		$src .= '		<form action="?" method="get" class="inline">'."\n";
		$src .= '			<p class="center"><input type="submit" value="エクスポートする" /></p>'."\n";
		$src .= '			<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'export'))).'" /></div>'."\n";
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
		$this->set_title('インポートする');
		$error = $this->check_import_check();
		if( $this->px->req()->get_param('mode') == 'execute' && !count($error) ){
			return $this->execute_import_execute();
		}elseif( $this->px->req()->get_param('mode') == 'thanks' ){
			return $this->page_import_thanks();
		}elseif( !strlen($this->px->req()->get_param('mode')) ){
			$error = array();
			$this->px->req()->delete_uploadfile_all();// 一時ファイルを削除
			$this->px->req()->set_param('file_overwrite','1');
		}
		return $this->page_import_input($error);
	}
	private function page_import_input($error){
		$src = '';
		// $src .= '<p>インポート機能は開発準備中です。</p>'."\n";
		$src .= '<form action="?" method="post" class="inline" enctype="multipart/form-data">'."\n";

		$src .= '<table class="form_elements">'."\n";
		$src .= '	<thead>'."\n";
		$src .= '		<tr>'."\n";
		$src .= '			<th>入力項目名</th>'."\n";
		$src .= '			<th>入力フィールド</th>'."\n";
		$src .= '		</tr>'."\n";
		$src .= '	</thead>'."\n";
		$src .= '	<tbody>'."\n";
		$src .= '		<tr'.(strlen($error['file_xlsx'])?' class="form_elements-error"':'').'>'."\n";
		$src .= '			<th>サイトマップ(xlsx形式)</th>'."\n";
		$src .= '			<td>'."\n";
		if( strlen($error['file_xlsx']) ){
			$src .= '<ul class="form_elements-errors">'."\n";
			$src .= '	<li>'.t::h($error['file_xlsx']).'</li>'."\n";
			$src .= '</ul>'."\n";
		}
		$src .= '				<input type="file" name="file_xlsx" value="" />'."\n";
		$src .= '			</td>'."\n";
		$src .= '		</tr>'."\n";
		$src .= '		<tr'.(strlen($error['file_overwrite'])?' class="form_elements-error"':'').'>'."\n";
		$src .= '			<th>サイトマップCSVの上書き</th>'."\n";
		$src .= '			<td>'."\n";
		if( strlen($error['file_overwrite']) ){
			$src .= '<ul class="form_elements-errors">'."\n";
			$src .= '	<li>'.t::h($error['file_overwrite']).'</li>'."\n";
			$src .= '</ul>'."\n";
		}
		$src .= '				<ul class="form_elements-list">'."\n";
		$src .= '					<li><label><input type="radio" name="file_overwrite" value="1"'.($this->px->req()->get_param('file_overwrite')=='1'?' checked="checked"':'').' /> サイトマップCSVを直接上書きする (現在のサイトマップCSVは失われます)</label></li>'."\n";
		$src .= '					<li><label><input type="radio" name="file_overwrite" value="0"'.($this->px->req()->get_param('file_overwrite')=='0'?' checked="checked"':'').' /> 直接上書きはせず、ダウンロードする。</label></li>'."\n";
		$src .= '				</ul>'."\n";
		$src .= '			</td>'."\n";
		$src .= '		</tr>'."\n";
		$src .= '	</tbody>'."\n";
		$src .= '</table>'."\n";
		$src .= ''."\n";

		$src .= '	<p class="center"><input type="submit" value="インポートを実行する" /></p>'."\n";
		$src .= '	<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
		$src .= '	<div><input type="hidden" name="mode" value="execute" /></div>'."\n";
		$src .= '</form>'."\n";
		print $this->html_template($src);
		exit;
	}
	private function check_import_check(){
		$rtn = array();

		$ulfile_info = $this->px->req()->get_param('file_xlsx');
		if( strlen($ulfile_info['tmp_name']) && is_file($ulfile_info['tmp_name']) ){
			$this->px->req()->save_uploadfile('file_xlsx', $ulfile_info);
		}
		$ulfile_info = $this->px->req()->get_uploadfile('file_xlsx');

		if( !strlen($ulfile_info['name']) ){
			$rtn['file_xlsx'] = 'ファイルがアップロードされませんでした。';
		}elseif( strtolower($this->px->dbh()->get_extension($ulfile_info['name'])) != 'xlsx' ){
			$rtn['file_xlsx'] = '拡張子が xlsx ではないファイルがアップロードされました。';
		}elseif( !strlen($ulfile_info['content']) ){
			$rtn['file_xlsx'] = 'ファイルが0バイトです。';
		}

		if( !strlen( $this->px->req()->get_param('file_overwrite') ) ){
			$rtn['file_overwrite'] = 'サイトマップCSVの上書き設定を選択してください。';
		}elseif( $this->px->req()->get_param('file_overwrite') < 0 || $this->px->req()->get_param('file_overwrite') > 1 ){
			$rtn['file_overwrite'] = 'サイトマップCSVの上書き設定に、想定外の値が渡されました。';
		}
		if( $this->px->req()->get_param('file_overwrite') == 1 ){
			$tmp_sitemap_files = $this->px->dbh()->ls( $this->px->get_conf('paths.px_dir').'sitemaps/' );
			foreach( $tmp_sitemap_files as $tmp_sitemap_files_basename ){
				if( !$this->px->dbh()->is_writable( $this->px->get_conf('paths.px_dir').'sitemaps/'.$tmp_sitemap_files_basename ) ){
					$rtn['file_overwrite'] = 'サイトマップCSVファイル 「'.$tmp_sitemap_files_basename.'」を上書きできません。パーミッション設定を変更してください。';
					break;
				}
			}
			if( !$this->px->dbh()->is_writable($this->px->get_conf('paths.px_dir').'sitemaps/') ){
				$rtn['file_overwrite'] = 'サイトマップディレクトリ「'.realpath($this->px->get_conf('paths.px_dir').'sitemaps/').'」を上書きできません。パーミッション設定を変更してください。';
			}
		}
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

		$path_xlsx = $this->path_data_dir.'sitemapExcel.xlsx';//[UTODO]仮実装
		$path_csv  = $this->path_data_dir.'sitemapExcel.csv';//[UTODO]仮実装

		if( !$this->px->dbh()->mkdir_all( dirname($path_xlsx) ) ){
			$this->px->error()->error_log('FAILED to make a directory ['.dirname($path_xlsx).'].', __FILE__, __LINE__);
			print $this->html_template('[ERROR] FAILED to make a directory ['.dirname($path_xlsx).'].');
			exit;
		}

		$ulfileinfo = $this->px->req()->get_uploadfile('file_xlsx');
		if( !$this->px->dbh()->file_overwrite( $path_xlsx, $ulfileinfo['content'] ) ){
			$this->px->error()->error_log('FAILED to update inner xlsx.', __FILE__, __LINE__);
			print $this->html_template('[ERROR] FAILED to update inner xlsx.');
			exit;
		}

		if( !$obj_import->import_xlsx2sitemap( $path_xlsx, $path_csv ) ){
			$this->px->error()->error_log('FAILED to import xlsx.', __FILE__, __LINE__);
			print $this->html_template('[ERROR] FAILED to import xlsx.');
			exit;
		}

		clearstatcache();
		$this->px->req()->delete_uploadfile_all();// セッション上の一時ファイルを削除
		clearstatcache();


		if( $this->px->req()->get_param('file_overwrite') == 1 ){
			// サイトマップを自動的に置き換えて完了画面へリダイレクト
			$tmp_sitemap_files = $this->px->dbh()->ls( $this->px->get_conf('paths.px_dir').'sitemaps/' );
			foreach( $tmp_sitemap_files as $tmp_sitemap_files_basename ){
				if( !strlen($tmp_sitemap_files_basename) ){ continue; }
				if( !$this->px->dbh()->rm( $this->px->get_conf('paths.px_dir').'sitemaps/'.$tmp_sitemap_files_basename ) ){
					$this->px->error()->error_log('FAILED to remove sitemap file "'.realpath($this->px->get_conf('paths.px_dir').'sitemaps/'.$tmp_sitemap_files_basename).'".', __FILE__, __LINE__);
					print $this->html_template('[ERROR] FAILED to remove sitemap file "'.realpath($this->px->get_conf('paths.px_dir').'sitemaps/'.$tmp_sitemap_files_basename).'".');
					exit;
				}
			}
			if( !$this->px->dbh()->rename( $path_csv, $this->px->get_conf('paths.px_dir').'sitemaps/sitemapExcel.csv' ) ){
				$this->px->error()->error_log('FAILED to rename sitemap file "'.$path_csv.'" to "'.$this->px->get_conf('paths.px_dir').'sitemaps/sitemapExcel.csv".', __FILE__, __LINE__);
				print $this->html_template('[ERROR] FAILED to remove sitemap file "'.$path_csv.'" to "'.$this->px->get_conf('paths.px_dir').'sitemaps/sitemapExcel.csv".');
				exit;
			}
			return $this->px->redirect( $this->href().'&mode=thanks' );
		}else{
			// 変換後のCSVをダウンロード
			$this->px->flush_file($path_csv, array('filename'=>'PxFW_'.$this->px->get_conf('project.id').'_sitemap_'.date('Ymd_Hi').'.csv', 'delete'=>false));
		}
		exit;
		// return $this->px->redirect( $this->href().'&mode=thanks' );

	}
	private function page_import_thanks($error){
		$src = '';
		$src .= '<p>インポートしました。</p>'."\n";
		$src .= '<form action="?" method="get" class="inline">'."\n";
		$src .= '	<p class="center"><input type="submit" value="もう一度、インポートを実行する" /></p>'."\n";
		$src .= '	<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1],'import'))).'" /></div>'."\n";
		$src .= '	<div><input type="hidden" name="mode" value="" /></div>'."\n";
		$src .= '</form>'."\n";
		$src .= '<hr />'."\n";
		$src .= '<form action="?" method="get" class="inline">'."\n";
		$src .= '	<p class="center"><input type="submit" value="戻る" /></p>'."\n";
		$src .= '	<div><input type="hidden" name="PX" value="'.t::h(implode('.',array($this->command[0],$this->command[1]))).'" /></div>'."\n";
		$src .= '	<div><input type="hidden" name="mode" value="" /></div>'."\n";
		$src .= '</form>'."\n";
		print $this->html_template($src);
		exit;
	}



	/**
	 * 現在のサイトマップをエクスポートする。
	 */
	private function page_export(){

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

		$this->px->flush_file($path_work_dir.'tmp.xlsx', array('filename'=>'PxFW_'.$this->px->get_conf('project.id').'_sitemap_'.date('Ymd_Hi').'.xlsx', 'delete'=>true));
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
