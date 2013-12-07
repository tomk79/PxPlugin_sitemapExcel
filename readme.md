# sitemapExcel (PxPlugin)

## インストール方法

1. PxFW(Pickles Framework) をセットアップする。
2. ディレクトリ plugins/sitemapExcel を、
   PxFW の plugins ディレクトリにアップロードする。
3. ディレクトリ libs/PHPExcel を、
   PxFW の libs ディレクトリにアップロードする。
4. PxFW の _PX/sitemaps ディレクトリ、
   およびすべてのサブディレクトリとファイルに、
   ウェブサーバーから書き込み可能なパーミッションを設定する。

## 使い方

1. ブラウザで、PxCommand "?PX=plugins.sitemapExcel" にアクセスする。
2. エクセルファイルをアップロード(またはダウンロード)する。

## システム要件

PxFW および PHPExcel の要件を参照。

- Pickles Framework requirement http://pickles.pxt.jp/setup/requirement/
	- Linux系サーバ または Windowsサーバ
	- Apache1.3以降
		- mod_rewrite が利用可能であること
		- .htaccess が利用可能であること
	- PHP5系
		- mb_string が有効に設定されていること
		- safe_mode が無効に設定されていること
- PHPExcel requirement http://phpexcel.codeplex.com/wikipage?title=Requirements&referringTitle=Home
	- PHP version 5.2.0 or higher
	- PHP extension php_zip enabled *)
	- PHP extension php_xml enabled
	- PHP extension php_gd2 enabled (if not compiled in)

## Pickles Framework(PxFW) について

詳しくは下記URLを参照。
http://pickles.pxt.jp/

------
(C)Tomoya Koyanagi.
http://www.pxt.jp/

