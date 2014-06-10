# sitemapExcel (PxPlugin)

sitemapExcelプラグインは、Pickles Framework(PxFW)のサイトマップCSVを、見やすい Microsoft Excel 形式で、より直感的な形式で編集できるようにします。

## インストール方法 - Setup

1. Pickles Framework(PxFW) をダウンロードして、<a href="http://pickles.pxt.jp/setup/" target="_blank">セットアップ</a>する。
2. ディレクトリ `plugins/sitemapExcel` を、
   PxFW の `_PX/plugins` ディレクトリにアップロードする。
3. ディレクトリ `libs/PHPExcel` を、
   PxFW の `_PX/libs` ディレクトリにアップロードする。
4. PxFW の `_PX/sitemaps` ディレクトリ、
   およびすべてのサブディレクトリとファイルに、
   ウェブサーバーから書き込み可能なパーミッションを設定する。

エクセルファイルの容量が大きい場合に、インポート時にメモリが足りずに異常終了することがあります。この場合、`.htaccess` で `php_value memory_limit -1` を有効にするなどして、使用可能なメモリを増やすことで解決されるかも知れません。


## 使い方 - Usage

1. ブラウザで、URLにPX Command `?PX=plugins.sitemapExcel` をつけてアクセスする。
2. インポートボタンをクリックする。
3. エクセルファイルをドラッグ＆ドロップする、またはファイル選択してアップロードする。

エクセルファイルの作り方、形式については、エクスポート機能でダウンロードできるファイルを参考にしてください。


## システム要件 - Requirement

PxFW および PHPExcel の要件を参照。

- Pickles Framework <a href="http://pickles.pxt.jp/setup/requirement/">requirement</a>
	- Linux系サーバ または Windowsサーバ
	- Apache1.3以降
		- mod\_rewrite が利用可能であること
		- .htaccess が利用可能であること
	- PHP5.3以上
		- mb\_string が有効に設定されていること
		- safe\_mode が無効に設定されていること
- PHPExcel <a href="http://phpexcel.codeplex.com/wikipage?title=Requirements&referringTitle=Home">requirement</a>
	- PHP version 5.2.0 or higher
	- PHP extension php\_zip enabled *)
	- PHP extension php\_xml enabled
	- PHP extension php\_gd2 enabled (if not compiled in)

## Pickles Framework(PxFW) について

詳しくは下記のウェブサイトを参照してください。

- <a href="http://pickles.pxt.jp/" target="_blank">http://pickles.pxt.jp/</a>

## ライセンス - License

<a href="http://ja.wikipedia.org/wiki/MIT_License" target="_blank">MIT License</a>


## 作者 - Author

- (C)Tomoya Koyanagi &lt;tomk79@gmail.com&gt;
- website: <a href="http://www.pxt.jp/" target="_blank">http://www.pxt.jp/</a>
- Twitter: @tomk79 <a href="http://twitter.com/tomk79/" target="_blank">http://twitter.com/tomk79/</a>



