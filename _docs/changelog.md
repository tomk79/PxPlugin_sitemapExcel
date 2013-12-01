
# PxPlugin "sitemapExcel" 更新履歴

## PxPlugin sitemapExcel 0.5.0b2 (2013/**/**)

- 行ごとコメントアウトする機能を追加。カラム「**delete_flg」に値を入れる。
- 設定項目に skip_empty_col を追加。定義行が空白の列があっても、skip_empty_colの数まで右を調べる。


## PxPlugin sitemapExcel 0.5.0b1 (2013/10/16)

- トップページに id がセットされている場合に、これを無視するようにした。
- インポート時に、sitemap_definision.csv に記載のないカスタムカラムも、CSVに反映するようにした。
- インポートして sitemaps のCSVを自動的に上書きする場合に、ディレクトリおよび拡張子 *.csv 以外のファイルは消さないようにした。


## PxPlugin sitemapExcel 0.0.1b1 (2013/9/8)

- アップロードした xlsx で直接サイトマップCSVを上書きできるようにした。
- 設定シートをやめて、データシートのセルA1に設定を書くようにした。
- A列に、文字列「EndOfData」を発見したら、データ読み込みを終了するようにした。
- 出力されるエクセルファイルが、ちょっぴりおしゃれになった。


## PxPlugin sitemapExcel 0.0.1a1 (2013/6/10)

- 初版リリース。
